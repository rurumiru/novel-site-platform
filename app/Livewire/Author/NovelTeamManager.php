<?php
namespace App\Livewire\Author;

use App\Models\Novel;
use App\Models\NovelTeamMemberShare;
use App\Models\TranslationTeam;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NovelTeamManager extends Component {
    public Novel $novel;

    public ?int $selectedTeamId = null;
    public float $newTeamShare  = 70;
    public bool $isPrimary      = false;
    public bool $showCredits    = true;

    public ?int $editingTeamId  = null;
    public float $editShare     = 70;
    public bool $editIsPrimary  = false;
    public bool $editShowCredits = true;

    public array $memberShares  = [];

    protected function authorizeOwner(): void {
        $userId = Auth::id();
        if ($this->novel->user_id === $userId) return;
        if (Auth::user()?->hasRole(['super_admin', 'owner', 'deputy_admin'])) return;
        abort(403);
    }

    public function mount(Novel $novel): void {
        $this->novel = $novel;
        $this->loadMemberShares();
    }

    private function loadMemberShares(): void {
        try {
            NovelTeamMemberShare::where('novel_id', $this->novel->id)
                ->get()
                ->each(function ($s) {
                    $this->memberShares[$s->team_id][$s->user_id] = (float)$s->revenue_share;
                });
        } catch (\Throwable) {}
    }

    public function assignTeam(): void {
        $this->authorizeOwner();
        $this->validate([
            'selectedTeamId' => 'required|integer|exists:translation_teams,id',
            'newTeamShare'   => 'required|numeric|min:0|max:100',
        ]);

        if ($this->isPrimary) {
            $this->novel->teams()
                ->wherePivot('is_primary', true)
                ->each(fn($t) => $this->novel->teams()->updateExistingPivot($t->id, ['is_primary' => false]));
        }

        if ($this->novel->teams()->where('translation_teams.id', $this->selectedTeamId)->exists()) {
            $this->novel->teams()->updateExistingPivot($this->selectedTeamId, [
                'team_revenue_share' => $this->newTeamShare,
                'is_primary'         => $this->isPrimary,
                'show_credits'       => $this->showCredits,
            ]);
        } else {
            $this->novel->teams()->attach($this->selectedTeamId, [
                'team_revenue_share' => $this->newTeamShare,
                'is_primary'         => $this->isPrimary,
                'show_credits'       => $this->showCredits,
                'assigned_at'        => now(),
            ]);
        }

        $this->reset(['selectedTeamId', 'newTeamShare', 'isPrimary', 'showCredits']);
        $this->newTeamShare = 70;
        $this->showCredits  = true;
        $this->novel->refresh();
        $this->novel->load('teams.activeMembers.user');
    }

    public function startEdit(int $teamId): void {
        $this->authorizeOwner();
        $team = $this->novel->teams()->where('translation_teams.id', $teamId)->first();
        if (!$team) return;
        $this->editingTeamId   = $teamId;
        $this->editShare       = (float)($team->pivot->team_revenue_share ?? 70);
        $this->editIsPrimary   = (bool)($team->pivot->is_primary ?? false);
        $this->editShowCredits = (bool)($team->pivot->show_credits ?? true);
    }

    public function saveEdit(): void {
        $this->authorizeOwner();
        if (!$this->editingTeamId) return;
        $this->validate(['editShare' => 'required|numeric|min:0|max:100']);

        if ($this->editIsPrimary) {
            $this->novel->teams()
                ->wherePivot('is_primary', true)
                ->where('translation_teams.id', '!=', $this->editingTeamId)
                ->each(fn($t) => $this->novel->teams()->updateExistingPivot($t->id, ['is_primary' => false]));
        }

        $this->novel->teams()->updateExistingPivot($this->editingTeamId, [
            'team_revenue_share' => $this->editShare,
            'is_primary'         => $this->editIsPrimary,
            'show_credits'       => $this->editShowCredits,
        ]);

        $this->editingTeamId = null;
        $this->novel->refresh();
        $this->novel->load('teams.activeMembers.user');
    }

    public function cancelEdit(): void {
        $this->editingTeamId = null;
    }

    public function removeTeam(int $teamId): void {
        $this->authorizeOwner();
        $this->novel->teams()->detach($teamId);
        try {
            NovelTeamMemberShare::where('novel_id', $this->novel->id)
                ->where('team_id', $teamId)->delete();
            unset($this->memberShares[$teamId]);
        } catch (\Throwable) {}
        $this->novel->refresh();
        $this->novel->load('teams.activeMembers.user');
    }

    public function saveMemberShare(int $teamId, int $userId, string $share): void {
        $this->authorizeOwner();
        $shareFloat = (float)$share;
        try {
            NovelTeamMemberShare::updateOrCreate(
                ['novel_id' => $this->novel->id, 'team_id' => $teamId, 'user_id' => $userId],
                ['revenue_share' => $shareFloat]
            );
            $this->memberShares[$teamId][$userId] = $shareFloat;
        } catch (\Throwable) {}
    }

    public function getMemberShare(int $teamId, int $userId): float {
        return $this->memberShares[$teamId][$userId] ?? 0.0;
    }

    public function render() {
        $this->novel->loadMissing('teams');
        $this->novel->load('teams.activeMembers.user');

        $assignedTeamIds = $this->novel->teams->pluck('id')->toArray();
        $availableTeams  = TranslationTeam::whereNotIn('id', $assignedTeamIds)
            ->whereIn('status', ['active', 'recruiting'])
            ->orderBy('name')->get();

        return view('livewire.author.novel-team-manager', [
            'teams'          => $this->novel->teams,
            'availableTeams' => $availableTeams,
        ]);
    }
}
