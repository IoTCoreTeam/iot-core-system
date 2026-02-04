<?php

namespace Modules\MapModule\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Modules\MapModule\Models\Area;
use Modules\MapModule\Http\Requests\Area\StoreAreaRequest;
use Modules\MapModule\Http\Requests\Area\UpdateAreaRequest;
use Illuminate\Http\Request;
use Modules\MapModule\QueryBuilders\AreaQueryBuilder;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        return AreaQueryBuilder::fromRequest($request);
    }

    public function store(StoreAreaRequest $request)
    {
        try{
            Area::create($request->validated());
            return ApiResponse::success(null, ['message' => 'Created successfully'], 201);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to create map', 500, $e->getMessage());
        }
    }

    public function update(UpdateAreaRequest $request, $id)
    {
        try{
            $area = Area::findOrFail($id);
            $area->update($request->validated());
            return ApiResponse::success(null, ['message' => 'Updated successfully'], 200);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to update area', 500, $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try{
            $area = Area::findOrFail($id);
            $area->delete();
            return ApiResponse::success(null, ['message' => 'Deleted successfully'], 200);
        } catch (\Throwable $e) {
            return ApiResponse::error('Failed to delete area', 500, $e->getMessage());
        }
    }
}
