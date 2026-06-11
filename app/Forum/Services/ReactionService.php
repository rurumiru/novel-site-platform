<?php

namespace App\Forum\Services;

use App\Forum\Models\Post;
use App\Forum\Models\Reaction;
use App\Forum\Models\Thread;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReactionService
{
    public function toggle(User $user, Model $target, string $emoji): array
    {
        if (!in_array($emoji, Reaction::ALLOWED, true)) {
            abort(422, 'Недопустимая реакция');
        }
        if (!($target instanceof Thread || $target instanceof Post)) {
            abort(422, 'Невалидная цель реакции');
        }

        return DB::transaction(function () use ($user, $target, $emoji) {
            $existing = Reaction::where('user_id', $user->id)
                ->where('reactable_type', get_class($target))
                ->where('reactable_id', $target->id)
                ->where('emoji', $emoji)
                ->first();

            $added = false;
            if ($existing) {
                $existing->delete();
            } else {
                Reaction::create([
                    'user_id'        => $user->id,
                    'reactable_type' => get_class($target),
                    'reactable_id'   => $target->id,
                    'emoji'          => $emoji,
                ]);
                $added = true;
            }

            $counts = $target->reactions()->select('emoji', DB::raw('count(*) as c'))
                ->groupBy('emoji')->pluck('c', 'emoji')->toArray();
            $total = array_sum($counts);
            $target->update(['reactions_count' => $total]);

            return [
                'added'     => $added,
                'total'     => $total,
                'breakdown' => $counts,
            ];
        });
    }
}
