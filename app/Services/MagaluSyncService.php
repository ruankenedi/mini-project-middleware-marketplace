<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Exception;

class MagaluSyncService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.magalu.base_url');
        $this->apiKey = config('services.magalu.api_key');
    }

    public function syncAll()
    {
        DB::beginTransaction();

        try {
            $this->syncCategories();
            $this->syncProducts();

            DB::commit();

            DB::table('sync_logs')->insert([
                'supplier_id' => 1,
                'status' => 'success',
                'message' => 'Departments, Categories and products synced successfully',
                'created_at' => now()
            ]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();

            DB::table('sync_logs')->insert([
                'supplier_id' => 1,
                'status' => 'error',
                'message' => $e->getMessage(),
                'created_at' => now()
            ]);

            return false;
        }
    }

    // Sync departments and categories     
    public function syncCategories()
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->apiKey}"
        ])->get($this->baseUrl . '/categories');

        if ($response->failed()) {
            throw new Exception("Failed to fetch categories from Magalu");
        }

        $categories = $response->json() ?? [];

        foreach ($categories as $category) {
            DB::table('departments')->updateOrInsert(
                [
                    'external_id' => $category['id'],
                    'supplier_id' => 1
                ],
                [
                    'name' => $category['name'] ?? '',
                    'updated_at' => now()
                ]
            );

            foreach ($category['sub_categories'] as $sub) {
                DB::table('categories')->updateOrInsert(
                    [
                        'external_id' => $sub['id'],
                        'supplier_id' => 1
                    ],
                    [
                        'name' => $sub['name'] ?? '',
                        'updated_at' => now()
                    ]
                );
            }
        }
    }

    // Sync products
    public function syncProducts()
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->apiKey}"
        ])->get($this->baseUrl . '/products', [
            '_limit' => 10,
            '_page' => 1
        ]);

        if ($response->failed()) {
            throw new Exception("Failed to fetch products from Magalu");
        }

        $products = $response->json() ?? [];

        foreach ($products as $product) {
            DB::table('products')->updateOrInsert(
                ['external_id' => $product['id'], 'supplier_id' => 1],
                [
                    'name' => $product['title'],
                    'description' => $product['description'],
                    'price' => $product['price'] ?? 0,
                    'department_id' => $this->resolveDepartmentsId($product['categories'][0]['id'] ?? null),
                    'category_id' => null,
                    'store_id' => null,
                    'updated_at' => now()
                ]
            );
        }
    }

    // Helper for linking departments to products
    protected function resolveDepartmentsId($externalDepartmentId)
    {
        if (!$externalDepartmentId) {
            return null;
        }

        return DB::table('departments')
            ->where('external_id', $externalDepartmentId)
            ->where('supplier_id', 1)
            ->value('id');
    }

    // Helper for linking departments to categories
    protected function resolveCategoryId($externalCategoryId)
    {
        if (!$externalCategoryId) {
            return null;
        }

        return DB::table('categories')
            ->where('external_id', $externalCategoryId)
            ->where('supplier_id', 1)
            ->value('id');
    }
}
