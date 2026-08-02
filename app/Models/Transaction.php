<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'created_by',
        'updated_by',
        'transaction_number',
        'transaction_date',
        'customer_name',
        'company_name',
        'description',
        'work_type',
        'work_location',
        'work_duration',
        'brought_item_id',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'words',
        'notes',
        'status',
        'template_snapshot',
        'printed_at',
        'printed_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'printed_at' => 'datetime',
        'template_snapshot' => 'array',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function broughtItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'brought_item_id');
    }

    public function broughtItems(): HasMany
    {
        return $this->hasMany(TransactionBroughtItem::class)->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function printer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class)->orderBy('sort_order');
    }
}
