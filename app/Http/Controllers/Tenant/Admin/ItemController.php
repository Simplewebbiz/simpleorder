<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Category;
use App\Models\Tenant\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Items/Index', [
            'items' => Item::with(['categories', 'image', 'options.values'])->orderBy('sort')->get(),
            'categories' => Category::orderBy('sort')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Items/Edit', [
            'item' => null,
            'categories' => Category::orderBy('sort')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $item = Item::create($this->itemData($data));
            $this->syncCategories($item, $data['category_ids'] ?? []);
            $this->syncOptions($item, $data['options'] ?? []);
        });

        return redirect()->route('tenant.admin.items.index')->with('success', 'Item created.');
    }

    public function edit(Item $item)
    {
        return Inertia::render('Admin/Items/Edit', [
            'item' => $item->load(['categories', 'image', 'options.values']),
            'categories' => Category::orderBy('sort')->get(),
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($item, $data) {
            $item->update($this->itemData($data));
            $this->syncCategories($item, $data['category_ids'] ?? []);
            $this->syncOptions($item, $data['options'] ?? []);
        });

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
            'options.*.label' => 'required|string|max:120',
            'options.*.required' => 'boolean',
            'options.*.input_type' => 'required|in:select,multiselect',
            'options.*.sort' => 'integer|min:0',
            'options.*.values' => 'array',
            'options.*.values.*.label' => 'required|string|max:120',
            'options.*.values.*.price' => 'numeric|min:0',
            'options.*.values.*.price_type' => 'required|in:flat,percent',
            'options.*.values.*.sort' => 'integer|min:0',
        ]);

        $this->syncOptions($item, $data['options'] ?? []);

        return redirect()->back()->with('success', 'Options saved.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:200',
            'sku' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:food,product',
            'taxable' => 'boolean',
            'image_id' => 'nullable|exists:media,id',
            'is_active' => 'boolean',
            'sort' => 'integer|min:0',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:categories,id',
            'options' => 'array',
            'options.*.label' => 'nullable|string|max:120',
            'options.*.required' => 'boolean',
            'options.*.input_type' => 'required_with:options.*.label|in:select,multiselect',
            'options.*.sort' => 'integer|min:0',
            'options.*.values' => 'array',
            'options.*.values.*.label' => 'nullable|string|max:120',
            'options.*.values.*.price' => 'numeric|min:0',
            'options.*.values.*.price_type' => 'required_with:options.*.values.*.label|in:flat,percent',
            'options.*.values.*.sort' => 'integer|min:0',
        ]);
    }

    private function itemData(array $data): array
    {
        return [
            'name' => $data['name'],
            'sku' => $data['sku'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'type' => $data['type'] ?? 'food',
            'taxable' => $data['taxable'] ?? true,
            'image_id' => $data['image_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'sort' => $data['sort'] ?? 0,
        ];
    }

    private function syncCategories(Item $item, array $categoryIds): void
    {
        $sync = [];

        foreach (array_values($categoryIds) as $index => $categoryId) {
            $sync[$categoryId] = ['sort' => $index];
        }

        $item->categories()->sync($sync);
    }

    private function syncOptions(Item $item, array $options): void
    {
        $item->options()->delete();

        foreach ($options as $index => $optionData) {
            if (empty($optionData['label'])) {
                continue;
            }

            $option = $item->options()->create([
                'label' => $optionData['label'],
                'required' => $optionData['required'] ?? false,
                'input_type' => $optionData['input_type'] ?? 'select',
                'sort' => $optionData['sort'] ?? $index,
            ]);

            foreach ($optionData['values'] ?? [] as $valueIndex => $valueData) {
                if (empty($valueData['label'])) {
                    continue;
                }

                $option->values()->create([
                    'label' => $valueData['label'],
                    'price' => $valueData['price'] ?? 0,
                    'price_type' => $valueData['price_type'] ?? 'flat',
                    'sort' => $valueData['sort'] ?? $valueIndex,
                ]);
            }
        }
    }
}