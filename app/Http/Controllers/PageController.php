<?php
namespace App\Http\Controllers;
use App\Models\Page;

class PageController extends Controller {
    public function show($slug) {
        $page = Page::where('slug', $slug)->where('is_published', true)->firstOrFail();
        return view('page.show', compact('page'));
    }
}
