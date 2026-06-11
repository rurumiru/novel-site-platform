<?php

namespace App\Services;

use App\Models\User;
use App\Models\Donation;
use Illuminate\Support\Facades\DB;

class TrustLevelService
{
    public static function recalculateFor(User $user): bool
    {
        $changed = false;

        $newTrust = self::computeTrustLevel($user);
        if ($newTrust > ($user->trust_level ?? 0)) {
            $user->trust_level = $newTrust;
            $changed = true;
        }

        $newPatron = self::computePatronTier($user);
        if ($newPatron !== ($user->patron_tier ?? 0)) {
            $user->patron_tier = $newPatron;
            $changed = true;
        }

        if ($changed) {
            $user->save();
            self::syncPatronRole($user);
        }

        return $changed;
    }

    public static function recalculateAll(int $batchSize = 200): int
    {
        $count = 0;
        User::query()->orderBy('id')->chunk($batchSize, function ($users) use (&$count) {
            foreach ($users as $u) {
                if (self::recalculateFor($u)) $count++;
            }
        });
        return $count;
    }

    private static function computeTrustLevel(User $user): int
    {
        $current = (int) ($user->trust_level ?? 0);
        if ($current >= 4) return 4;

        $accountDays = $user->created_at ? $user->created_at->diffInDays(now()) : 0;

        $chaptersRead = DB::table('reading_progress')
            ->where('user_id', $user->id)
            ->where('percent', '>=', 80)
            ->count();

        $commentsCount = DB::table('comments')
            ->where('user_id', $user->id)->count();

        $likesReceived = DB::table('comment_likes')
            ->whereIn('comment_id', function ($q) use ($user) {
                $q->select('id')->from('comments')->where('user_id', $user->id);
            })->count();

        if ($accountDays >= 30 && $chaptersRead >= 100 && $commentsCount >= 50 && $likesReceived >= 10) {
            return 3;
        }
        if ($accountDays >= 7 && $chaptersRead >= 30 && $commentsCount >= 10) {
            return 2;
        }
        if ($accountDays >= 1 && $chaptersRead >= 5) {
            return 1;
        }
        return 0;
    }

    private static function computePatronTier(User $user): int
    {
        $totalGiven = (int) Donation::where('user_id', $user->id)
            ->where('status', 'completed')
            ->sum('amount');

        if ($totalGiven >= 25000) return 3;
        if ($totalGiven >= 5000)  return 2;
        if ($totalGiven >= 500)   return 1;
        return 0;
    }

    private static function syncPatronRole(User $user): void
    {
        if ($user->patron_tier >= 1 && !$user->hasRole('donor')) {
            try { $user->assignRole('donor'); } catch (\Throwable $e) {}
        }
    }
}
