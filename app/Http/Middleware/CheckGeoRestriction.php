<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Novel;
use App\Models\Chapter;
use App\Models\Setting;
use App\Services\GeoService;
use Illuminate\Support\Facades\Auth;

class CheckGeoRestriction {
    public function handle(Request $request, Closure $next): Response {
        $novelId = $request->route('id') ?? $request->route('novel_id');
        $chapterId = $request->route('chapter_id');

        $isAdultContent = false;
        $novel = null;

        if ($novelId) {
            $novel = Novel::find($novelId);
            if ($novel) {
                if ($novel->is_adult) $isAdultContent = true;

                if ($novel->is_restricted && GeoService::isRu($request->ip())) {
                    if (!Auth::check() || !Auth::user()->hasRole('super_admin')) {
                        return response()->view('errors.restricted', [], 403);
                    }
                }
            }
        }

        if ($chapterId) {
            $chapter = Chapter::find($chapterId);
            if ($chapter && $chapter->is_adult) $isAdultContent = true;
        }

        if ($isAdultContent) {
            if (Auth::check() && (Auth::user()->hasRole('super_admin') || ($novel && Auth::id() === $novel->user_id))) {
                return $next($request);
            }

            if (!Auth::check()) {
                return response()->view('errors.adult', ['guest_only' => true]);
            }

            $user = Auth::user();

            if (!$user->hasVerifiedEmail()) {
                return response()->view('errors.adult', ['need_verify' => true]);
            }

            if (!filter_var(Setting::retrieve('require_age_confirmation', '1'), FILTER_VALIDATE_BOOLEAN)) {
                return $next($request);
            }

            if (!$user->hasAgeInfo()) {
                return response()->view('errors.adult', ['need_profile' => true]);
            }

            $minAge = (int) Setting::retrieve('min_age_for_adult', 18);
            if (!$user->isOfAge($minAge)) {
                return response()->view('errors.adult', ['too_young' => true, 'min_age' => $minAge]);
            }
        }

        return $next($request);
    }
}
