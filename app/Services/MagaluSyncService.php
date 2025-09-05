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

        $departments = $response->json() ?? [];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                [
                    'external_id' => $department['id'],
                    'supplier_id' => 1
                ],
                [
                    'name' => $department['name'] ?? '',
                    'updated_at' => now()
                ]
            );

            // Get the ID of the newly inserted or updated department
            $departmentId = DB::table('departments')
                ->where('external_id', $department['id'])
                ->where('supplier_id', 1)
                ->value('id');

            // Save the subcategories with the department_id
            foreach ($department['sub_categories'] as $sub_categories) {
                DB::table('categories')->updateOrInsert(
                    [
                        'external_id' => $sub_categories['id'],
                        'supplier_id' => 1
                    ],
                    [
                        'name' => $sub_categories['name'] ?? '',
                        'department_id' => $departmentId,
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
}
