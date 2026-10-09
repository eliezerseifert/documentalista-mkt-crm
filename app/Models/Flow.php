<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'total_days',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_days' => 'integer',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(FlowStep::class)->orderBy('step_number');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(LeadFlowRun::class);
    }
}
