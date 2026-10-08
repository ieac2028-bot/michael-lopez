<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inquiry extends Model
{
    use HasFactory;

    // 'type' distinguishes a quick general question (Contact page) from an
    // actual consultation booking request (Book a Consultation page).
    public const TYPE_GENERAL = 'general';
    public const TYPE_CONSULTATION = 'consultation';

    protected $fillable = [
        'full_name', 'email', 'phone', 'practice_area_id', 'message',
        'preferred_date', 'preferred_time', 'status', 'type',
    ];

    protected $casts = [
        'preferred_date' => 'date',
    ];

    public function practiceArea(): BelongsTo
    {
        return $this->belongsTo(PracticeArea::class);
    }
}
