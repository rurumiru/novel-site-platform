<?php
namespace App\Http\Controllers;

use App\Models\TranslationTeam;
use App\Models\TeamInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller {

    public function index() {
        try {
            $teams = TranslationTeam::where('status', '!=', 'disbanded')
                ->withCount(['activeMembers', 'novels'])
                ->with('leader:id,name,avatar')
                ->orderByDesc('is_featured')
                ->orderByDesc('is_official')
                ->orderByDesc('total_earned')
                ->paginate(24);
        } catch (\Throwable $e) {
            $teams = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 24);
        }

        return view('teams.index', compact('teams'));
    }

    public function create() {
        return view('teams.create');
    }

    public function store(Request $request) {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'name'             => 'required|string|max:120',
            'mission'          => 'nullable|string|max:280',
            'description'      => 'nullable|string|max:4000',
            'status'           => 'required|in:active,recruiting,closed,on_hiatus',
            'application_mode' => 'required|in:invite_only,open,closed',
            'discord'          => 'nullable|url|max:255',
            'telegram'         => 'nullable|url|max:255',
            'vk'               => 'nullable|url|max:255',
            'website'          => 'nullable|url|max:255',
        ], [
            'name.required'    => 'Введите название команды.',
            'name.max'         => 'Название не более 120 символов.',
            'status.in'        => 'Укажите корректный статус.',
            'application_mode.in' => 'Укажите корректный режим вступления.',
        ]);

        try {
            $slug = \Illuminate\Support\Str::slug($data['name']);
            if (empty($slug)) $slug = 'team-' . time();
            $baseSlug = $slug;
            $i = 1;
            while (TranslationTeam::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $i++;
            }

            $team = TranslationTeam::create([
                'name'             => $data['name'],
                'slug'             => $slug,
                'leader_id'        => Auth::id(),
                'mission'          => $data['mission'] ?? null,
                'description'      => $data['description'] ?? null,
                'status'           => $data['status'],
                'application_mode' => $data['application_mode'],
                'discord'          => $data['discord'] ?? null,
                'telegram'         => $data['telegram'] ?? null,
                'vk'               => $data['vk'] ?? null,
                'website'          => $data['website'] ?? null,
                'is_official'      => false,
                'is_featured'      => false,
                'balance'          => 0,
                'total_earned'     => 0,
            ]);

            try {
                $team->members()->create([
                    'user_id'    => Auth::id(),
                    'role'       => 'leader',
                    'is_active'  => true,
                    'joined_at'  => now(),
                ]);
            } catch (\Throwable $e) {
            }

            return redirect()->route('teams.show', $team->slug)
                ->with('success', 'Команда «' . $team->name . '» создана! Теперь вы можете настроить её.');

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'Ошибка при создании команды: ' . $e->getMessage()]);
        }
    }

    public function show(string $slug) {
        $team = TranslationTeam::where('slug', $slug)
            ->withCount(['activeMembers', 'novels'])
            ->with([
                'leader:id,name,username,avatar',
                'activeMembers.user:id,name,username,avatar',
                'novels' => fn($q) => $q->select('novels.id', 'novels.title', 'novels.cover_image', 'novels.status')
                    ->with('genres:id,name'),
            ])
            ->firstOrFail();

        return view('teams.show', compact('team'));
    }

    public function acceptInvite(string $token) {
        $invite = TeamInvite::where('token', $token)
            ->where('status', 'pending')
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->firstOrFail();

        if ($invite->team->hasMember(Auth::id())) {
            return redirect()->route('teams.show', $invite->team->slug)
                ->with('error', 'Вы уже состоите в этой команде.');
        }

        \DB::transaction(function () use ($invite) {
            \App\Models\TranslationTeamMember::create([
                'team_id'        => $invite->team_id,
                'user_id'        => Auth::id(),
                'role'           => $invite->proposed_role,
                'revenue_share'  => $invite->proposed_share,
                'is_active'      => true,
                'joined_at'      => now(),
            ]);
            $invite->update([
                'status'       => 'accepted',
                'user_id'      => Auth::id(),
                'responded_at' => now(),
            ]);
        });

        return redirect()->route('teams.show', $invite->team->slug)
            ->with('success', 'Добро пожаловать в команду ' . $invite->team->name . '!');
    }
}
