<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no', 'client_email', 'title', 'attorney_id', 'inquiry_id', 'practice_area_id', 'timeline',
        'status', 'submitted_at', 'accepted_at', 'resolved_at', 'outcome', 'testimony',
    ];

    protected $casts = [
        'timeline' => 'array',
        'submitted_at' => 'datetime',
        'accepted_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function attorney(): BelongsTo
    {
        return $this->belongsTo(Attorney::class);
    }

    public function practiceArea(): BelongsTo
    {
        return $this->belongsTo(PracticeArea::class);
    }
}
