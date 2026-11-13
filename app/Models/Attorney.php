<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Attorney extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'photo_path', 'rank', 'practice_area_id', 'specialization_note',
        'sample_rating', 'sample_last_practiced_on', 'years_experience', 'sort_order',
        'is_available', 'accepting_matches', 'coverage_areas',
        'additional_practice_area_ids', 'spoken_languages',
        'budget_min', 'budget_max',
        'contact_email', 'contact_phone',
    ];

    protected $casts = [
        'sample_rating' => 'float',
        'sample_last_practiced_on' => 'date',
        'is_available' => 'boolean',
        'accepting_matches' => 'boolean',
        'coverage_areas' => 'array',
        'additional_practice_area_ids' => 'array',
        'spoken_languages' => 'array',
    ];

    public function practiceArea(): BelongsTo
    {
        return $this->belongsTo(PracticeArea::class);
    }

    public function account(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function getInitialsAttribute(): string
    {
        $parts = preg_split('/\s+/', str_replace('Atty.', '', $this->name));
        $parts = array_filter($parts);
        $initials = array_map(fn ($p) => mb_substr($p, 0, 1), $parts);

        return mb_strtoupper(implode('', array_slice($initials, 0, 2)));
    }
}
