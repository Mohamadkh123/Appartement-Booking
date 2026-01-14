<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Apartment;
use App\Models\ApartmentImage;

class ApartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $locations = [
            // Damascus
            ['province' => 'Damascus', 'city' => 'Al-Maliki'],
            ['province' => 'Damascus', 'city' => 'Mazzeh'],
            ['province' => 'Damascus', 'city' => 'Baramkeh'],

            // Aleppo
            ['province' => 'Aleppo', 'city' => 'Al-Jamiliya'],
            ['province' => 'Aleppo', 'city' => 'Azaz'],
            ['province' => 'Aleppo', 'city' => 'Manbij'],

            // Homs
            ['province' => 'Homs', 'city' => 'Rastan'],
            ['province' => 'Homs', 'city' => 'Al-Qusayr'],
            ['province' => 'Homs', 'city' => 'Al-Hawash'],

            // Hama
            ['province' => 'Hama', 'city' => 'Mharda'],
            ['province' => 'Hama', 'city' => 'Masyaf'],
            ['province' => 'Hama', 'city' => 'Salamiyah'],

            // Latakia
            ['province' => 'Latakia', 'city' => 'Slanfa'],
            ['province' => 'Latakia', 'city' => 'Jableh'],
            ['province' => 'Latakia', 'city' => 'Ras Al-Basit'],

            // Tartous
            ['province' => 'Tartous', 'city' => 'Drakesh'],
            ['province' => 'Tartous', 'city' => 'Baniyas'],
            ['province' => 'Tartous', 'city' => 'Safita']
        ];

    } 
       
}