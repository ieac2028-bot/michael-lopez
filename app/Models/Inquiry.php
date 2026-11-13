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
        'full_name', 'email', 'client_id', 'phone', 'nationality', 'practice_area_id', 'attorney_id', 'message',
        'client_type', 'client_location', 'legal_profile', 'client_requirements',
        'preferred_date', 'preferred_time', 'budget_range', 'consultation_method',
        'recommended_attorney_ids', 'status', 'type',
        'consultation_fee', 'proposed_consultation_fee', 'payment_status', 'payment_instructions', 'payment_refund_terms', 'fee_proposed_by', 'fee_proposed_at',
        'fee_confirmed_by', 'fee_confirmed_at', 'payment_expires_at', 'payment_requested_at', 'payment_terms_accepted_at', 'payment_reference',
        'payment_verification_started_by', 'payment_verification_started_at', 'payment_verified_by',
        'payment_verified_at', 'payment_canceled_at', 'payment_expired_at',
        'refund_requested_at',
        'meeting_access_url', 'consultation_access_details',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'recommended_attorney_ids' => 'array',
        'consultation_fee' => 'decimal:2',
        'proposed_consultation_fee' => 'decimal:2',
        'fee_proposed_at' => 'datetime',
        'fee_confirmed_at' => 'datetime',
        'payment_expires_at' => 'datetime',
        'payment_requested_at' => 'datetime',
        'payment_terms_accepted_at' => 'datetime',
        'payment_verification_started_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'payment_canceled_at' => 'datetime',
        'payment_expired_at' => 'datetime',
        'refund_requested_at' => 'datetime',
    ];

    public function practiceArea(): BelongsTo
    {
        return $this->belongsTo(PracticeArea::class);
    }

    public function attorney(): BelongsTo
    {
        return $this->belongsTo(Attorney::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function caseRecord(): HasOne
    {
        return $this->hasOne(CaseRecord::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(InquiryMessage::class);
    }

    public function paymentVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }
}
