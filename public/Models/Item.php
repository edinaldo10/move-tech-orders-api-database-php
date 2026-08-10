<?php

namespace Models; 

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $table = 'items';
    
    // Mantido caso utilize UUID/String para os itens
    protected $keyType = 'string';
    public $incrementing = false;
    
    public $timestamps = false;

    protected $fillable = [
        'id',
        'order_id',
        'sku',
        'description',
        'quantity'
    ];

    protected $casts = [
        'quantity' => 'integer', // Garante que o Eloquent trate quantity como número inteiro
    ];

    /**
     * Relacionamento com o pedido pertencente.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}