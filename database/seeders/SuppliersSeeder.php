<?php

namespace Database\Seeders;

use App\Enums\SupplierClass;
use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = SupplierClass::toValues();

        foreach ($suppliers as $key => $name) {
            Supplier::firstOrCreate([
                'raison_sociale' => $name,
            ]);
        }
    }
}
