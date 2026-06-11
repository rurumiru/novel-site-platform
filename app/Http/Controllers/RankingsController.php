<?php
namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Setting;
use App\Services\GeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RankingsController extends Controller
{
    public function index(Request $request)
    {
        $sort   = $request->get('sort', 'rating');
        $period = $request->get('period', 'all');

        if (!in_array($sort, ['rating', 'subs', 'views'], true)) $sort = 'rating';
        if (!in_array($period, ['all', 'month', 'week'], true)) $period = 'all';

        $isRu      = GeoService::isRu($request->ip());
        $isGuest   = !Auth::check();
        $hideAdult = $isGuest && filter_var(Setting::retrieve('hide_adult_for_guests', '1'), FILTER_VALIDATE_BOOLEAN);

        $makeQuery = function () use ($isRu, $hideAdult, $isGuest, $sort, $period) {
            $q = Novel::where('is_published', true)
                ->when($isRu,     fn($x) => $x->where('is_restricted', false))
                ->when($hideAdult,fn($x) => $x->where('is_adult', false))
                ->when($isGuest,  fn($x) => $x->where('hide_from_guests', false))
                ->withCount('chapters')
                ->withCount(['ratings as avg_rating' => fn($x) => $x->select(DB::raw('coalesce(avg(score),0)'))])
                ->withCount(['subscriptions as subs_count' => fn($x) => $x->where('status', 'active')]);

            if ($sort === 'views') {
                if ($period === 'week') {
                    $q->withCount(['viewsLog as views_period' => fn($x) => $x->where('viewed_at', '>=', now()->subDays(7)->toDateString())]);
                } elseif ($period === 'month') {
                    $q->withCount(['viewsLog as views_period' => fn($x) => $x->where('viewed_at', '>=', now()->subDays(30)->toDateString())]);
                } else {
                    $q->selectRaw('novels.*, novels.views as views_period');
                }
            }

            switch ($sort) {
                case 'subs':  $q->orderByDesc('subs_count')->orderByDesc('avg_rating'); break;
                case 'views': $q->orderByDesc('views_period')->orderByDesc('views');    break;
                case 'rating':
                default:      $q->orderByDesc('avg_rating')->orderByDesc('subs_count'); break;
            }

            return $q->with('genres');
        };

        $novels = $makeQuery()->paginate(30)->withQueryString();
        $podium = $makeQuery()->take(3)->get();

        return view('rankings.index', compact('novels', 'podium', 'sort', 'period'));
    }
}
