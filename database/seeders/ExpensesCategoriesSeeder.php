<?php

namespace Database\Seeders;

use App\Enums\ExpensesCategoresClass;
use App\Models\ExpensesCategorie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpensesCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run(): void
        {
            $categories = ExpensesCategoresClass::toValues();
    
            foreach ($categories as $key => $name) {
                ExpensesCategorie::firstOrCreate([
                    'label' => $name,
                ]);
            }
    
        }
    
}
