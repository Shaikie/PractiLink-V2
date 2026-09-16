<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function createLink()
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request)
    {
        $data = $request->validate([
            'account_type' => ['required', 'in:student,staff'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($data['email']));
        $broker = $data['account_type'] === 'student' ? 'students' : 'users';
        $model = $data['account_type'] === 'student' ? Student::class : User::class;
        $throttleKey = 'password-reset:'.$broker.'|'.$email.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many reset requests. Please try again in a few minutes.',
            ]);
        }

        RateLimiter::hit($throttleKey, 300);

        if ($model::where('email', $email)->where('is_active', true)->exists()) {
            Password::broker($broker)->sendResetLink(['email' => $email]);
        }

        return back()->with('status', 'If an active account matches that email address, a password reset link has been sent.');
    }

    public function createResetForm(Request $request, string $token)
    {
        $accountType = $request->query('account_type', 'student');
        abort_unless(in_array($accountType, ['student', 'staff'], true), 404);

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
            'accountType' => $accountType,
        ]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'account_type' => ['required', 'in:student,staff'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->mixedCase()->numbers()],
            'password_confirmation' => ['required', 'string'],
        ]);

        $broker = $data['account_type'] === 'student' ? 'students' : 'users';
        $status = Password::broker($broker)->reset(
            [
                'email' => strtolower(trim($data['email'])),
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function ($user) use ($data): void {
                $user->forceFill([
                    'password' => $data['password'],
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => 'This password reset link is invalid or has expired. Please request a new one.',
            ]);
        }

        return redirect()->route('login')->with('success', 'Your password has been reset. You can now sign in.');
    }
}
