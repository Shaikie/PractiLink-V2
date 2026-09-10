<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Nationality;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudyLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['is_active'] = true;
        $credentials['locked_at'] = null;

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect or the account is inactive.',
            ]);
        }

        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('dashboard'));
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
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['required', 'in:male,female,other'],
            'nationality_id' => ['required', 'exists:nationalities,id'],
            'institution_id' => ['required', 'exists:institutions,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'study_level_id' => ['required', 'exists:study_levels,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'fullname' => trim($validated['first_name'].' '.$validated['last_name']),
                'username' => $validated['registration_number'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => $validated['password'],
                'is_active' => true,
            ]);

            Student::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'registration_number' => $validated['registration_number'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'gender' => $validated['gender'],
                'nationality_id' => $validated['nationality_id'],
                'institution_id' => $validated['institution_id'],
                'course_id' => $validated['course_id'],
                'study_level_id' => $validated['study_level_id'],
            ]);

            $studentRole = Role::where('slug', 'student')->firstOrFail();
            $user->roles()->attach($studentRole->id);
        });

        return redirect()->route('login')->with('success', 'Student account created successfully. You can now log in.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
