<?php

namespace martinpa13py\RUCParaguay\Services;

use Illuminate\Support\Collection;
use martinpa13py\RUCParaguay\Models\RucParaguaySet;

class RUCParaguay
{
    public static function search($busqueda, ?int $limit = null): Collection
    {
        return app(self::class)->performSearch($busqueda, $limit);
    }

    public function __call(string $method, array $arguments)
    {
        if ($method === 'search') {
            return $this->performSearch(...$arguments);
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    public function performSearch($busqueda, ?int $limit = null): Collection
    {
        $limit = $limit ?? (int) config('ruc-paraguay.search_limit', 50);
        $query = RucParaguaySet::query();

        if (is_array($busqueda)) {
            if (array_is_list($busqueda)) {
                $busqueda = trim(implode(' ', $busqueda));
            } else {
                if (!empty($busqueda['nro_ruc'])) {
                    $query->where('nro_ruc', 'LIKE', $this->prefixLike($busqueda['nro_ruc']));
                }
                if (!empty($busqueda['denominacion'])) {
                    $query->where('denominacion', 'LIKE', '%' . $busqueda['denominacion'] . '%');
                }
                if (!empty($busqueda['ruc_anterior'])) {
                    $query->where('ruc_anterior', 'LIKE', $this->prefixLike($busqueda['ruc_anterior']));
                }

                return $query->limit($limit)->get();
            }
        }

        $term = trim((string) $busqueda);
        if ($term === '') {
            return collect();
        }

        if ($this->looksLikeExactRuc($term)) {
            [$nro, $dv] = $this->splitRuc($term);
            $query->where('nro_ruc', $nro);
            if ($dv !== null) {
                $query->where('digito_verificador', $dv);
            }

            return $query->limit($limit)->get();
        }

        if ($this->looksLikeRucPrefix($term)) {
            return $query->where('nro_ruc', 'LIKE', $this->prefixLike(strtoupper($term)))
                ->limit($limit)
                ->get();
        }

        $query->where(function ($q) use ($term) {
            $q->where('nro_ruc', 'LIKE', $this->prefixLike($term))
                ->orWhere('denominacion', 'LIKE', '%' . $term . '%')
                ->orWhere('ruc_anterior', 'LIKE', $this->prefixLike($term));
        });

        return $query->limit($limit)->get();
    }

    protected function prefixLike(string $value): string
    {
        return str_replace(['%', '_'], ['\%', '\_'], $value) . '%';
    }

    protected function looksLikeExactRuc(string $term): bool
    {
        return (bool) preg_match('/^[0-9]{6,8}[ABab]?(-[0-9])?$/', $term);
    }

    protected function looksLikeRucPrefix(string $term): bool
    {
        return (bool) preg_match('/^[0-9]{1,8}[ABab]?$/', $term);
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    protected function splitRuc(string $term): array
    {
        $nro = $term;
        $dv = null;

        if (str_contains($term, '-')) {
            [$nro, $dv] = explode('-', $term, 2);
        }

        return [strtoupper($nro), $dv];
    }
}
