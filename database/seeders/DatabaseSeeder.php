<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Llama a tus seeders en orden
        $this->call([
            BombaSeeder::class,     // O BombaSeeder, como lo hayas nombrado
            LecturaSeeder::class,  // El de los datos con el error
        ]);
    }
}