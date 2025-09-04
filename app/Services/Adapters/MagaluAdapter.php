<?php

namespace App\Services\Adapters;

use App\Models\Supplier;
use Illuminate\Support\Facades\Http;

class MagaluAdapter
{
    protected $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * Fetch all data from supplier and normalize to expected array shape.
     * This implementation is generic and must be adapted to each supplier API structure.
     */
    public function fetchAll(): array
    {
        $base = rtrim($this->supplier->api_base_url, '/');

        // Example: assumes supplier offers /products endpoint that returns JSON array
        $url = $base . '/products';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => $this->supplier->api_key ? 'Bearer ' . $this->supplier->api_key : null
        ])->get($url);

        if (!$response->ok()) {
            throw new \Exception('Supplier API error: ' . $response->status());
        }

        $payload = $response->json();

        // Normalize - this is an example. YOU MUST ADAPT normalization to real supplier's response.
        $items = [];
        foreach ($payload as $p) {
            $items[] = [
                'external_id' => $p['id'] ?? ($p['product_id'] ?? null),
                'name' => $p['name'] ?? '',
                'description' => $p['description'] ?? null,
                'price' => isset($p['price']) ? (float)$p['price'] : null,
                'category' => [
                    'id' => $p['category']['id'] ?? ($p['category_id'] ?? null),
                    'name' => $p['category']['name'] ?? ($p['category_name'] ?? null)
                ],
                'department' => [
                    'id' => $p['department']['id'] ?? ($p['department_id'] ?? null),
                    'name' => $p['department']['name'] ?? ($p['department_name'] ?? null)
                ],
                'store' => [
                    'id' => $p['store']['id'] ?? ($p['store_id'] ?? null),
                    'name' => $p['store']['name'] ?? ($p['store_name'] ?? null)
                ],
                'sku' => $p['sku'] ?? null,
                'stock' => $p['stock'] ?? 0,
                'attributes' => $p['attributes'] ?? null,
                'extra' => $p // keep raw for debugging if useful
            ];
        }

        return $items;
    }
}
