<?php

namespace Modules\MapModule\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Modules\MapModule\Models\Area;
use Modules\MapModule\Http\Requests\Area\StoreAreaRequest;
use Modules\MapModule\Http\Requests\Area\UpdateAreaRequest;
use Illuminate\Http\Request;
use Modules\MapModule\QueryBuilders\AreaQueryBuilder;
use Modules\ControlModule\Helpers\SystemLogHelper;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        return AreaQueryBuilder::fromRequest($request);
    }

    public function store(StoreAreaRequest $request)
    {
        try{
            $payload = $request->validated();
            $area = Area::create($payload);
            SystemLogHelper::log(
                'area.created',
                'Created area',
                ['area_id' => $area->id, 'name' => $area->name ?? null, 'payload' => $payload]
            );
            return ApiResponse::success(null, 'Created successfully', 201);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to create map', 500, $e->getMessage());
        }
    }

    public function update(UpdateAreaRequest $request, $id)
    {
        try{
            $area = Area::findOrFail($id);
            $payload = $request->validated();
            $area->update($payload);
            SystemLogHelper::log(
                'area.updated',
                'Updated area',
                ['area_id' => $area->id, 'name' => $area->name ?? null, 'payload' => $payload]
            );
            return ApiResponse::success(null, 'Updated successfully', 200);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to update area', 500, $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try{
            $area = Area::findOrFail($id);
            $area->delete();
            SystemLogHelper::log(
                'area.deleted',
                'Deleted area',
                ['area_id' => $area->id, 'name' => $area->name ?? null]
            );
            return ApiResponse::success(null, 'Deleted successfully', 200);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to delete area', 500, $e->getMessage());
        }
    }
}
