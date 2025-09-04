<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SuppliersTableSeeder extends Seeder
{
    public function run()
    {
        Supplier::updateOrCreate(
            ['slug' => 'magalu-supplier'],
            [
                'name' => 'Magalu Supplier',
                'api_base_url' => 'https://b2b-platform-staging.luizalabs.com/api/v1/',
                'api_key' => null,
                'status' => 'active'
            ]
        );
    }
}
