<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Nationality;
use App\Models\Student;
use App\Models\StudyLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('web')->check() || Auth::guard('students')->check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($validated['login']);
        $normalizedLogin = strtolower($login);
        $throttleKey = 'login:'.$normalizedLogin.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages(['login' => 'Too many login attempts. Please try again in a minute.']);
        }

        $user = User::where('email', $normalizedLogin)->orWhere('username', $login)->first();

        if ($user) {
            $authenticated = Auth::guard('web')->attempt([
                'email' => $user->email,
                'password' => $validated['password'],
                'is_active' => true,
                'locked_at' => null,
            ], $request->boolean('remember'));

            if ($authenticated) {
                $request->session()->regenerate();
                RateLimiter::clear($throttleKey);
                $request->user('web')->forceFill(['last_login_at' => now()])->save();
                return redirect()->intended(route('dashboard'));
            }
        } else {
            $student = Student::where('email', $normalizedLogin)->orWhere('registration_number', $login)->first();

            if ($student && Auth::guard('students')->attempt([
                'email' => $student->email,
                'password' => $validated['password'],
                'is_active' => true,
                'locked_at' => null,
            ], $request->boolean('remember'))) {
                $request->session()->regenerate();
                RateLimiter::clear($throttleKey);
                $request->user('students')->forceFill(['last_login_at' => now()])->save();
                return redirect()->intended(route('dashboard'));
            }
        }

        RateLimiter::hit($throttleKey, 60);
        throw ValidationException::withMessages(['login' => 'The provided credentials are incorrect or the account is inactive.']);
    }

    public function showRegister()
    {
        return view('auth.register', [
            'nationalities' => Nationality::orderBy('name')->get(),
            'institutions' => Institution::orderBy('name')->get(),
            'courses' => Course::orderBy('name')->get(),
            'studyLevels' => StudyLevel::orderBy('name')->get(),
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'registration_number' => ['required', 'string', 'max:100', 'unique:students,registration_number'],
            'email' => ['required', 'email', 'max:255', Rule::unique('students', 'email')],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['required', 'in:male,female,other'],
            'nationality_id' => ['required', 'exists:nationalities,id'],
            'institution_id' => ['required', 'exists:institutions,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'study_level_id' => ['required', 'exists:study_levels,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $validated['email'] = strtolower(trim($validated['email']));
        if (User::where('email', $validated['email'])->exists()) {
            throw ValidationException::withMessages(['email' => 'That email address is already registered.']);
        }

        DB::transaction(fn () => Student::create([
            'first_name' => $validated['first_name'], 'last_name' => $validated['last_name'],
            'registration_number' => $validated['registration_number'], 'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null, 'gender' => $validated['gender'],
            'nationality_id' => $validated['nationality_id'], 'institution_id' => $validated['institution_id'],
            'course_id' => $validated['course_id'], 'study_level_id' => $validated['study_level_id'],
            'password' => $validated['password'], 'is_active' => true,
        ]));

        return redirect()->route('login')->with('success', 'Student account created successfully. You can now log in.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('students')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
