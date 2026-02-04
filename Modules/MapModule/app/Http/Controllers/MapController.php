<?php

namespace Modules\MapModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\MapModule\Models\Map;
use Modules\MapModule\Http\Requests\Map\StoreMapRequest;
use Modules\MapModule\Http\Requests\Map\UpdateMapRequest;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Modules\MapModule\QueryBuilders\MapQueryBuilder;

class MapController extends Controller
{
    public function index(Request $request)
    {
        return MapQueryBuilder::fromRequest($request);
    }

    public function store(StoreMapRequest $request)
    {
        try{
            Map::create($request->validated());
            return ApiResponse::success(null, ['message' => 'Created successfully'], 201);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to create map', 500, $e->getMessage());
        }
    }

    public function update(UpdateMapRequest $request, $id)
    {
        try{
            $map = Map::findOrFail($id);
            $map->update($request->validated());
            return ApiResponse::success(null, ['message' => 'Updated successfully'], 200);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to update map', 500, $e->getMessage());
        }
    }
    public function destroy($id)
    {
        try{
            $map = Map::findOrFail($id);
            $map->delete();
            return ApiResponse::success(null, ['message' => 'Deleted successfully'], 200);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to delete map', 500, $e->getMessage());
        }
    }
}
