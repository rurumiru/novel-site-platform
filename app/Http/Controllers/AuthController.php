<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Events\Verified;

class AuthController extends Controller {
    public function showLogin() { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    public function login(Request $request) {
        $data = $request->validate(['login' => 'required|string', 'password' => 'required']);
        $login = $data['login'];

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$field => $login, 'password' => $data['password']];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }
        return back()->withInput($request->only('login'))->withErrors(['login' => 'Неверный логин или пароль']);
    }

    public function register(Request $request) {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|min:3|max:30|alpha_dash|unique:users',
            'email'    => 'required|email|unique:users',
            'social_link' => 'required|string',
            'password' => 'required|min:6|confirmed'
        ], [
            'username.unique'    => 'Этот логин уже занят.',
            'username.alpha_dash'=> 'Логин может содержать только латинские буквы, цифры и символ _.',
            'username.min'       => 'Логин должен быть не короче 3 символов.',
            'username.max'       => 'Логин не может быть длиннее 30 символов.',
            'email.unique'       => 'Этот email уже зарегистрирован.',
        ]);
        $user = User::create([
            'name'        => $data['name'],
            'username'    => strtolower($data['username']),
            'email'       => $data['email'],
            'social_link' => $data['social_link'],
            'password'    => Hash::make($data['password'])
        ]);
        $user->sendEmailVerificationNotification();
        Auth::login($user);
        return redirect('/')->with('success', 'Проверьте почту — на неё отправлена ссылка для подтверждения.');
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function profile() {
        $user = Auth::user();
        $favorites = $user->favorites()->withCount('chapters')->get();

        $history = \App\Models\ReadingProgress::where('user_id', $user->id)
            ->with(['novel' => fn($q) => $q->select('id', 'title', 'cover_image', 'slug'), 'chapter:id,title,novel_id'])
            ->latest('updated_at')
            ->get()
            ->unique('novel_id')
            ->values();

        return view('auth.profile', compact('user', 'favorites', 'history'));
    }

    public function updateProfile(Request $request) {
        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            $request->validate(['avatar' => 'image|max:5120']);

            if ($user->avatar) Storage::disk('s3')->delete($user->avatar);
            $user->avatar = $request->file('avatar')->store('avatars', 's3');
            $user->save();

            return back()->with('success', 'Аватар обновлен');
        }

        if ($request->hasFile('banner')) {
            if (!$user->can_set_banner) abort(403);

            $request->validate(['banner' => 'image|max:10240']);

            if ($user->banner_image) Storage::disk('s3')->delete($user->banner_image);
            $user->banner_image = $request->file('banner')->store('banners', 's3');
            $user->save();

            return back()->with('success', 'Баннер обновлен');
        }

        $rules = [
            'name'          => 'required|string|max:255',
            'username'      => 'nullable|string|min:3|max:30|alpha_dash|unique:users,username,' . $user->id,
            'social_link'   => 'nullable|string',
            'donation_link' => 'nullable|url',
            'bio'           => 'nullable|string|max:1000',
            'bundle_discount' => 'nullable|integer|min:0|max:90',
        ];
        if (!$user->birth_date) {
            $rules['birth_date'] = 'required|date|before:' . now()->subYears(5)->toDateString() . '|after:1920-01-01';
        }
        if (!$user->gender) {
            $rules['gender'] = 'required|in:male,female,hidden';
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('commenter_badges')) {
            $rules['commenter_badge_id'] = 'nullable|exists:commenter_badges,id';
            $rules['commenter_background_id'] = 'nullable|exists:commenter_backgrounds,id';
        }
        $request->validate($rules, [
            'username.unique'    => 'Этот логин уже занят.',
            'username.alpha_dash'=> 'Логин может содержать только латинские буквы, цифры и символ _.',
            'username.min'       => 'Логин должен быть не короче 3 символов.',
            'birth_date.before'  => 'Слишком маленький возраст.',
            'birth_date.after'   => 'Слишком ранняя дата.',
            'gender.in'          => 'Выберите вариант из списка.',
        ]);

        if (!$user->birth_date && $request->filled('birth_date')) {
            $user->birth_date = $request->birth_date;
        }
        if (!$user->gender && $request->filled('gender')) {
            $user->gender = $request->gender;
        }

        $user->name = $request->name;
        if ($request->filled('username') && empty($user->username)) {
            $user->username = strtolower($request->username);
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('commenter_badges')) {
            $user->commenter_badge_id = $request->commenter_badge_id ?: null;
            $user->commenter_background_id = $request->commenter_background_id ?: null;
        }
        $user->social_link = $request->social_link;

        if ($user->donation_link !== $request->donation_link) {
            $user->donation_link = $request->donation_link;
            $user->is_donation_link_approved = false;
        }

        if ($request->bio && $request->bio !== $user->bio) {
            $user->bio_pending = $request->bio;
        }

        if ($user->hasRole('author') || $user->hasRole('super_admin')) {
            $user->bundle_discount = $request->bundle_discount ?? 15;
        }

        $user->save();

        return back()->with('success', 'Профиль обновлен');
    }

    public function verifyEmail(\Illuminate\Http\Request $request, $id, $hash) {
        $user = User::findOrFail($id);
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('profile')->with('success', 'Почта уже подтверждена.');
        }
        $user->markEmailAsVerified();
        event(new Verified($user));
        return redirect()->route('profile')->with('success', 'Почта успешно подтверждена!');
    }

    public function resendVerification(\Illuminate\Http\Request $request) {
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('profile')->with('success', 'Почта уже подтверждена.');
        }
        Auth::user()->sendEmailVerificationNotification();
        return back()->with('success', 'Ссылка для подтверждения отправлена на почту.');
    }
}
