<?php
namespace App\Http\Controllers;
use App\Models\Chapter;
use App\Models\Setting;
use Illuminate\Http\Request;

class LatestUpdatesController extends Controller {
    public function index() {
        $perPage = Setting::retrieve('updates_per_page', 30);
        
        $chapters = Chapter::published()
            ->whereHas('novel', fn($q) => $q->where('is_published', true))
            ->with('novel')
            ->latest('published_at')
            ->paginate($perPage);
            
        return view('updates.index', compact('chapters'));
    }
}
