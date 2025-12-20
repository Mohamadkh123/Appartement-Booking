<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        
        User::factory()->create([
            'first_name' => 'Mohamad',
            'last_name' => 'Khallouff',
            'email' => 'mohamadkhallouff@gmail.com',
            'mobile' => '0987654321',
            'role' => 'admin',
            'status' => 'active',
            'password' => Hash::make('mohamad123'),
        ]);

        
        
        
        $this->call(ApartmentsTableSeeder::class);
    }
}