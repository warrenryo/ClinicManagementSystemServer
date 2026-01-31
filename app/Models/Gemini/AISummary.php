<?php

namespace App\Models\Gemini;

use Illuminate\Database\Eloquent\Model;

class AISummary extends Model
{
    protected $table = 'ai_summaries';
    protected $fillable = [
        'id',
        'ai_id',
        'title',
        'summary',
        'insights',
        'confidence'
    ];

    protected $casts = [
        'insights' => 'array',
    ];
}
