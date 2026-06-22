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
        return Inertia::render('Platform/SuperAdmin/Plans', [
            'plans' => Plan::withCount('tenants')->orderBy('sort')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                    => 'required|string|max:100',
            'slug'                    => 'required|string|max:100|unique:plans,slug',
            'description'             => 'nullable|string',
            'price_monthly'           => 'required|integer|min:0',
            'price_yearly'            => 'required|integer|min:0',
            'stripe_monthly_price_id' => 'nullable|string',
            'stripe_yearly_price_id'  => 'nullable|string',
            'max_items'               => 'nullable|integer|min:0',
            'max_categories'          => 'nullable|integer|min:0',
            'custom_domain'           => 'boolean',
            'order_reports'           => 'boolean',
            'is_active'               => 'boolean',
            'sort'                    => 'integer',
        ]);
        Plan::create($data);
        return redirect()->route('platform.superadmin.plans.index')->with('success', 'Plan created.');
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name'                    => 'required|string|max:100',
            'slug'                    => 'required|string|max:100|unique:plans,slug,' . $plan->id,
            'description'             => 'nullable|string',
            'price_monthly'           => 'required|integer|min:0',
            'price_yearly'            => 'required|integer|min:0',
            'stripe_monthly_price_id' => 'nullable|string',
            'stripe_yearly_price_id'  => 'nullable|string',
            'max_items'               => 'nullable|integer|min:0',
            'max_categories'          => 'nullable|integer|min:0',
            'custom_domain'           => 'boolean',
            'order_reports'           => 'boolean',
            'is_active'               => 'boolean',
            'sort'                    => 'integer',
        ]);
        $plan->update($data);
        return redirect()->route('platform.superadmin.plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->route('platform.superadmin.plans.index')->with('success', 'Plan deleted.');
    }
}
