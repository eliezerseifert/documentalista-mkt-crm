<?php

namespace App\Models;

use App\Enums\FlowChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'flow_id',
        'step_number',
        'day_offset',
        'send_time',
        'channel',
        'is_active',
        'is_promotion',
        'promo_coupon_code',
        'promo_expires_in_days',
        'conditions',
    ];

    protected $casts = [
        'step_number' => 'integer',
        'day_offset' => 'integer',
        'channel' => FlowChannel::class,
        'is_active' => 'boolean',
        'is_promotion' => 'boolean',
        'promo_expires_in_days' => 'integer',
        'conditions' => 'array',
    ];

    public function flow(): BelongsTo
    {
        return $this->belongsTo(Flow::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(StepMessage::class);
    }

    public function whatsappMessage()
    {
        return $this->hasOne(StepMessage::class)->where('channel', 'whatsapp');
    }

    public function emailMessage()
    {
        return $this->hasOne(StepMessage::class)->where('channel', 'email');
    }
}
