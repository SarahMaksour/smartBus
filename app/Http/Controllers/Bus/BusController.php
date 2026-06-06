<?php

namespace App\Http\Controllers\Bus;

use App\Services\BusService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BusController extends Controller
{
    protected $service;

    public function __construct(BusService $service)
    {
        $this->service = $service;
    }
    public function index()
    {
        return response()->json([
            'buses' => $this->service->getAllBuses()
        ]);
    }
     public function show($id)
    {
        return response()->json(
            $this->service->getById($id)
        );
    }
}
