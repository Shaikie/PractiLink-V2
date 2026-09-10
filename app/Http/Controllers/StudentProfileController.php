<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Nationality;
use App\Models\StudyLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentProfileController extends Controller
{
    public function edit()
    {
        return view('student.profile.edit', [
            'student' => Auth::guard('students')->user(),
            'nationalities' => Nationality::orderBy('name')->get(),
            'institutions' => Institution::orderBy('name')->get(),
            'courses' => Course::orderBy('name')->get(),
            'studyLevels' => StudyLevel::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $student = Auth::guard('students')->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('students', 'email')->ignore($student->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['required', 'in:male,female,other'],
            'nationality_id' => ['required', 'exists:nationalities,id'],
            'institution_id' => ['required', 'exists:institutions,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'study_level_id' => ['required', 'exists:study_levels,id'],
        ]);

        $student->update($validated);

        return back()->with('success', 'Student profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $student = Auth::guard('students')->user();

        if (! Hash::check($validated['current_password'], $student->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $student->update(['password' => $validated['password']]);

        return back()->with('success', 'Password changed successfully.');
    }
}
