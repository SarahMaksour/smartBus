<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RouteSearchRequest;
use App\Services\RouteSearch\RouteSearchService;

class RouteSearchController extends Controller
{
    public function __construct(
        private readonly RouteSearchService $searchService,
    ) {}

    public function search(RouteSearchRequest $request)
    {
        $results = $this->searchService->search(
            fromLat: $request->float('from_lat'),
            fromLng: $request->float('from_lng'),
            toLat:   $request->float('to_lat'),
            toLng:   $request->float('to_lng'),
        );

        if (empty($results)) {
            return response()->json([
                'message' => 'لا يوجد طريق متاح حالياً',
                'data'    => [],
            ]);
        }

        return response()->json([
            'data' => array_map(fn($r) => $r->toArray(), $results),
        ]);
    }
}