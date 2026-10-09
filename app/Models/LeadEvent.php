<?php

namespace App\Models;

use App\Enums\LeadEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'event_type',
        'channel',
        'title',
        'description',
        'metadata',
        'score_delta',
        'occurred_at',
    ];

    protected $casts = [
        'event_type' => LeadEventType::class,
        'metadata' => 'array',
        'score_delta' => 'integer',
        'occurred_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
