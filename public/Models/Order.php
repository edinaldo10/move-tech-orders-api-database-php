<?php

namespace App\Models; // Ajustado para o namespace padrão do Laravel (App\Models)

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'orders';
    
    // Se a sua chave primária for um UUID ou String, mantenha assim:
    protected $keyType = 'string';
    public $incrementing = false;

    // Se você quer gerenciar o created_at manualmente mas quer que o Eloquent 
    // trate ele como data, o ideal é manter public $timestamps = true, 
    // mas desativar o updated_at caso ele não exista na tabela:
    public $timestamps = true;
    const UPDATED_AT = null; // Desativa o updated_at se a tabela só tem created_at

    protected $fillable = [
        'id',
        'customer',
        'status',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relacionamento com os itens do pedido.
     */
    public function items(): HasMany
    {
        // Se a foreign key na tabela 'items' for 'order_id', 
        // o Laravel já deduz isso automaticamente, mas explicitar garante segurança.
        return $this->hasMany(Item::class, 'order_id', 'id');
    }
}