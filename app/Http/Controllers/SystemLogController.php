<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{

    
    public function index(Request $request)
    {
        $perPage = SystemLog::normalizePerPage($request->integer('per_page'), 15);
        if ($request->filled('id')) {
            return SystemLog::findWithUser($request->query('id'));
        }

        if ($request->filled('search') || $request->filled('keyword')) {
            return SystemLog::searchByKeywords([
                $request->query('search'),
                $request->query('keyword'),
            ], $perPage);
        }

        $filters = SystemLog::normalizeFilters([
            'action' => $request->query('action'),
            'levels' => $request->query('level'),
            'user_id' => $request->query('user_id'),
            'ip_address' => $request->query('ip_address') ?? $request->query('ip'),
            'start' => $request->query('start'),
            'end' => $request->query('end'),
        ]);
        return SystemLog::filterLogs($filters, $perPage);
    }

    public function show(SystemLog $systemLog)
    {
        return ApiResponse::success(
            $systemLog->load('user:id,name,email')
        );
    }

}
