<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\{MarketingPage, Plan};
use Inertia\Inertia;

class MarketingController extends Controller
{
    public function home()
    {
        return Inertia::render('Platform/Marketing/Home', [
            'page' => $this->page('home'),
            'navPages' => $this->navPages(),
            'plans' => $this->activePlans(),
        ]);
    }

    public function about()
    {
        return Inertia::render('Platform/Marketing/About', [
            'page' => $this->page('about'),
            'navPages' => $this->navPages(),
        ]);
    }

    public function plans()
    {
        return Inertia::render('Platform/Marketing/Plans', [
            'page' => $this->page('plans'),
            'navPages' => $this->navPages(),
            'plans' => $this->activePlans(),
        ]);
    }

    public function contact()
    {
        return Inertia::render('Platform/Marketing/Contact', [
            'page' => $this->page('contact'),
            'navPages' => $this->navPages(),
        ]);
    }

    private function page(string $slug): MarketingPage
    {
        MarketingPage::seedDefaults();

        return MarketingPage::where('slug', $slug)->firstOrFail();
    }

    private function navPages()
    {
        MarketingPage::seedDefaults();

        return MarketingPage::where('is_published', true)->orderBy('sort')->get([
            'title', 'slug', 'nav_label', 'sort',
        ]);
    }

    private function activePlans()
    {
        return Plan::where('is_active', true)->orderBy('sort')->get();
    }
}