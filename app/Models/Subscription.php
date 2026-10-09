<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'plan_name',
        'amount',
        'transaction_id',
        'subscribed_at',
        'payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'subscribed_at' => 'datetime',
        'payload' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
