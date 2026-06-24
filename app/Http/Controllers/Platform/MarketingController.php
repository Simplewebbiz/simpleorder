<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Inertia\Inertia;

class MarketingController extends Controller
{
    public function home()
    {
        return Inertia::render('Platform/Marketing/Home', [
            'plans' => $this->activePlans(),
        ]);
    }

    public function about()
    {
        return Inertia::render('Platform/Marketing/About');
    }

    public function plans()
    {
        return Inertia::render('Platform/Marketing/Plans', [
            'plans' => $this->activePlans(),
        ]);
    }

    public function contact()
    {
        return Inertia::render('Platform/Marketing/Contact');
    }

    private function activePlans()
    {
        return Plan::where('is_active', true)->orderBy('sort')->get();
    }
}