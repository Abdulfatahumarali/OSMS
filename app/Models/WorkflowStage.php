<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholarship_id', 'name', 'stage_order', 'assigned_user_id', 'assigned_role',
    ];

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function reviewDecisions()
    {
        return $this->hasMany(ReviewDecision::class);
    }
}
