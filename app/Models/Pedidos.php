<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedidos extends Model
{
    protected $primaryKey = 'id_pedido';
    use HasFactory;
    public $timestamps = false;

    // Definir la relación con la tabla "personas"
    public function persona()
    {
        return $this->belongsTo(Personas::class, 'id_persona');
    }

    // Definir la relación con la tabla "empresas"
    public function empresa()
    {
        return $this->belongsTo(Empresas::class, 'id_empresa');
    }

    // Definir la relación con la tabla "productos"
    public function productos()
    {
        return $this->belongsToMany(Productos::class, 'detalles', 'id_pedido', 'id_producto');
    }
}
