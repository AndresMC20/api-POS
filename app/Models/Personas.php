<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personas extends Model
{
    protected $primaryKey = 'id_persona';
    use HasFactory;

    public function empresas()
    {
        return $this->belongsToMany(Empresas::class, 'empresas_personas', 'id_persona', 'id_empresa');
    }
}
