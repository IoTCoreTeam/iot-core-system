<?php

namespace Modules\ControlModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ControlModule\Helpers\ApiResponse;
use Modules\ControlModule\Helpers\SystemLogHelper;
use Modules\ControlModule\Http\Requests\StoreNodeRequest;
use Modules\ControlModule\Http\Requests\StoreNodeControllerRequest;
use Modules\ControlModule\Http\Requests\StoreNodeSensorRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\ControlModule\Models\NodeController as NodeControllerModel;
use Modules\ControlModule\QueryBuilders\NodeQueryBuilder;
use Modules\ControlModule\Services\NodeManagementService;
use Modules\ControlModule\Services\NodeService;
use Illuminate\Support\Facades\DB;
use Modules\ControlModule\Models\NodeSensor;
use Throwable;

class NodeController extends Controller
{
    public function __construct(private readonly NodeService $nodeService) {}

    // device registration
    public function registation(StoreNodeRequest $request)
    {
        $payload = $request->validated();

        try {
            $result = DB::transaction(function () use ($payload) {
                $result = $this->nodeService->register($payload);
                self::sendAvailableNode();

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
                self::sendAvailableNode();

                return $result;
            });

            return ApiResponse::success(null, $result['message']);
        } catch (Throwable $e) {
            report($e);
            SystemLogHelper::log('node.deactivation_failed', $e->getMessage(), ['external_id' => $externalId], ['level' => 'error']);
            $errorMessage = config('app.debug') ? $e->getMessage() : 'Failed to delete node';
            return ApiResponse::error($errorMessage, 500);
        }
    }

    // node listing with filters
    public function index(Request $request)
    {
        return NodeQueryBuilder::fromRequest($request);
    }

    public function getActiveDevices(): JsonResponse // hàm này để client, 3rd party gọi lấy danh sách node đã đăng ký
    {
        return ApiResponse::success(NodeQueryBuilder::getActiveDevicesPayload(), 'Registered node resources fetched successfully');
    }

    public static function sendAvailableNode(): JsonResponse // hàm này để chủ động gửi danh sách node đang active cho server điều khiển+++
    {
        return app(NodeManagementService::class)->sendAvailableNode();
    }

    // Node controller handle here

    public function registerNodeController(StoreNodeControllerRequest $request)
    {
        try {
            $nodeController = DB::transaction(function () use ($request) {
                $nodeController = NodeControllerModel::updateOrCreate(['node_id' => $request->node_id],$request->validated());
                self::sendAvailableNode();
                return $nodeController;
            });
            return ApiResponse::success($nodeController, 'Node controller registered or updated successfully');
        } catch (Throwable $e) {
            report($e);
            SystemLogHelper::log('node_controller.registration_failed',$e->getMessage(),['payload' => $request->all()],['level' => 'error']);
            $errorMessage = config('app.debug')? $e->getMessage(): 'Failed to register or update node controller';
            return ApiResponse::error($errorMessage, 500);
        }
    }

    public function registerNodeSensor(StoreNodeSensorRequest $request)
    {
        try {
            $nodeSensor = DB::transaction(function () use ($request) {
                $nodeSensor = NodeSensor::updateOrCreate(['node_id' => $request->node_id],$request->validated());
                self::sendAvailableNode();
                return $nodeSensor;
            });
            return ApiResponse::success($nodeSensor, 'Node sensor registered or updated successfully');
        } catch (Throwable $e) {
            report($e);
            SystemLogHelper::log('node_sensor.registration_failed',$e->getMessage(),['payload' => $request->all()],['level' => 'error']);
            $errorMessage = config('app.debug')? $e->getMessage(): 'Failed to register or update node sensor';
            return ApiResponse::error($errorMessage, 500);
        }
    }
}
