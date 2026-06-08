<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusListRequest;
use App\Http\Resources\BusLineResource;
use App\Services\Contracts\BusServiceInterface;

class BusController extends Controller
{
    public function __construct(
        private readonly BusServiceInterface $busService,
    ) {}

    public function index(BusListRequest $request)
    {
        $lines = $this->busService->getAllLines(
            search: $request->string('search')->value(),
        );

        return BusLineResource::collection($lines);
    }
}