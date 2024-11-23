<?php

namespace Database\Seeders;

use App\Enums\RoutingsClass;
use App\Models\Routing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoutingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $routings = RoutingsClass::toValues();

        foreach ($routings as $key => $name) {
            Routing::firstOrCreate([
                'label' => $name,
            ]);
        }

    }
}
