<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresas extends Model
{
    protected $primaryKey = 'id_empresa';
    use HasFactory;

    public function personas()
    {
        return $this->belongsToMany(Personas::class, 'empresas_personas', 'id_empresa', 'id_persona');
    }
}
