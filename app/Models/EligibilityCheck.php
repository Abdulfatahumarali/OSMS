<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EligibilityCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id', 'result', 'failed_criteria',
        'is_overridden', 'overridden_by', 'override_justification', 'evaluated_at',
    ];

    protected $casts = [
        'failed_criteria' => 'array',
        'is_overridden' => 'boolean',
        'evaluated_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function overriddenBy()
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }
}
