<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'saas_user_id',
        'trial_ends_at',
        'cycle_ends_at',
        'status',
        'score',
        'is_whatsapp_opted_out',
        'is_email_opted_out',
        'last_whatsapp_received_at',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'meta',
    ];

    protected $casts = [
        'status' => LeadStatus::class,
        'trial_ends_at' => 'datetime',
        'cycle_ends_at' => 'datetime',
        'last_whatsapp_received_at' => 'datetime',
        'is_whatsapp_opted_out' => 'boolean',
        'is_email_opted_out' => 'boolean',
        'score' => 'integer',
        'meta' => 'array',
    ];

    public function flowRuns(): HasMany
    {
        return $this->hasMany(LeadFlowRun::class);
    }

    public function activeFlowRun()
    {
        return $this->hasOne(LeadFlowRun::class)->where('status', 'active');
    }

    public function events(): HasMany
    {
        return $this->hasMany(LeadEvent::class)->orderBy('occurred_at', 'desc');
    }

    public function messageLogs(): HasMany
    {
        return $this->hasMany(MessageLog::class)->orderBy('created_at', 'desc');
    }

    public function trackedLinks(): HasMany
    {
        return $this->hasMany(TrackedLink::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getFirstNameAttribute(): string
    {
        $parts = explode(' ', trim($this->name));
        return $parts[0] ?? $this->name;
    }

    public function getDaysRemainingTrialAttribute(): int
    {
        if (!$this->trial_ends_at) {
            return 0;
        }

        $diff = now()->diffInDays($this->trial_ends_at, false);
        return max(0, (int) ceil($diff));
    }

    public function isWithinWhatsApp24hWindow(): bool
    {
        if (!$this->last_whatsapp_received_at) {
            return false;
        }

        return $this->last_whatsapp_received_at->diffInHours(now()) < 24;
    }
}
