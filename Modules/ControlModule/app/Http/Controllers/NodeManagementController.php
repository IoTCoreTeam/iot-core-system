<?php

namespace Modules\ControlModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ControlModule\Helpers\ApiResponse;
use Modules\ControlModule\Models\Gateway;
use Modules\ControlModule\Models\Node;
use Modules\ControlModule\Models\NodeController;
use Modules\ControlModule\Models\NodeSensor;
use Modules\ControlModule\Services\NodeManagementService;
use Illuminate\Http\JsonResponse;

class NodeManagementController extends Controller
{
    /**
     * Return all active, non-deleted gateways, node controllers, and node sensors.
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success([
            'gateways'         => Gateway::where('registration_status', true)->pluck('external_id')->filter()->values()->all(),
            'nodes'            => Node::where('registration_status', 'registered')->pluck('external_id')->filter()->values()->all(),
            'node_controllers' => NodeController::whereHas('node', function ($query) {$query->where('registration_status', 'registered');})->with('node:id,external_id')->get()->pluck('node.external_id')->filter()->values()->all(),
            'node_sensors'     => NodeSensor::whereHas('node', function ($query) {$query->where('registration_status', 'registered');})->with('node:id,external_id')->get()->pluck('node.external_id')->filter()->values()->all(),
        ], 'Registered node resources fetched successfully');
    }

    // this function sends available nodes to server
    public static function sendAvailableNode(): JsonResponse
    {
        return app(NodeManagementService::class)->sendAvailableNode();
    }
}
