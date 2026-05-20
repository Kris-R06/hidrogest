<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bomba;

class BombaSeeder extends Seeder
{
    public function run(): void
    {
        $bombas = [
            // Plantas Potabilizadoras
            [
                'name' => 'Planta Potabilizadora No. 1 (Centro/Oriente)', 
                'lat' => 25.8893424, 
                'lng' => -97.5040654, 
                'status' => 'normal'
            ],
            [
                'name' => 'Planta Potabilizadora No. 2 (Puerto Rico)', 
                'lat' => 25.8501152, 
                'lng' => -97.5266917, 
                'status' => 'normal'
            ],
            [
                'name' => 'Planta Potabilizadora P. I y II (CIMA)', 
                'lat' => 25.8470718, 
                'lng' => -97.4431095, 
                'status' => 'normal'
            ],
            // Tanques Elevados / Rebombeo
            [
                'name' => 'Tanque Elevado No. 2 (La Copa)', 
                'lat' => 25.8528713, 
                'lng' => -97.4864710, 
                'status' => 'normal'
            ],
            [
                'name' => 'Tanque Elevado No. 3 (Los Ébanos)', 
                'lat' => 25.8278675, 
                'lng' => -97.5252782, 
                'status' => 'normal'
            ],
            [
                'name' => 'Tanque Elevado No. 4 (Campestre)', 
                'lat' => 25.8617681, 
                'lng' => -97.4671495, 
                'status' => 'normal'
            ],
            [
                'name' => 'Tanque Elevado No. 1 (Rigo Tovar)', 
                'lat' => 25.8762889, 
                'lng' => -97.5206935, 
                'status' => 'normal'
            ]
        ];

        foreach ($bombas as $bomba) {
            Bomba::create($bomba);
        }
    }
}