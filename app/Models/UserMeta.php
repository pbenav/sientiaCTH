<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada al modelo.
     * Por defecto Laravel buscaría 'user_metas'.
     * @var string
     */
    protected $table = 'user_meta';

    /**
     * Los atributos que son asignables en masa.
     * Esto es importante para las operaciones de creación y actualización seguras.
     * @var array
     */
    protected $fillable = [
        'user_id',
        'meta_key',
        'meta_value',
    ];

    /**
     * La relación con el modelo User.
     * Un metadato pertenece a un solo usuario.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}