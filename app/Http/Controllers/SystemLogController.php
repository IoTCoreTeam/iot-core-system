<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    /**
     * Display a paginated listing of system logs with filtering support.
     */
    public function index(Request $request)
    {
        $perPage = SystemLog::normalizePerPage($request->integer('per_page'), 15);
        $filters = SystemLog::normalizeFilters($request->query());

        $logs = SystemLog::fetchForListing($filters, $perPage);

        return ApiResponse::success($logs);
    }

    /**
     * Display the specified system log entry.
     */
    public function show(SystemLog $systemLog)
    {
        return ApiResponse::success($systemLog->load('user:id,name,email'));
    }
}
