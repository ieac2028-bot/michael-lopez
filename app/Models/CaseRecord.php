<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseRecord extends Model
{
    use HasFactory;

    protected $fillable = ['reference_no', 'client_email', 'title', 'attorney_id', 'timeline'];

    protected $casts = [
        'timeline' => 'array',
    ];

    public function attorney(): BelongsTo
    {
        return $this->belongsTo(Attorney::class);
    }
}
