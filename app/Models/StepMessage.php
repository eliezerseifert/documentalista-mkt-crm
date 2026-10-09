<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StepMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'flow_step_id',
        'channel',
        'subject',
        'body_text',
        'image_path',
        'button_label',
        'button_url',
        'whatsapp_template_name',
    ];

    public function flowStep(): BelongsTo
    {
        return $this->belongsTo(FlowStep::class);
    }
}
