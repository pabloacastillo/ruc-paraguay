<?php

namespace martinpa13py\RUCParaguay\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use martinpa13py\RUCParaguay\Models\RucParaguaySet;
use ZipArchive;

class RUCParaguayUpdater
{
    protected string $url;
    protected string $folder = 'ruc-paraguay';
    protected int $cacheDuration;
    protected int $batchSize = 2000;
    protected ?int $limit = null;
    protected bool $force = false;
    protected bool $test = false;
    protected bool $skipDownload = false;
    /** @var int[]|null */
    protected ?array $fileIndexes = null;
    protected $output;

    public function __construct(?callable $output = null)
    {
        $this->output = $output ?? function (string $msg) {
            echo $msg . "\n";
        };

        $this->url = rtrim((string) $this->config('base_url', 'https://www.dnit.gov.py/documents/20123/976078/'), '/') . '/';
        $this->cacheDuration = ((int) $this->config('cache_hours', 48)) * 3600;
        $this->batchSize = max(100, (int) $this->config('batch_size', 2000));
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        if (function_exists('config')) {
            return config('ruc-paraguay.' . $key, $default);
        }

        return $default;
    }

    public function setForce(bool $force): self
    {
        $this->force = $force;
        return $this;
    }

    public function setBatchSize(int $batchSize): self
    {
        $this->batchSize = max(100, $batchSize);
        return $this;
    }

    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function setTest(bool $test): self
    {
        $this->test = $test;
        if ($test) {
            if ($this->fileIndexes === null) {
                $this->fileIndexes = [0];
            }
            if ($this->limit === null) {
                $this->limit = 1000;
            }
        }
        return $this;
    }

    /**
     * @param int[] $indexes
     */
    public function setFiles(array $indexes): self
    {
        $this->fileIndexes = array_values(array_unique(array_map('intval', $indexes)));
        return $this;
    }

    public function setSkipDownload(bool $skip): self
    {
        $this->skipDownload = $skip;
        return $this;
    }

    public function setOutput(callable $output): self
    {
        $this->output = $output;
        return $this;
    }

    protected function out(string $message): void
    {
        ($this->output)($message);
    }

    /**
     * @return int[]
     */
    protected function fileIndexes(): array
    {
        if ($this->fileIndexes !== null && $this->fileIndexes !== []) {
            return $this->fileIndexes;
        }

        if ($this->limit !== null) {
            return [0];
        }

        return range(0, 9);
    }

    public function run(): void
    {
        $startTime = time();
        $storagePath = Storage::disk('local')->path($this->folder);

        $this->out('Iniciando proceso de descarga e importación de datos RUC...');

        if (!Storage::disk('local')->exists($this->folder)) {
            Storage::disk('local')->makeDirectory($this->folder, 0775, true);
            $this->out("Directorio creado: {$this->folder}");
        }

        $tableEmpty = $this->tableIsEmpty();
        $sampleImport = $this->test || $this->limit !== null;

        if (!$tableEmpty && $sampleImport && !$this->force) {
            $this->out('La tabla ya tiene datos. Use --force para reemplazarlos, o quite --test/--limit.');
            return;
        }

        if ($this->force && !$this->skipDownload) {
            $this->clearCache();
            $this->out('Caché limpiada (--force). Los archivos seleccionados serán descargados nuevamente.');
        }

        $downloadedFiles = 0;
        if (!$this->skipDownload) {
            $this->out('Buscando archivos de datos...');
            $client = new Client([
                'timeout' => 120,
                'connect_timeout' => 30,
            ]);

            foreach ($this->fileIndexes() as $i) {
                $filePath = "ruc{$i}.zip";
                $url = "{$this->url}{$filePath}";

                if (!$this->force && $this->isCached($filePath)) {
                    $this->out("  Saltando {$filePath} (en caché)");
                    continue;
                }

                $this->out("  Descargando {$filePath}...");
                $this->downloadWithRetry($client, $url, "{$storagePath}/{$filePath}");
                $downloadedFiles++;
            }
        }

        if ($downloadedFiles === 0 && !$tableEmpty && !$this->force && !$this->skipDownload && !$sampleImport) {
            $this->out('No hay datos nuevos para importar. Todos los archivos están en caché.');
            return;
        }

        if ($downloadedFiles === 0 && $tableEmpty) {
            $this->out('Tabla vacía: se importará desde los archivos locales en caché.');
        }

        $this->out("Archivos descargados: {$downloadedFiles}");
        $this->out('Iniciando extracción e importación...');

        $extractStartTime = time();
        $this->extractAndImportData($storagePath);
        $extractTime = time() - $extractStartTime;

        $this->out("Extracción e importación completada en {$extractTime} segundos");
        $this->out('Proceso total completado en ' . (time() - $startTime) . ' segundos');
    }

    public function clearCache(): void
    {
        $files = Storage::disk('local')->files($this->folder);
        foreach ($files as $file) {
            Storage::disk('local')->delete($file);
        }
        $this->out('Archivos de caché eliminados.');
    }

    protected function tableIsEmpty(): bool
    {
        if (!Schema::hasTable((new RucParaguaySet())->getTable())) {
            return true;
        }

        return !RucParaguaySet::query()->exists();
    }

    protected function isCached(string $filePath): bool
    {
        if (Storage::disk('local')->exists("{$this->folder}/{$filePath}")) {
            $lastModified = Storage::disk('local')->lastModified("{$this->folder}/{$filePath}");
            return (time() - $lastModified) < $this->cacheDuration;
        }
        return false;
    }

    protected function downloadWithRetry(Client $client, string $url, string $destination, int $attempts = 3): void
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $client->get($url, ['sink' => $destination]);
                return;
            } catch (GuzzleException $e) {
                $lastException = $e;
                $this->out("  Intento {$attempt}/{$attempts} falló: " . $e->getMessage());
                if ($attempt < $attempts) {
                    sleep($attempt);
                }
            }
        }

        throw $lastException;
    }

    protected function extractAndImportData(string $storagePath): void
    {
        $this->extractZipFiles($storagePath);
        RucParaguaySet::truncate();

        $allowed = array_map(fn (int $i) => 'ruc' . $i . '.txt', $this->fileIndexes());

        $txtFiles = array_values(array_filter(
            Storage::disk('local')->files($this->folder),
            function ($file) use ($allowed) {
                if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'txt') {
                    return false;
                }
                return in_array(strtolower(basename($file)), $allowed, true);
            }
        ));

        $totalFiles = count($txtFiles);
        $currentFile = 0;
        $totalInserted = 0;
        $queryLogWasEnabled = DB::logging();
        DB::disableQueryLog();

        try {
            foreach ($txtFiles as $txtFile) {
                if ($this->limit !== null && $totalInserted >= $this->limit) {
                    $this->out("Límite de {$this->limit} registros alcanzado. Deteniendo importación.");
                    break;
                }

                $currentFile++;
                $this->out("Procesando archivo {$currentFile}/{$totalFiles}: " . basename($txtFile));
                $inserted = $this->importDataFromTxt($txtFile, $totalInserted);
                $totalInserted += $inserted;
            }
        } finally {
            if ($queryLogWasEnabled) {
                DB::enableQueryLog();
            }
        }

        $this->out("Total insertado: {$totalInserted} registros");
    }

    protected function extractZipFiles(string $storagePath): void
    {
        $allowed = array_map(fn (int $i) => 'ruc' . $i . '.zip', $this->fileIndexes());

        foreach (Storage::disk('local')->files($this->folder) as $zipFile) {
            if (strtolower(pathinfo($zipFile, PATHINFO_EXTENSION)) !== 'zip') {
                continue;
            }
            if (!in_array(strtolower(basename($zipFile)), $allowed, true)) {
                continue;
            }
            $this->extractZip($zipFile, $storagePath);
        }
    }

    protected function extractZip(string $zipFile, string $storagePath): void
    {
        $localZip = Storage::disk('local')->path($zipFile);
        $zip = new ZipArchive;

        if ($zip->open($localZip) === true) {
            $zip->extractTo($storagePath);
            $zip->close();
        } else {
            Storage::disk('local')->delete($zipFile);
        }
    }

    protected function importDataFromTxt(string $txtFile, int $alreadyInserted): int
    {
        $localTxt = Storage::disk('local')->path($txtFile);
        $batch = [];
        $totalRecords = 0;
        $skipped = 0;

        if (($file = fopen($localTxt, 'r')) === false) {
            return 0;
        }

        while (($line = fgets($file)) !== false) {
            if ($this->limit !== null && ($alreadyInserted + $totalRecords + count($batch)) >= $this->limit) {
                break;
            }

            $data = $this->parseLine($line);
            if ($data) {
                $batch[] = $data;

                if (count($batch) >= $this->batchSize) {
                    $inserted = $this->insertBatch($batch);
                    $totalRecords += $inserted;
                    $skipped += count($batch) - $inserted;
                    $this->out("  Insertados {$totalRecords} registros");
                    $batch = [];
                }
            }
        }

        if (!empty($batch)) {
            if ($this->limit !== null) {
                $remaining = $this->limit - ($alreadyInserted + $totalRecords);
                $batch = array_slice($batch, 0, max(0, $remaining));
            }
            if (!empty($batch)) {
                $inserted = $this->insertBatch($batch);
                $totalRecords += $inserted;
                $skipped += count($batch) - $inserted;
            }
        }

        $this->out("  Completado: {$totalRecords} registros insertados" . ($skipped ? ", {$skipped} omitidos" : ''));
        fclose($file);

        return $totalRecords;
    }

    /**
     * @param array<int, array<string, mixed>> $batch
     */
    protected function insertBatch(array $batch): int
    {
        try {
            RucParaguaySet::insert($batch);
            return count($batch);
        } catch (\Throwable $e) {
            $this->out('  Error insertando lote: ' . $e->getMessage());
            return 0;
        }
    }

    public function parseLine(string $line): ?array
    {
        $line = trim($line);
        if ($line === '') {
            return null;
        }

        $parts = explode('|', $line);
        if (count($parts) < 5) {
            return null;
        }

        if (count($parts) > 5) {
            $nro = array_shift($parts);
            $estado = array_pop($parts);
            $rucAnterior = array_pop($parts);
            $dv = array_pop($parts);
            $parts = [$nro, implode('|', $parts), $dv, $rucAnterior, $estado];
        }

        $digitoVerificador = preg_replace('/[^0-9]/', '', trim($parts[2] ?? '')) ?? '';
        $digitoVerificador = substr($digitoVerificador, 0, 1);

        $rucAnterior = trim($parts[3] ?? '');
        $nroRuc = substr(trim($parts[0] ?? ''), 0, 8);
        $denominacion = substr(trim($parts[1] ?? ''), 0, 255);
        $estado = substr(trim($parts[4] ?? ''), 0, 20);

        if ($nroRuc === '') {
            return null;
        }

        return [
            'nro_ruc' => $nroRuc,
            'denominacion' => $denominacion,
            'digito_verificador' => $digitoVerificador,
            'ruc_anterior' => $rucAnterior === '' ? null : substr($rucAnterior, 0, 16),
            'estado' => $estado,
        ];
    }
}
