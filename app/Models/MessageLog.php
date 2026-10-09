<?php

namespace App\Models;

use App\Enums\MessageDeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'flow_step_id',
        'channel',
        'direction',
        'status',
        'external_message_id',
        'content_sent',
        'error_message',
        'sent_at',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'status' => MessageDeliveryStatus::class,
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function flowStep(): BelongsTo
    {
        return $this->belongsTo(FlowStep::class);
    }
}
