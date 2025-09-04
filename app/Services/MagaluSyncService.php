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

    public function syncProducts()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}"
            ])->get($this->baseUrl . '/products');

            if ($response->failed()) {
                throw new Exception("Failed to fetch products from Magalu");
            }

            $products = $response->json()['data'] ?? [];

            foreach ($products as $product) {
                DB::table('products')->updateOrInsert(
                    ['external_id' => $product['id'], 'supplier_id' => 1], // 1 = Magalu
                    [
                        'name' => $product['name'],
                        'price' => $product['price'] ?? 0,
                        'department_id' => null, // ajusta depois com mappings
                        'category_id' => null,
                        'store_id' => null,
                        'updated_at' => now(),
                    ]
                );
            }

            DB::table('sync_logs')->insert([
                'supplier_id' => 1,
                'status' => 'success',
                'message' => 'Products synced successfully',
                'created_at' => now()
            ]);

            return true;
        } catch (Exception $e) {
            DB::table('sync_logs')->insert([
                'supplier_id' => 1,
                'status' => 'error',
                'message' => $e->getMessage(),
                'created_at' => now()
            ]);

            return false;
        }
    }
}
