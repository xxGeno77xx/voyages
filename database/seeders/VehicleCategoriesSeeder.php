<?php

namespace Database\Seeders;

use App\Enums\VehicleCategoriesClass;
use App\Models\VehicleType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = VehicleCategoriesClass::toValues();

        foreach ($categories as $key => $name) {
            VehicleType::firstOrCreate([
                'label' => $name,
            ]);
        }

    }
}
