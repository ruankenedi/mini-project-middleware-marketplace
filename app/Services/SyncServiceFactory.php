<?php

namespace App\Services;

use InvalidArgumentException;

class SyncServiceFactory
{
    public static function make(string $supplier)
    {
        switch (strtolower($supplier)) {
            case 'magalu':
                return app(MagaluSyncService::class);

                // case 'casasbahia':
                //     return app(CasasBahiaSyncService::class);

            default:
                throw new InvalidArgumentException("Supplier {$supplier} not supported.");
        }
    }
}
