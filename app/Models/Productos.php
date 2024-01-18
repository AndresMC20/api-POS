<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productos extends Model
{
    protected $primaryKey = 'id_producto';
    use HasFactory;

    // Definir la relación con la tabla "categorias"
    public function categoria()
    {
        return $this->belongsTo(Categorias::class, 'id_categoria');
    }

    // Definir la relación con la tabla "pedidos"
    public function pedidos()
    {
        return $this->belongsToMany(Pedidos::class, 'detalles', 'id_producto', 'id_pedido');
    }
    

}
