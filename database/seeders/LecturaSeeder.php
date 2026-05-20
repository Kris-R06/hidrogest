<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bomba;
use App\Models\LecturasBomba;
use Faker\Factory as Faker;
use Carbon\Carbon;

class LecturaSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $bombas = Bomba::all();

        foreach ($bombas as $bomba) {
            // Generamos 10 lecturas para cada bomba
            for ($i = 10; $i >= 1; $i--) {
                LecturasBomba::create([
                    'bomba_id' => $bomba->id,
                    // Datos realistas usando Faker
                    'ph' => $faker->randomFloat(2, 6.8, 7.8),       // Seguro: Lejos de 6.5 y 8.5
                    'turb' => $faker->randomFloat(2, 0.5, 2.5),     // Seguro: Menor a 3.0
                    'ppm' => $faker->randomFloat(1, 200, 600),      // Seguro: Menor a 1000
                    'temp' => $faker->randomFloat(1, 10.0, 19.0),   // Seguro: Entre 20 y 45
                    'flujo' => $faker->randomFloat(1, 90.0, 130.0), // Seguro: Mayor a 15
                    'presion' => $faker->randomFloat(1, 40.0, 60.0),
                    
                    'created_at' => Carbon::now()->subMinutes($i * 5),
                    'updated_at' => Carbon::now()->subMinutes($i * 5),
                ]);
            }

            if ($bomba->id === 1) {
                LecturasBomba::create([
                    'bomba_id' => $bomba->id,
                    // Parámetros críticos:
                    'ph' => 4.0,       // ERROR: Agua muy ácida
                    'turb' => 9.5,     // ERROR: Agua súper sucia (lodo)
                    'ppm' => 800.0,    // ERROR: Exceso de sólidos
                    'temp' => 25.0,
                    'flujo' => 0.0,    // ERROR: Corte total de agua
                    'presion' => 5.0,  // ERROR: Sin presión
                    'created_at' => Carbon::now(), // Ocurrió justo ahora
                    'updated_at' => Carbon::now(),
                ]);

                // Actualizamos el estado de la bomba para que el mapa la pinte roja
                $bomba->status = 'alert';
                $bomba->save();
            }
        }
    }
}