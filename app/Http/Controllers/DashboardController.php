<?php

namespace App\Http\Controllers;

use App\Services\DashboardMetricsService;
use App\Support\PeriodeTenant;
use App\Support\ProfilMbgTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard', [
            'title' => 'Dasbor',
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $metrics = new DashboardMetricsService(ProfilMbgTenant::id(), PeriodeTenant::id());

        return response()->json($metrics->all());
    }
}
