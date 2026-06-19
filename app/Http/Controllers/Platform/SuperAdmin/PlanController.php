<?php

namespace App\Http\Controllers\Platform\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index()
    {
        return Inertia::render('Platform/SuperAdmin/Settings', [
            'plans' => Plan::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'stripe_price_id' => 'nullable|string',
            'active' => 'boolean',
        ]);
        Plan::create($data);
        return redirect()->back()->with('success', 'Plan created.');
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'stripe_price_id' => 'nullable|string',
            'active' => 'boolean',
        ]);
        $plan->update($data);
        return redirect()->back()->with('success', 'Plan updated.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->back()->with('success', 'Plan deleted.');
    }
}
