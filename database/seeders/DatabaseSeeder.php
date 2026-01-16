<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    
    public function run(): void
    {

        if (!User::where('email', 'mohamadkhallouff@gmail.com')->exists()) {
            User::factory()->create([
                'first_name' => 'Mohamad',
                'last_name' => 'Khallouff',
                'email' => 'mohamadkhallouff@gmail.com',
                'mobile' => '0987654321',
                'date_of_birth' => '2002-02-17',
                'role' => 'owner',
                'status' => 'pending',
                'password' => Hash::make('mohamad123'),
            ]);
              User::factory()->create([
                'first_name' => 'Ghina',
                'last_name' => 'Albalkhi',
                'email' => 'ghina@gmail.com',
                'mobile' => '0987654325',
                'date_of_birth' => '2005-06-25',
                'role' => 'admin',
                'status' => 'active',
                'password' => Hash::make('12345678'),
            ]);

            User::factory()->create([
                'first_name' => 'Raghad',
                'last_name' => 'Alnusaerat',
                'email' => 'raghad@gmail.com',
                'mobile' => '09876789325',
                'date_of_birth' => '2005-06-25',
                'role' => 'tenant',
                'status' => 'pending',
                'password' => Hash::make('12345678'),
            ]);
        }




        $this->call(ApartmentsTableSeeder::class);
    }
}
