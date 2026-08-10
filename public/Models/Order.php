<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'orders';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false; // Usa CreatedAt manual ou gerencia customizado

    protected $fillable = [
        'id',
        'customer',
        'status',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'order_id', 'id');
    }
}