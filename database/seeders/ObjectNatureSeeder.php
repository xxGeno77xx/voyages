<?php

namespace Database\Seeders;

use App\Enums\ObjectNaturesClass;
use App\Models\ObjectNature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Enums\RoutingsClass;

class ObjectNatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $natures = ObjectNaturesClass::toValues();

        foreach ($natures as $key => $name) {
            ObjectNature::firstOrCreate([
                'label' => $name,
            ]);
        }

    }
}
