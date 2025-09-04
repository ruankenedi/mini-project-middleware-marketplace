<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\Category;
use App\Models\Department;
use App\Models\Store;
use App\Models\Product;
use App\Models\Sku;
use App\Models\Mapping;
use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Adapters\MagaluAdapter;

class SupplierSyncService
{
    /**
     * Sync supplier - synchronous.
     * Returns ['success'=>bool, 'processed'=>int, 'message'=>string]
     */
    public function syncSupplier(Supplier $supplier)
    {
        $startedAt = now();
        $processed = 0;

        // choose adapter - here we use MagaluAdapter by default
        $adapter = new MagaluAdapter($supplier);

        try {
            $items = $adapter->fetchAll(); // should return array of normalized items

            DB::beginTransaction();

            foreach ($items as $item) {
                $this->processItem($item, $supplier);
                $processed++;
            }

            $supplier->last_sync_at = now();
            $supplier->status = 'active';
            $supplier->save();

            DB::commit();

            SyncLog::create([
                'supplier_id' => $supplier->id,
                'status' => 'success',
                'processed_count' => $processed,
                'message' => 'Sync completed',
                'raw_response' => null,
                'started_at' => $startedAt,
                'finished_at' => now()
            ]);

            return ['success' => true, 'processed' => $processed];
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Sync error: ' . $e->getMessage());
            SyncLog::create([
                'supplier_id' => $supplier->id,
                'status' => 'error',
                'processed_count' => $processed,
                'message' => $e->getMessage(),
                'raw_response' => null,
                'started_at' => $startedAt,
                'finished_at' => now()
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function processItem(array $item, Supplier $supplier)
    {
        // category
        $category = null;
        if (!empty($item['category'])) {
            $category = Category::firstOrCreate(
                ['supplier_id' => $supplier->id, 'external_id' => $item['category']['id']],
                ['name' => $item['category']['name'] ?? '']
            );
            Mapping::updateOrCreate(
                ['mapping_type' => 'category', 'supplier_id' => $supplier->id, 'supplier_external_id' => $item['category']['id']],
                ['local_id' => $category->id, 'supplier_external_name' => $item['category']['name'] ?? null]
            );
        }

        // department
        $department = null;
        if (!empty($item['department'])) {
            $department = Department::firstOrCreate(
                ['supplier_id' => $supplier->id, 'external_id' => $item['department']['id']],
                ['name' => $item['department']['name'] ?? '']
            );
            Mapping::updateOrCreate(
                ['mapping_type' => 'department', 'supplier_id' => $supplier->id, 'supplier_external_id' => $item['department']['id']],
                ['local_id' => $department->id, 'supplier_external_name' => $item['department']['name'] ?? null]
            );
        }

        // store
        $store = null;
        if (!empty($item['store'])) {
            $store = Store::firstOrCreate(
                ['supplier_id' => $supplier->id, 'external_id' => $item['store']['id']],
                ['name' => $item['store']['name'] ?? '']
            );
            Mapping::updateOrCreate(
                ['mapping_type' => 'store', 'supplier_id' => $supplier->id, 'supplier_external_id' => $item['store']['id']],
                ['local_id' => $store->id, 'supplier_external_name' => $item['store']['name'] ?? null]
            );
        }

        // create product ou upsert
        $product = Product::updateOrCreate(
            ['supplier_id' => $supplier->id, 'external_id' => $item['external_id']],
            [
                'name' => $item['name'] ?? '',
                'description' => $item['description'] ?? null,
                'price' => $item['price'] ?? null,
                'category_id' => $category ? $category->id : null,
                'department_id' => $department ? $department->id : null,
                'store_id' => $store ? $store->id : null,
                'extra' => $item['extra'] ?? null
            ]
        );

        // create sku or upsert
        if (!empty($item['sku'])) {
            Sku::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'product_id' => $product->id,
                    'stock' => $item['stock'] ?? 0,
                    'price' => $item['price'] ?? null,
                    'attributes' => $item['attributes'] ?? null
                ]
            );
        }
    }
}
