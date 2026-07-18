<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no', 'user_id', 'scholarship_id',
        'programme_of_study', 'year_of_study', 'nationality', 'gpa_submitted',
        'financial_need_declared', 'personal_statement', 'referee_name', 'referee_email',
        'status', 'current_stage_order', 'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'financial_need_declared' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function eligibilityCheck()
    {
        return $this->hasOne(EligibilityCheck::class);
    }

    public function reviewDecisions()
    {
        return $this->hasMany(ReviewDecision::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function disbursement()
    {
        return $this->hasOne(Disbursement::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
