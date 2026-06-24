<?php

namespace App\Http\Controllers\Platform\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\MarketingPage;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        $data['content'] = $this->cleanHtml($data['content'] ?? '');

        $marketingPage->update($data);

        return redirect()->route('platform.superadmin.marketing-pages.index')->with('success', 'Website page updated.');
    }

    private function cleanHtml(string $html): string
    {
        $html = strip_tags($html, '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><a><img><blockquote>');
        $html = preg_replace('/\s+on[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? '';

        return $html;
    }
}