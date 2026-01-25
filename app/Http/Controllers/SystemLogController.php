<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{
    public function index(Request $request)
    {
        $perpage = $request->integer('per_page', 15);
        $query = SystemLog::query()->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('level')) {
            $query->where('level', $request->query('level'));
        }

        if ($request->filled('message')) {
            $query->where('message', $request->query('message'));
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', $request->query('ip_address'));
        }

        if ($request->filled('context')) {
            $query->where('context', $request->query('context'));
        }

        if ($request->filled('keyword')) {
            $query->keyword($request->query('keyword'));
        }

        if ($request->filled('start') || $request->filled('end')) {
            $query->time($request->query('start'), $request->query('end'));
        }

        return $query->paginate($perpage);
    }

    public function count(Request $request)
    {
        return response()->json(SystemLog::countByWeekAndLevel());
    }
}
