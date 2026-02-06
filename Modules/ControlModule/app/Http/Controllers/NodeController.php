<?php

namespace Modules\ControlModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ControlModule\Helpers\ApiResponse;
use Modules\ControlModule\Helpers\SystemLogHelper;
use Modules\ControlModule\Http\Requests\StoreNodeRequest;
use Illuminate\Http\Request;
use Modules\ControlModule\QueryBuilders\NodeQueryBuilder;
use Modules\ControlModule\Services\NodeService;
use Illuminate\Support\Facades\DB;
use Throwable;

class NodeController extends Controller
{
    public function __construct(private readonly NodeService $nodeService) {}

    public function registation(StoreNodeRequest $request)
    {
        $payload = $request->validated();

        try {
            $result = DB::transaction(function () use ($payload) {
                $result = $this->nodeService->register($payload);
                NodeManagementController::sendAvailableNode();

                return $result;
            });

            return ApiResponse::success($result['node'], $result['message'], $result['status']);
        } catch (Throwable $e) {
            report($e);

            SystemLogHelper::log('node.registration_failed', $e->getMessage(), ['payload' => $payload, 'external_id' => $payload['external_id'] ?? null], ['level' => 'error']);

            $errorMessage = config('app.debug') ? $e->getMessage() : 'Failed to register node';

            return ApiResponse::error($errorMessage, 500);
        }
    }

    public function deactivation(string $externalId)
    {
        try {
            $result = DB::transaction(function () use ($externalId) {
                $result = $this->nodeService->deactivate($externalId);
                NodeManagementController::sendAvailableNode();

                return $result;
            });

            return ApiResponse::success(null, $result['message']);
        } catch (Throwable $e) {
            report($e);
            SystemLogHelper::log('node.deactivation_failed', $e->getMessage(), ['external_id' => $externalId], ['level' => 'error']);
            $errorMessage = config('app.debug') ? $e->getMessage() : 'Failed to deactivate node';
            return ApiResponse::error($errorMessage, 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return NodeQueryBuilder::fromRequest($request);
    }

}
