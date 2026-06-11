<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ReadingProgress;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller {
    public function save(Request $request) {
        if (!Auth::check()) return response()->json(['status' => 'guest']);

        $request->validate([
            'novel_id' => 'required|integer',
            'chapter_id' => 'required|integer',
            'percent' => 'required|numeric|min:0|max:100',
        ]);

        ReadingProgress::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'novel_id' => $request->novel_id,
                'chapter_id' => $request->chapter_id
            ],
            [
                'percent' => $request->percent,
                'is_completed' => $request->percent > 90,
                'updated_at' => now()
            ]
        );

        return response()->json(['status' => 'saved']);
    }
}
