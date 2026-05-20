<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\LecturasBomba;

class Bomba extends Model {
    protected $fillable = ['name', 'lat', 'lng', 'status'];

    // Relación original
    public function readings() {
        return $this->hasMany(LecturasBomba::class);
    }

    // Relación serializada como 'lecturas' para el frontend
    public function getLecturasAttribute() {
        return $this->readings()->latest()->take(15)->get();
    }

    // Para que 'lecturas' aparezca en el JSON
    protected $appends = ['lecturas'];
}
