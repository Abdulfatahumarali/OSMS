<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * FR-33 to FR-39 — Disbursement Tracking. Model scaffolded to match the
 * schema only; no business logic (auto-creation on approval, status
 * transitions, duplicate prevention) has been implemented yet. Planned
 * for Capstone Part 2.
 */
class Disbursement extends Model
{
    use HasFactory;

    protected $fillable = ['application_id', 'amount', 'status', 'payment_reference', 'payment_date'];

    protected $casts = ['payment_date' => 'date'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
