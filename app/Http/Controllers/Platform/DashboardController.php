<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth('platform')->user();
        $tenant = $user->tenant;

        return Inertia::render('Platform/Dashboard/Index', [
            'tenant' => $tenant,
            'user' => $user,
        ]);
    }
}
