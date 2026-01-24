<?php

namespace Modules\ControlModule\Helpers;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SystemLogHelper
{
    /**
     * Log an action to the system logs.
     *
     * @param string $action The action being performed (e.g., 'gateway.registered')
     * @param string $message A description of the action
     * @param array $context Additional data for context
     * @param array $options Additional options (e.g., level)
     */
    public static function log(string $action, string $message, array $context = [], array $options = []): void
    {
        $level = $options['level'] ?? 'info';

        // Log to file system
        Log::channel('stack')->$level($message, array_merge([
            'action' => $action,
        ], $context));

        // Log to database using global SystemLog model
        try {
            SystemLog::create([
                'user_id'    => Auth::id(),
                'action'     => $action,
                'level'      => $level,
                'message'    => $message,
                'context'    => $context,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to save system log to database: ' . $e->getMessage());
        }
    }
}
