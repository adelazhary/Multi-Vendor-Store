<?php

namespace App\Http\Controllers\Api;

use App\Events\DeliveryLocationUpdated;
use App\Http\Controllers\Controller;
use App\Http\Resources\DeliveryResource;
use App\Models\Delivery;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveriesController extends Controller
{
    use ApiResponses;
    public function show(Delivery $delivery)
    {
        return $this->successResponse(new DeliveryResource($delivery));
    }
    public function update(Request $request, Delivery $delivery)
    {
        $request->validate([
            'lng' => 'required|numeric',
            'lat' => 'required|numeric',
        ]);


        $delivery->update($request->only('lat', 'lng'));

        event(new DeliveryLocationUpdated($request->lat, $request->lng));

        return response()->json($delivery, 200);
    }
}
