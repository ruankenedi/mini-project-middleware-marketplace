<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'trocapontos@example.com'],
            [
                'name' => 'Troca Pontos Client',
                'password' => Hash::make('ChangeThisPassword123!') // change in production
            ]
        );
    }
}
