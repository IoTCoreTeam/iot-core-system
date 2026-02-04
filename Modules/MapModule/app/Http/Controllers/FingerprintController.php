<?php

namespace Modules\MapModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\MapModule\Models\Fingerprint;
use Modules\MapModule\Http\Requests\Fingerprint\StoreFingerprintRequest;
use Modules\MapModule\Http\Requests\Fingerprint\UpdateFingerprintRequest;

class FingerprintController extends Controller
{
    public function index(Request $request)
    {
        $query = Fingerprint::query();

        if ($request->has('map_id')) {
            $query->where('map_id', $request->map_id);
        }

        return response()->json($query->with('rssi')->get());
    }

    public function store(StoreFingerprintRequest $request)
    {
        $fingerprint = Fingerprint::create($request->validated());
        return response()->json($fingerprint, 201);
    }

    public function show($id)
    {
        return response()->json(Fingerprint::with('rssi')->findOrFail($id));
    }

    public function update(UpdateFingerprintRequest $request, $id)
    {
        $fingerprint = Fingerprint::findOrFail($id);
        $fingerprint->update($request->validated());
        return response()->json($fingerprint);
    }

    public function destroy($id)
    {
        $fingerprint = Fingerprint::findOrFail($id);
        $fingerprint->delete();
        return response()->json(null, 204);
    }
}
