<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Novel;
use App\Models\PlusPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SubscriptionController extends Controller {
    public function index() {
        $plans = Schema::hasTable('plus_plans')
            ? PlusPlan::active()->ordered()->get()
            : collect();

        $authors = User::whereHas('novels', function($q) {
            $q->where('price', '>', 0)->where('is_published', true);
        })->with(['novels' => function($q) {
            $q->where('price', '>', 0)->where('is_published', true);
        }])->get();

        return view('subscription.index', compact('plans', 'authors'));
    }

    public function subscribePlus(Request $request, PlusPlan $plan) {
        if (!Auth::check()) return redirect()->route('login');
        if (!$plan->is_active) abort(404);

        $hasPending = Subscription::where('user_id', Auth::id())
            ->where('type', 'plus')
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($hasPending) {
            return redirect()->route('subscription.index')
                ->with('info', 'У вас уже есть активная или ожидающая Plus-заявка.');
        }

        Subscription::create([
            'user_id'      => Auth::id(),
            'plus_plan_id' => $plan->id,
            'amount_paid'  => $plan->price ?? 0,
            'type'         => 'plus',
            'status'       => 'pending',
        ]);

        return redirect()->route('subscription.index')->with('success', 'Заявка на Plus-подписку отправлена! Оплатите по реквизитам ниже и дождитесь подтверждения администратором.');
    }

    public function subscribe(Request $request, Novel $novel) {
        if (!Auth::check()) return redirect()->route('login');
        
        $this->checkExisting($novel->id, null);

        Subscription::create([
            'user_id' => Auth::id(),
            'novel_id' => $novel->id,
            'author_id' => $novel->user_id,
            'amount_paid' => $novel->price,
            'type' => 'single',
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Заявка на новеллу отправлена!');
    }

    public function subscribeBundle(Request $request, User $author) {
        if (!Auth::check()) return redirect()->route('login');

        $this->checkExisting(null, $author->id);

        $totalPrice = $author->novels->where('price', '>', 0)->sum('price');
        $discount = $author->bundle_discount ?? 15;
        $bundlePrice = round($totalPrice * (1 - ($discount / 100))); 

        Subscription::create([
            'user_id' => Auth::id(),
            'author_id' => $author->id,
            'amount_paid' => $bundlePrice,
            'type' => 'author_bundle',
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Заявка на пакет отправлена!');
    }

    private function checkExisting($novelId, $authorId) {
        $query = Subscription::where('user_id', Auth::id())
            ->whereIn('status', ['active', 'pending']);
            
        if ($novelId) {
            $query->where('novel_id', $novelId);
        } elseif ($authorId) {
            $query->where('type', 'author_bundle')->where('author_id', $authorId);
        }

        if ($query->exists()) {
            abort(redirect()->back()->with('info', 'У вас уже есть активная заявка или подписка.'));
        }
    }
}
