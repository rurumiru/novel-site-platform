<?php
namespace App\Livewire\Teams;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\TranslationTeam;
use App\Models\TranslationTeamMember;
use App\Models\TeamKickLog;
use App\Models\TeamInvite;
use App\Models\Novel;
use App\Models\NovelTeamMemberShare;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeamManager extends Component {
    public TranslationTeam $team;

    public ?int $editMemberId = null;
    public string $editRole = 'member';
    public string $editTitle = '';
    public float $editShare = 0;
    public bool $editCanPublish = false;
    public bool $editCanInvite = false;
    public bool $editCanAssignNovels = false;
    public bool $editCanEditAny = false;
    public bool $editCanManagePayouts = false;
    public string $editNote = '';
    public bool $showMemberForm = false;

    public ?int $kickMemberId = null;
    public string $kickReason = '';
    public bool $kickShowInProfile = false;
    public bool $showKickForm = false;

    public ?int $novelShareMemberId = null;
    public ?int $novelShareNovelId = null;
    public float $novelShareValue = 0;
    public string $novelShareRole = '';
    public bool $showNovelShareForm = false;

    public string $teamName = '';
    public string $teamDescription = '';
    public string $teamMission = '';
    public string $teamStatus = 'active';
    public string $teamApplicationMode = 'invite_only';
    public float $teamLeaderShare = 0;
    public bool $showTeamSettingsForm = false;

    public string $inviteEmail = '';
    public string $inviteRole = 'member';
    public float $inviteShare = 0;
    public bool $showInviteForm = false;
    public ?string $inviteLink = null;

    public string $activeTab = 'members';

    public $kickLogs;
    public $teamNovels;
    public $novelShares;
    public $pendingApplications;

    public function mount(string $slug): void {
        $this->team = TranslationTeam::where('slug', $slug)->firstOrFail();
        $this->authorizeManage();
        $this->loadData();
        $this->teamName = $team->name;
        $this->teamDescription = $team->description ?? '';
        $this->teamMission = $team->mission ?? '';
        $this->teamStatus = $team->status;
        $this->teamApplicationMode = $team->application_mode ?? 'invite_only';
        $this->teamLeaderShare = (float) ($team->leader_share ?? 0);
    }

    private function authorizeManage(): void {
        $userId = Auth::id();
        if ($this->team->leader_id === $userId) return;

        try {
            $m = $this->team->members()->where('user_id', $userId)->where('is_active', true)->first();
            if ($m && in_array($m->role, ['leader', 'coordinator'])) return;
        } catch (\Throwable $e) {
        }

        if (Auth::user()?->hasRole(['super_admin', 'owner', 'deputy_admin'])) return;

        abort(403, 'У вас нет прав управлять этой командой.');
    }

    private function isLeader(): bool {
        return $this->team->isLeader(Auth::id());
    }

    private function loadData(): void {
        $this->team->refresh();
        $this->kickLogs = TeamKickLog::where('team_id', $this->team->id)
            ->with(['user:id,name,username', 'kickedBy:id,name'])
            ->orderByDesc('logged_at')
            ->take(50)
            ->get();
        $this->teamNovels = $this->team->novels()->with('volumes')->get();
        $this->novelShares = NovelTeamMemberShare::where('team_id', $this->team->id)
            ->with(['user:id,name', 'novel:id,title'])
            ->get();
        $this->pendingApplications = $this->team->applications()
            ->where('status', 'pending')
            ->with('user:id,name,username')
            ->get();
    }

    public function openMemberEdit(int $memberId): void {
        $member = TranslationTeamMember::where('team_id', $this->team->id)->findOrFail($memberId);
        $this->editMemberId = $memberId;
        $this->editRole = $member->role;
        $this->editTitle = $member->title ?? '';
        $this->editShare = (float) $member->revenue_share;
        $this->editCanPublish = (bool) $member->can_publish;
        $this->editCanInvite = (bool) $member->can_invite_members;
        $this->editCanAssignNovels = (bool) $member->can_assign_novels;
        $this->editCanEditAny = (bool) $member->can_edit_any_chapter;
        $this->editCanManagePayouts = (bool) $member->can_manage_payouts;
        $this->editNote = $member->note ?? '';
        $this->showMemberForm = true;
    }

    public function saveMemberEdit(): void {
        $this->authorizeManage();
        $member = TranslationTeamMember::where('team_id', $this->team->id)
            ->where('id', $this->editMemberId)
            ->firstOrFail();

        if (!$this->isLeader() && in_array($this->editRole, ['leader', 'coordinator'])) {
            $this->addError('editRole', 'Только лидер может назначить роль Лидер/Координатор.');
            return;
        }

        $prevRole  = $member->role;
        $prevShare = (float) $member->revenue_share;

        $member->update([
            'role'               => $this->editRole,
            'title'              => $this->editTitle ?: null,
            'revenue_share'      => max(0, min(100, $this->editShare)),
            'can_publish'        => $this->editCanPublish,
            'can_invite_members' => $this->editCanInvite,
            'can_assign_novels'  => $this->editCanAssignNovels,
            'can_edit_any_chapter' => $this->editCanEditAny,
            'can_manage_payouts' => $this->editCanManagePayouts,
            'note'               => $this->editNote ?: null,
        ]);

        if ($prevRole !== $this->editRole) {
            TeamKickLog::create([
                'team_id'   => $this->team->id,
                'user_id'   => $member->user_id,
                'kicked_by' => Auth::id(),
                'action'    => 'role_change',
                'prev_role' => $prevRole,
                'new_role'  => $this->editRole,
            ]);
        }
        if ($prevShare != $this->editShare) {
            TeamKickLog::create([
                'team_id'    => $this->team->id,
                'user_id'    => $member->user_id,
                'kicked_by'  => Auth::id(),
                'action'     => 'share_change',
                'prev_share' => $prevShare,
                'new_share'  => $this->editShare,
            ]);
        }

        $this->showMemberForm = false;
        $this->loadData();
        session()->flash('success', 'Данные участника обновлены.');
    }

    public function openKick(int $memberId): void {
        $member = TranslationTeamMember::where('team_id', $this->team->id)->findOrFail($memberId);
        if ($member->user_id === $this->team->leader_id) {
            session()->flash('error', 'Нельзя исключить лидера команды.');
            return;
        }
        $this->kickMemberId = $memberId;
        $this->kickReason = '';
        $this->kickShowInProfile = false;
        $this->showKickForm = true;
    }

    public function confirmKick(): void {
        $this->authorizeManage();
        $member = TranslationTeamMember::where('team_id', $this->team->id)
            ->where('id', $this->kickMemberId)
            ->firstOrFail();

        if ($member->user_id === $this->team->leader_id) {
            session()->flash('error', 'Нельзя исключить лидера.');
            return;
        }
        if (trim($this->kickReason) === '') {
            $this->addError('kickReason', 'Укажите причину исключения.');
            return;
        }

        DB::transaction(function () use ($member) {
            $member->update([
                'is_active' => false,
                'left_at'   => now(),
            ]);

            TeamKickLog::create([
                'team_id'          => $this->team->id,
                'user_id'          => $member->user_id,
                'kicked_by'        => Auth::id(),
                'action'           => 'kick',
                'reason'           => trim($this->kickReason),
                'show_in_profile'  => $this->kickShowInProfile,
                'prev_role'        => $member->role,
            ]);
        });

        $this->showKickForm = false;
        $this->kickMemberId = null;
        $this->loadData();
        session()->flash('success', 'Участник исключён из команды.');
    }

    public function leaveTeam(): void {
        $member = TranslationTeamMember::where('team_id', $this->team->id)
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();
        if (!$member) return;
        if ($this->team->isLeader(Auth::id())) {
            session()->flash('error', 'Лидер не может покинуть команду. Сначала передайте лидерство.');
            return;
        }

        DB::transaction(function () use ($member) {
            $member->update(['is_active' => false, 'left_at' => now()]);
            TeamKickLog::create([
                'team_id'   => $this->team->id,
                'user_id'   => Auth::id(),
                'kicked_by' => Auth::id(),
                'action'    => 'leave',
                'prev_role' => $member->role,
            ]);
        });
        session()->flash('success', 'Вы покинули команду.');
        $this->redirect(route('teams.index'));
    }

    public function openNovelShare(int $memberId, ?int $novelId = null): void {
        $this->novelShareMemberId = $memberId;
        $this->novelShareNovelId  = $novelId;
        $this->novelShareRole     = '';
        $this->novelShareValue    = 0;

        if ($novelId) {
            $existing = NovelTeamMemberShare::where([
                'team_id'  => $this->team->id,
                'user_id'  => TranslationTeamMember::find($memberId)?->user_id,
                'novel_id' => $novelId,
            ])->first();
            if ($existing) {
                $this->novelShareValue = (float) $existing->revenue_share;
                $this->novelShareRole  = $existing->role_override ?? '';
            }
        }
        $this->showNovelShareForm = true;
    }

    public function saveNovelShare(): void {
        $this->authorizeManage();
        if (!$this->novelShareNovelId || !$this->novelShareMemberId) return;

        $member = TranslationTeamMember::where('team_id', $this->team->id)
            ->find($this->novelShareMemberId);
        if (!$member) return;

        NovelTeamMemberShare::updateOrCreate(
            [
                'novel_id' => $this->novelShareNovelId,
                'team_id'  => $this->team->id,
                'user_id'  => $member->user_id,
            ],
            [
                'revenue_share' => max(0, min(100, $this->novelShareValue)),
                'role_override' => $this->novelShareRole ?: null,
            ]
        );

        $this->showNovelShareForm = false;
        $this->loadData();
        session()->flash('success', 'Доля на новелле обновлена.');
    }

    public function deleteNovelShare(int $shareId): void {
        $this->authorizeManage();
        NovelTeamMemberShare::where('team_id', $this->team->id)->findOrFail($shareId)->delete();
        $this->loadData();
        session()->flash('success', 'Персональная доля удалена — используется общая.');
    }

    public function generateInviteLink(): void {
        $this->authorizeManage();
        $invite = TeamInvite::create([
            'team_id'       => $this->team->id,
            'invited_by'    => Auth::id(),
            'email'         => trim($this->inviteEmail) ?: null,
            'token'         => Str::random(32),
            'proposed_role' => $this->inviteRole,
            'proposed_share'=> max(0, min(100, $this->inviteShare)),
            'expires_at'    => now()->addDays(7),
        ]);
        $this->inviteLink = route('teams.invite.accept', $invite->token);
        session()->flash('success', 'Ссылка для приглашения создана.');
    }

    public function transferLeadership(int $toMemberId): void {
        if (!$this->isLeader()) abort(403);
        $member = TranslationTeamMember::where('team_id', $this->team->id)
            ->where('id', $toMemberId)
            ->where('is_active', true)
            ->firstOrFail();

        DB::transaction(function () use ($member) {
            $oldLeaderMember = $this->team->members()->where('user_id', $this->team->leader_id)->first();
            $oldLeaderMember?->update(['role' => 'coordinator']);

            $member->update(['role' => 'leader']);
            $this->team->update(['leader_id' => $member->user_id]);

            TeamKickLog::create([
                'team_id'   => $this->team->id,
                'user_id'   => $member->user_id,
                'kicked_by' => Auth::id(),
                'action'    => 'role_change',
                'prev_role' => $member->role,
                'new_role'  => 'leader',
                'reason'    => 'Передача лидерства от ' . Auth::user()->name,
            ]);
        });

        session()->flash('success', 'Лидерство передано.');
        $this->loadData();
    }

    public function saveTeamSettings(): void {
        if (!$this->isLeader()) abort(403);

        $this->validate([
            'teamName'        => 'required|string|max:120',
            'teamDescription' => 'nullable|string|max:2000',
            'teamMission'     => 'nullable|string|max:280',
            'teamLeaderShare' => 'numeric|min:0|max:100',
        ]);

        $this->team->update([
            'name'             => $this->teamName,
            'slug'             => \Illuminate\Support\Str::slug($this->teamName),
            'description'      => $this->teamDescription ?: null,
            'mission'          => $this->teamMission ?: null,
            'status'           => $this->teamStatus,
            'application_mode' => $this->teamApplicationMode,
            'leader_share'     => max(0, min(100, $this->teamLeaderShare)),
        ]);

        $this->showTeamSettingsForm = false;
        $this->loadData();
        session()->flash('success', 'Настройки команды сохранены.');
    }

    public function acceptApplication(int $appId): void {
        $this->authorizeManage();
        $app = $this->team->applications()->findOrFail($appId);

        DB::transaction(function () use ($app) {
            TranslationTeamMember::create([
                'team_id'    => $this->team->id,
                'user_id'    => $app->user_id,
                'role'       => $app->desired_role,
                'is_active'  => true,
                'joined_at'  => now(),
            ]);
            $app->update([
                'status'      => 'accepted',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        });
        $this->loadData();
        session()->flash('success', 'Заявка принята, участник добавлен.');
    }

    public function rejectApplication(int $appId, string $reason = ''): void {
        $this->authorizeManage();
        $this->team->applications()->findOrFail($appId)->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason ?: null,
            'reviewed_by'      => Auth::id(),
            'reviewed_at'      => now(),
        ]);
        $this->loadData();
        session()->flash('success', 'Заявка отклонена.');
    }

    public function render() {
        return view('livewire.teams.team-manager', [
            'members'     => $this->team->members()->with('user:id,name,username,avatar')->get(),
            'totalShare'  => $this->team->total_share,
            'leaderShare' => $this->team->leader_effective_share,
            'novels'      => $this->teamNovels,
        ]);
    }
}
