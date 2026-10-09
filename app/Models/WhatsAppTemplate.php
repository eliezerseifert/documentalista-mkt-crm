<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'language',
        'category',
        'status',
        'body_preview',
        'variables_mapping',
    ];

    protected $casts = [
        'variables_mapping' => 'array',
    ];
}
