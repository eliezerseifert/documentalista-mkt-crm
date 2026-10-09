<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackedLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'lead_id',
        'flow_step_id',
        'destination_url',
        'clicks_count',
        'first_clicked_at',
        'last_clicked_at',
    ];

    protected $casts = [
        'clicks_count' => 'integer',
        'first_clicked_at' => 'datetime',
        'last_clicked_at' => 'datetime',
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
