<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'item_id',
        'unit_id',
        'item_name',
        'item_category_name',
        'brand',
        'replacement_item_name',
        'qty',
        'unit_name',
        'price',
        'modal',
        'discount',
        'total',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'price' => 'decimal:2',
        'modal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function getProfitAmountAttribute(): float
    {
        return ((float) $this->qty * ((float) $this->price - (float) $this->modal)) - (float) $this->discount;
    }
}
