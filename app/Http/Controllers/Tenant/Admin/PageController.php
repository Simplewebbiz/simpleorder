<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends Controller
{
    public function index()
    {
        Page::seedDefaults();

        return Inertia::render('Admin/Pages/Index', [
            'pages' => Page::with('hero')->orderBy('sort')->get(),
        ]);
    }

    public function edit(Page $page)
    {
        return Inertia::render('Admin/Pages/Edit', [
            'pageModel' => $page->load('hero'),
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'slug' => 'nullable|string|max:120|alpha_dash',
            'menu_label' => 'nullable|string|max:60',
            'summary' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'hero_media_id' => 'nullable|exists:media,id',
            'is_published' => 'boolean',
            'sort' => 'integer|min:0',
        ]);

        $data['slug'] = $data['slug']
            ? Page::uniqueSlug($data['slug'], $page->id)
            : Page::uniqueSlug($data['title'], $page->id);
        $data['content'] = $this->cleanHtml($data['content'] ?? '');

        $page->update($data);

        return redirect()->route('tenant.admin.pages.index')->with('success', 'Page updated.');
    }

    private function cleanHtml(string $html): string
    {
        $html = strip_tags($html, '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><a><img><blockquote>');
        $html = preg_replace('/\s+on[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? '';

        return $html;
    }
}