<?php

namespace App\Http\Controllers;

use App\Models\EmailChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EmailChangeController extends Controller
{
    public function submit(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'new_email' => 'required|email|unique:users,email',
        ], [
            'new_email.required' => 'Введите новый email.',
            'new_email.email'    => 'Некорректный email.',
            'new_email.unique'   => 'Этот email уже занят другим пользователем.',
        ]);

        if (strcasecmp($data['new_email'], (string) $user->email) === 0) {
            return back()->withErrors(['new_email' => 'Новый email совпадает с текущим.']);
        }

        $active = EmailChangeRequest::where('user_id', $user->id)
            ->whereIn('status', [EmailChangeRequest::STATUS_PENDING_CODE, EmailChangeRequest::STATUS_AWAITING_ADMIN])
            ->first();
        if ($active) {
            return back()->withErrors(['new_email' => 'У вас уже есть активная заявка на смену почты. Дождитесь её рассмотрения.']);
        }

        $code = (string) random_int(100000, 999999);

        $req = EmailChangeRequest::create([
            'user_id'      => $user->id,
            'new_email'    => $data['new_email'],
            'code'         => $code,
            'code_sent_at' => now(),
            'status'       => EmailChangeRequest::STATUS_PENDING_CODE,
        ]);

        try {
            Mail::raw(
                "Здравствуйте!\n\n" .
                "Вы запросили смену email-а на сайте на этот адрес.\n" .
                "Код подтверждения: {$code}\n\n" .
                "Введите его в профиле, чтобы заявка ушла на одобрение администратору.\n\n" .
                "Если вы не запрашивали смену почты — просто проигнорируйте это письмо.",
                function ($m) use ($req) {
                    $m->to($req->new_email)->subject('Код подтверждения смены почты');
                }
            );
        } catch (\Throwable $e) {
            $req->delete();
            return back()->withErrors(['new_email' => 'Не удалось отправить письмо на новый email. Проверьте адрес и попробуйте позже.']);
        }

        return back()->with('success', 'Код подтверждения отправлен на ' . $req->new_email . '. Введите его ниже.');
    }

    public function verify(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'code' => 'required|string|size:6',
        ], [
            'code.required' => 'Введите код.',
            'code.size'     => 'Код должен состоять из 6 цифр.',
        ]);

        $req = EmailChangeRequest::where('user_id', $user->id)
            ->where('status', EmailChangeRequest::STATUS_PENDING_CODE)
            ->latest()
            ->first();
        if (!$req) {
            return back()->withErrors(['code' => 'Активной заявки нет. Подайте заявку заново.']);
        }

        if ($req->code_sent_at && $req->code_sent_at->diffInMinutes(now()) > 30) {
            $req->update(['status' => EmailChangeRequest::STATUS_REJECTED, 'admin_note' => 'Код устарел']);
            return back()->withErrors(['code' => 'Срок действия кода истёк. Подайте заявку заново.']);
        }

        if (!hash_equals((string) $req->code, (string) $data['code'])) {
            return back()->withErrors(['code' => 'Неверный код.']);
        }

        $req->update([
            'code_verified_at' => now(),
            'status'           => EmailChangeRequest::STATUS_AWAITING_ADMIN,
            'code'             => null,
        ]);

        return back()->with('success', 'Код подтверждён. Заявка отправлена администратору на одобрение.');
    }

    public function cancel(Request $request)
    {
        $user = Auth::user();
        EmailChangeRequest::where('user_id', $user->id)
            ->whereIn('status', [EmailChangeRequest::STATUS_PENDING_CODE, EmailChangeRequest::STATUS_AWAITING_ADMIN])
            ->delete();

        return back()->with('success', 'Заявка отменена. Можете подать новую.');
    }
}
