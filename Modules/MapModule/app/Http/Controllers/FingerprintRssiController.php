<?php

namespace Modules\MapModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\MapModule\Models\FingerprintRssi;
use Modules\MapModule\Http\Requests\FingerprintRssi\StoreFingerprintRssiRequest;
use Illuminate\Http\Request;

class FingerprintRssiController extends Controller
{
    public function index()
    {
        return response()->json(FingerprintRssi::all());
    }

    public function store(StoreFingerprintRssiRequest $request)
    {
        $rssi = FingerprintRssi::create($request->validated());
        return response()->json($rssi, 201);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'fingerprint_id' => 'required',
            'access_point_id' => 'required',
        ]);

        $deleted = FingerprintRssi::where('fingerprint_id', $request->fingerprint_id)
            ->where('access_point_id', $request->access_point_id)
            ->delete();

        if ($deleted) {
            return response()->json(null, 204);
        }

        return response()->json(['message' => 'Not found'], 404);
    }
}
