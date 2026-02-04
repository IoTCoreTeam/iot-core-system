<?php

namespace Modules\MapModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\MapModule\Models\AccessPoint;
use Modules\MapModule\Http\Requests\AccessPoint\StoreAccessPointRequest;
use Modules\MapModule\Http\Requests\AccessPoint\UpdateAccessPointRequest;

class AccessPointController extends Controller
{
    public function index()
    {
        return response()->json(AccessPoint::all());
    }

    public function store(StoreAccessPointRequest $request)
    {
        $accessPoint = AccessPoint::create($request->validated());
        return response()->json($accessPoint, 201);
    }

    public function show($id)
    {
        return response()->json(AccessPoint::findOrFail($id));
    }

    public function update(UpdateAccessPointRequest $request, $id)
    {
        $accessPoint = AccessPoint::findOrFail($id);
        $accessPoint->update($request->validated());
        return response()->json($accessPoint);
    }

    public function destroy($id)
    {
        $accessPoint = AccessPoint::findOrFail($id);
        $accessPoint->delete();
        return response()->json(null, 204);
    }
}
