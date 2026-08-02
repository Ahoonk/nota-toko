<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrintLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'document_template_id',
        'printed_by',
        'document_type',
        'template_path',
        'generated_path',
        'status',
        'metadata',
        'printed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'printed_at' => 'datetime',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }
}
