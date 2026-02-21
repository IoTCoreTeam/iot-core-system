<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkflowRequest;
use App\Http\Requests\UpdateWorkflowRequest;
use App\Models\Workflow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\ControlModule\Helpers\ApiResponse;

class WorkflowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);
        $query = Workflow::query();

        if ($request->filled('id')) {
            $query->where('id', $request->query('id'));
        }

        if ($request->filled('name')) {
            $name = (string) $request->query('name');
            $query->where('name', 'like', "%{$name}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('created_from') || $request->filled('created_to')) {
            $createdFrom = $request->query('created_from');
            $createdTo = $request->query('created_to');
            try {
                $from = $createdFrom ? Carbon::parse($createdFrom) : null;
                $to = $createdTo ? Carbon::parse($createdTo) : null;
                if ($from && $to) {
                    $query->whereBetween('created_at', [$from, $to]);
                } elseif ($from) {
                    $query->where('created_at', '>=', $from);
                } elseif ($to) {
                    $query->where('created_at', '<=', $to);
                }
            } catch (\Throwable $e) {
                // Ignore invalid date filters.
            }
        }

        if ($request->filled('search')) {
            $keyword = (string) $request->query('search');
            $query->where(function ($searchQuery) use ($keyword) {
                $searchQuery->where('name', 'like', "%{$keyword}%")
                    ->orWhere('status', 'like', "%{$keyword}%")
                    ->orWhere('id', 'like', "%{$keyword}%");
            });
        }

        return $query->orderByDesc('updated_at')->paginate($perPage);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkflowRequest $request)
    {
        $payload = $request->validated();
        $workflow = Workflow::create($payload);

        return ApiResponse::success($workflow->refresh(), 'Workflow created successfully', 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkflowRequest $request, Workflow $workflow)
    {
        $payload = $request->validated();
        $workflow->update($payload);

        return ApiResponse::success($workflow->refresh(), 'Workflow updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workflow $workflow)
    {
        $workflow->delete();

        return ApiResponse::success(null, 'Workflow deleted successfully');
    }
}
