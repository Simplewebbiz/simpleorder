<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Item;
use App\Models\Tenant\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Items/Index', [
            'items' => Item::with('category')->orderBy('sort_order')->get(),
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Items/Edit', [
            'item' => null,
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'active' => 'boolean',
            'sort_order' => 'integer',
            'image_url' => 'nullable|string',
        ]);
        Item::create($data);
        return redirect()->route('tenant.admin.items.index')->with('success', 'Item created.');
    }

    public function edit(Item $item)
    {
        return Inertia::render('Admin/Items/Edit', [
            'item' => $item->load('options'),
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'active' => 'boolean',
            'sort_order' => 'integer',
            'image_url' => 'nullable|string',
        ]);
        $item->update($data);
        return redirect()->route('tenant.admin.items.index')->with('success', 'Item updated.');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('tenant.admin.items.index')->with('success', 'Item deleted.');
    }

    public function saveOptions(Request $request, Item $item)
    {
        $data = $request->validate([
            'options' => 'array',
            'options.*.name' => 'required|string',
            'options.*.choices' => 'array',
        ]);
        $item->options()->delete();
        foreach ($data['options'] ?? [] as $option) {
            $item->options()->create($option);
        }
        return redirect()->back()->with('success', 'Options saved.');
    }
}
