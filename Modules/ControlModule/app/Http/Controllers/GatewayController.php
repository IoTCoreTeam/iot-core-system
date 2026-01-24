<?php

namespace Modules\ControlModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ControlModule\Exceptions\GatewayConflictException;
use Modules\ControlModule\Helpers\ApiResponse;
use Modules\ControlModule\Helpers\SystemLogHelper;
use Modules\ControlModule\Http\Requests\StoreGatewayRequest;
use Modules\ControlModule\Models\Gateway;
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
                NodeManagementController::sendAvailableNode();

                return $result;
            });

            return ApiResponse::success($result['gateway'], $result['message'], $result['status']);
        } catch (GatewayConflictException $e) {
            return ApiResponse::error($e->getMessage(), 409);
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
                NodeManagementController::sendAvailableNode();

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
        $perPage = $request->integer('per_page', 5);

        if ($request->has('external_id')) {
            return Gateway::where('external_id', $request->query('external_id'))->paginate($perPage);
        }

        if ($request->has('name')) {
            return Gateway::where('name', $request->query('name'))->paginate($perPage);
        }

        if ($request->has('location')) {
            return Gateway::where('location', $request->query('location'))->paginate($perPage);
        }

        if ($request->has('ip_address')) {
            return Gateway::where('ip_address', $request->query('ip_address'))->paginate($perPage);
        }

        if ($request->has('registration_status')) {
            $statusParam = $request->query('registration_status');
            $booleanStatus = filter_var($statusParam, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($booleanStatus !== null) {
                return Gateway::where('registration_status', $booleanStatus)->paginate($perPage);
            }
        }

        if ($request->has('search')) {
            $keyword = $request->query('search');

            return Gateway::search($keyword)->paginate($perPage);
        }

        return Gateway::paginate($perPage);
    }

    public function delete($external_id)
    {
        try {
            DB::transaction(function () use ($external_id) {
                $this->gatewayService->deleteByExternalId($external_id);
                NodeManagementController::sendAvailableNode();
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
