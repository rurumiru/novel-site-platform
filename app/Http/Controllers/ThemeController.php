<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class ThemeController extends Controller {
    public function toggle(Request $request) {
        $new = $request->input('theme');
        if (!in_array($new, ['light', 'dark', 'sepia'], true)) {
            $current = $request->cookie('theme', 'light');
            $new = $current === 'dark' ? 'light' : 'dark';
        }
        
        Cookie::queue('theme', $new, 5256000);
        
        return response()->json(['theme' => $new]);
    }
}
