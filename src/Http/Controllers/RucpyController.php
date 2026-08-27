<?php

namespace martinpa13py\RUCParaguay\Http\Controllers;

use martinpa13py\RUCParaguay\Models\RucParaguaySet;
use martinpa13py\RUCParaguay\Services\RUCParaguay;
use martinpa13py\RUCParaguay\Services\RUCParaguayUpdater;

class RucpyController
{
    public function search(string $busqueda)
    {
        return RUCParaguay::search($busqueda);
    }

    public function add(array $data): bool
    {
        return RucParaguaySet::create($data) !== null;
    }

    /**
     * Descarga e importa los datos de RUC desde DNIT.
     * Para compatibilidad con versiones anteriores, delega al servicio RUCParaguayUpdater.
     */
    public function download(?bool $force = null, ?int $batchSize = null, ?int $limit = null): void
    {
        $updater = app(RUCParaguayUpdater::class);

        if ($force !== null) {
            $updater->setForce($force);
        }
        if ($batchSize !== null) {
            $updater->setBatchSize($batchSize);
        }
        if ($limit !== null) {
            $updater->setLimit($limit);
        }

        $updater->run();
    }
}
