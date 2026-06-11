<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Setting;

class ModeratorUnlockController extends Controller {
    public function show() {
        if (!auth()->user() || !auth()->user()->hasRole('moderator')) {
            abort(403);
        }
        if (auth()->user()->hasRole('super_admin')) {
            return redirect()->route('moderator.panel');
        }
        return view('moderator.password');
    }

    public function unlock(Request $request) {
        $request->validate(['password' => 'required']);
        $stored = Setting::retrieve('moderation_password', '');
        if (empty($stored)) {
            return back()->with('error', 'Пароль модерации не настроен. Обратитесь к администратору.');
        }
        if (!hash_equals($stored, $request->password)) {
            return back()->with('error', 'Неверный пароль');
        }
        session(['moderator_unlocked' => true]);
        return redirect()->route('moderator.panel');
    }

    public function logout(Request $request) {
        $request->session()->forget('moderator_unlocked');
        return redirect()->route('home');
    }
}
