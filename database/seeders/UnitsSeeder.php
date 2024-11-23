<?php

namespace Database\Seeders;

use App\Enums\UnitsClass;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = UnitsClass::toValues();

        foreach ($units as $key => $name) {
            Unit::firstOrCreate([
                'label' => $name,
            ]);
        }
    }
}
