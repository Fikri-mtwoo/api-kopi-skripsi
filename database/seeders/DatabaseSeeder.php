<?php

namespace Database\Seeders;

use App\Models\Jenis;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // User::factory(10)->create();
        Jenis::create([
            'jenis_produk' => 'makanan'
        ]);
        Jenis::create([
            'jenis_produk' => 'minuman'
        ]);
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin'),
            'role' => 'admin'
        ]);
    }
}
