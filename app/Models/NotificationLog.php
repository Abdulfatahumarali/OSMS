<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * FR-40 to FR-44 — Status Notifications. Model scaffolded to match the
 * schema only; real email dispatch and log write-path are not yet
 * implemented. Planned for Capstone Part 2.
 */
class NotificationLog extends Model
{
    use HasFactory;

    protected $table = 'notification_logs';

    protected $fillable = ['user_id', 'trigger_event', 'channel', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
