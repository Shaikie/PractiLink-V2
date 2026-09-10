<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Nationality;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $user->update($validated);

            if ($user->student) {
                $user->student->update([
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Profile details updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function editStudent(Request $request)
    {
        $student = $request->user()->student;

        abort_unless($student, 404);

        return view('student.profile.edit', [
            'student' => $student,
            'nationalities' => Nationality::orderBy('name')->get(),
            'institutions' => Institution::orderBy('name')->get(),
            'courses' => Course::orderBy('name')->get(),
            'studyLevels' => \App\Models\StudyLevel::orderBy('name')->get(),
        ]);
    }

    public function updateStudent(Request $request)
    {
        $student = $request->user()->student;

        abort_unless($student, 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'registration_number' => ['required', 'string', 'max:100', Rule::unique('students', 'registration_number')->ignore($student->id)],
            'gender' => ['required', 'in:male,female,other'],
            'nationality_id' => ['required', 'exists:nationalities,id'],
            'institution_id' => ['required', 'exists:institutions,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'study_level_id' => ['required', 'exists:study_levels,id'],
        ]);

        DB::transaction(function () use ($student, $validated) {
            $student->update($validated);

            $student->user->update([
                'fullname' => trim($validated['first_name'].' '.$validated['last_name']),
                'username' => $validated['registration_number'],
            ]);
        });

        return back()->with('success', 'Student profile updated successfully.');
    }
}
