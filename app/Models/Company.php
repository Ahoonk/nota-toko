<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'logo',
        'address',
        'phone',
        'email',
        'website',
        'npwp',
        'responsible_name',
        'responsible_position',
        'signature_path',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function itemCategories(): HasMany
    {
        return $this->hasMany(ItemCategory::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function documentTemplates(): HasMany
    {
        return $this->hasMany(DocumentTemplate::class);
    }
}
