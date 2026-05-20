<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LecturasBomba extends Model {
    protected $fillable = ['bomba_id', 'ph', 'turb', 'ppm', 'temp', 'flujo', 'presion'];
}