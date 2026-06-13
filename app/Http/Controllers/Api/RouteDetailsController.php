<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RouteDetailsRequest;
use App\Services\RouteSearch\RouteDetailsBuilder;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class RouteDetailsController extends Controller
{
    public function __construct(
        private readonly RouteDetailsBuilder $detailsBuilder,
    ) {}

    public function show(RouteDetailsRequest $request, string $token): JsonResponse
    {
        try {
            $details = $this->detailsBuilder->build(
                token: $token,
                toLat: $request->float('to_lat'),
                toLng: $request->float('to_lng'),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }

        return response()->json([
            'data' => $details->toArray(),
        ]);
    }
}