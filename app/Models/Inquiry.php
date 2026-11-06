<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inquiry extends Model
{
    use HasFactory;

    // 'type' distinguishes a quick general question (Contact page) from an
    // actual consultation booking request (Book a Consultation page).
    public const TYPE_GENERAL = 'general';
    public const TYPE_CONSULTATION = 'consultation';

    protected $fillable = [
        'full_name', 'email', 'phone', 'nationality', 'practice_area_id', 'attorney_id', 'message',
        'client_type', 'client_location', 'legal_profile', 'client_requirements',
        'preferred_date', 'preferred_time', 'budget_range', 'consultation_method',
        'recommended_attorney_ids', 'status', 'type',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'recommended_attorney_ids' => 'array',
    ];

    public function practiceArea(): BelongsTo
    {
        return $this->belongsTo(PracticeArea::class);
    }

    public function attorney(): BelongsTo
    {
        return $this->belongsTo(Attorney::class);
    }

    public function caseRecord(): HasOne
    {
        return $this->hasOne(CaseRecord::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(InquiryMessage::class);
    }
}
