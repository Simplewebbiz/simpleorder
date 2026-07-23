<?php

namespace App\Http\Controllers\Platform\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\MarketingPage;
use App\Support\Html;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Intervention\Image\Laravel\Facades\Image;

class MarketingPageController extends Controller
{
    public function index()
    {
        MarketingPage::seedDefaults();

        return Inertia::render('Platform/SuperAdmin/MarketingPages', [
            'pages' => MarketingPage::orderBy('sort')->get(),
        ]);
    }

    public function update(Request $request, MarketingPage $marketingPage)
    {
        $data = $request->validate([
            'title' => 'required|string|max:160',
            'slug' => 'required|string|max:120|alpha_dash',
            'nav_label' => 'nullable|string|max:80',
            'eyebrow' => 'nullable|string|max:120',
            'summary' => 'nullable|string|max:700',
            'content' => 'nullable|string',
            'hero_image_url' => 'nullable|url|max:1000',
            'cta_label' => 'nullable|string|max:80',
            'cta_url' => 'nullable|string|max:300',
            'is_published' => 'boolean',
            'sort' => 'integer|min:0',
        ]);

        $data['slug'] = MarketingPage::uniqueSlug($data['slug'], $marketingPage->id);
        $data['content'] = Html::clean($data['content'] ?? '');

        $marketingPage->update($data);

        return redirect()->route('platform.superadmin.marketing-pages.index')->with('success', 'Website page updated.');
    }

    public function uploadHeroImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $file = $request->file('image');
        $path = 'marketing/hero/' . Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());

        $image = Image::read($file)->scaleDown(width: 2000);
        Storage::disk('public')->put($path, (string) $image->encode());

        return response()->json(['url' => Storage::url($path)]);
    }
}