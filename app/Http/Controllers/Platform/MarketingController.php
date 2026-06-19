<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Inertia\Inertia;

class MarketingController extends Controller
{
    public function home()
    {
        return Inertia::render('Platform/Auth/Login');
    }

    public function pricing()
    {
        return Inertia::render('Platform/Billing/Index', [
            'plans' => Plan::where('active', true)->get(),
        ]);
    }
}
