<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller {
    public function index() {
        $users = User::withCount(['novels' => fn($q) => $q->where('is_published', true)])
            ->with(['novels' => fn($q) => $q->where('is_published', true)->withCount('chapters')->latest()->take(4)])
            ->orderByDesc('novels_count')
            ->latest()
            ->paginate(24);
        
        return view('users.index', compact('users'));
    }

    public function show($id) {
        $user = User::with(['novels' => function($q) {
            $q->where('is_published', true)->withCount('chapters')->latest();
        }])->findOrFail($id);
        
        return view('users.show', compact('user'));
    }
}
