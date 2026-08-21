<?php

namespace Database\Seeders;

use App\Models\Priority;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed minimal: 1 akun admin + 4 prioritas standar (PRD GRAPH 3).
     * Master data lain diisi via UI admin.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
        ]);

        foreach ([['Critical', 1], ['High', 2], ['Medium', 3], ['Low', 4]] as [$name, $ordering]) {
            Priority::create(['name' => $name, 'ordering' => $ordering, 'status' => 'active']);
        }
    }
}
