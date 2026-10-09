<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFlowRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'flow_id',
        'current_step_number',
        'started_at',
        'paused_at',
        'completed_at',
        'cancelled_at',
        'status',
    ];

    protected $casts = [
        'current_step_number' => 'integer',
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function flow(): BelongsTo
    {
        return $this->belongsTo(Flow::class);
    }
}
