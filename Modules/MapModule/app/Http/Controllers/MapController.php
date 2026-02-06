<?php

namespace Modules\MapModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\MapModule\Models\Map;
use Modules\MapModule\Http\Requests\Map\StoreMapRequest;
use Modules\MapModule\Http\Requests\Map\UpdateMapRequest;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Modules\MapModule\QueryBuilders\MapQueryBuilder;
use Modules\ControlModule\Helpers\SystemLogHelper;

class MapController extends Controller
{
    public function index(Request $request)
    {
        return MapQueryBuilder::fromRequest($request);
    }

    public function store(StoreMapRequest $request)
    {
        try{
            $payload = $request->validated();
            $map = Map::create($payload);
            SystemLogHelper::log(
                'map.created',
                'Created map',
                ['map_id' => $map->id, 'name' => $map->name ?? null, 'payload' => $payload]
            );
            return ApiResponse::success(null, 'Created successfully', 201);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to create map', 500, $e->getMessage());
        }
    }

    public function update(UpdateMapRequest $request, $id)
    {
        try{
            $map = Map::findOrFail($id);
            $payload = $request->validated();
            $map->update($payload);
            SystemLogHelper::log(
                'map.updated',
                'Updated map',
                ['map_id' => $map->id, 'name' => $map->name ?? null, 'payload' => $payload]
            );
            return ApiResponse::success(null, 'Updated successfully', 200);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to update map', 500, $e->getMessage());
        }
    }
    public function destroy($id)
    {
        try{
            $map = Map::findOrFail($id);
            $map->delete();
            SystemLogHelper::log(
                'map.deleted',
                'Deleted map',
                ['map_id' => $map->id, 'name' => $map->name ?? null]
            );
            return ApiResponse::success(null, 'Deleted successfully', 200);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to delete map', 500, $e->getMessage());
        }
    }
}
