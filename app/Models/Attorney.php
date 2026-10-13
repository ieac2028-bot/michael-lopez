<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attorney extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'photo_path', 'rank', 'practice_area_id', 'specialization_note',
        'sample_rating', 'sample_last_practiced_on', 'years_experience', 'sort_order',
        'is_available', 'coverage_areas',
    ];

    protected $casts = [
        'sample_rating' => 'float',
        'sample_last_practiced_on' => 'date',
        'is_available' => 'boolean',
        'coverage_areas' => 'array',
    ];

    public function practiceArea(): BelongsTo
    {
        return $this->belongsTo(PracticeArea::class);
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', str_replace('Atty.', '', $this->name));
        $parts = array_filter($parts);
        $initials = array_map(fn ($p) => mb_substr($p, 0, 1), $parts);

        return mb_strtoupper(implode('', array_slice($initials, 0, 2)));
    }
}
