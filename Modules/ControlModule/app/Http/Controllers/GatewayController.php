<?php

namespace Modules\ControlModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ControlModule\Helpers\ApiResponse;
use Modules\ControlModule\Helpers\SystemLogHelper;
use Modules\ControlModule\Http\Requests\StoreGatewayRequest;
use Modules\ControlModule\QueryBuilders\GatewayQueryBuilder;
use Modules\ControlModule\Services\GatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class GatewayController extends Controller
{
    public function __construct(private readonly GatewayService $gatewayService) {}

    public function registation(StoreGatewayRequest $request)
    {
        $payload = $request->validated();

        try {
            $result = DB::transaction(function () use ($payload) {

                $result = $this->gatewayService->register($payload);
                NodeController::sendAvailableNode();

                return $result;
            });

            return ApiResponse::success($result['gateway'], $result['message'], $result['status']);

        } catch (Throwable $e) {
            report($e);

            SystemLogHelper::log('gateway.registration_failed', $e->getMessage(), ['payload' => $payload, 'external_id' => $payload['external_id'] ?? null], ['level' => 'error']);

            $errorMessage = config('app.debug') ? $e->getMessage() : 'Failed to register gateway';

            return ApiResponse::error($errorMessage, 500);
        }
    }

    public function deactivation(string $externalId)
    {
        try {
            $result = DB::transaction(function () use ($externalId) {

                $result = $this->gatewayService->deactivate($externalId);
                NodeController::sendAvailableNode();

                return $result;
            });

            return ApiResponse::success(null, $result['message']);

        } catch (Throwable $e) {
            report($e);
            SystemLogHelper::log('gateway.deactivation_failed', $e->getMessage(), ['external_id' => $externalId], ['level' => 'error']);
            $errorMessage = config('app.debug') ? $e->getMessage() : 'Failed to deactivate gateway';
            return ApiResponse::error($errorMessage, 500);
        }
    }

    public function index(Request $request)
    {
        return GatewayQueryBuilder::fromRequest($request);
    }

    public function delete($external_id)
    {
        try {
            DB::transaction(function () use ($external_id) {
                $this->gatewayService->deleteByExternalId($external_id);
                NodeController::sendAvailableNode();
            });

            return ApiResponse::success(null, 'Gateway soft deleted successfully');
        } catch (Throwable $e) {
            report($e);

            SystemLogHelper::log('gateway.deletion_failed', $e->getMessage(), ['external_id' => $external_id], ['level' => 'error']);

            $errorMessage = config('app.debug') ? $e->getMessage() : 'Failed to delete gateway';

            return ApiResponse::error($errorMessage, 500);
        }
    }
}
