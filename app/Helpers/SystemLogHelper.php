<?php

namespace App\Helpers;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;

class SystemLogHelper
{
    /**
     * Persist a system log entry with sensible defaults.
     *
     * @param  string  $action   Short action key describing what happened.
     * @param  string|null  $message  Optional human-readable message.
     * @param  array<string,mixed>  $context  Extra payload that will be stored as JSON.
     * @param  array<string,mixed>  $options  Supported keys: level, user_id, ip_address.
     */
    public static function log(string $action, ?string $message = null, array $context = [], array $options = []): SystemLog
    {
        $logData = [
            'action' => $action,
            'message' => $message,
            'context' => $context === [] ? null : $context,
            'level' => $options['level'] ?? 'info',
            'user_id' => $options['user_id'] ?? Auth::id(),
            'ip_address' => $options['ip_address'] ?? request()?->ip(),
        ];

        return SystemLog::create(array_filter(
            $logData,
            static fn ($value) => $value !== null
        ));
    }
}
