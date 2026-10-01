<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user('web'),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user('web');

        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $validated['email'] = strtolower(trim($validated['email']));
        if (Student::where('email', $validated['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'That email address is already registered to a student account.',
            ]);
        }

        $user->update($validated);

        return back()->with('success', 'Profile details updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user('web')->update([
            'password' => $validated['password'],
            'remember_token' => Str::random(60),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}
