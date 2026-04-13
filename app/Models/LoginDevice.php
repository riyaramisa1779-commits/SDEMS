<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginDevice extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'device_name',
        'last_active_at',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Parse a simple device name from the user agent string.
     */
    public static function parseDeviceName(string $userAgent): string
    {
        if (str_contains($userAgent, 'Mobile')) {
            return 'Mobile Device';
        }
        if (str_contains($userAgent, 'Tablet')) {
            return 'Tablet';
        }

        return 'Desktop Browser';
    }
}
