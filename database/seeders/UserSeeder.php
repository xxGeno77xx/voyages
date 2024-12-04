<?php

namespace Database\Seeders;

use App\Models\User;
use Hash;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "name" => "admin",
            "email" => "admin@gmail.com",
            "username" => "admin",
            "email_verified_at" => today(),
            "password" => Hash::make("password"),
            
        ]);
    }
}
