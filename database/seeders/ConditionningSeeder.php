<?php

namespace Database\Seeders;

use App\Enums\ConditionningClass;
use App\Models\Conditionning;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConditionningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conditionnings = ConditionningClass::toValues();

        foreach ($conditionnings as $key => $name) {
            Conditionning::firstOrCreate([
                'label' => $name,
            ]);
        }
    }
}
