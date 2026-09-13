<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Support\Facades\Auth;

class StudentApplicationProgressController extends Controller
{
    public function show(Application $application)
    {
        abort_unless($application->student_id === Auth::guard('students')->id(), 403);

        $application->load([
            'applicationWindow.trainingType',
            'department',
            'workflow.currentStage',
            'workflow.assignedUser',
            'workflow.version.stages.duties',
            'workflow.history.fromStage',
            'workflow.history.toStage',
        ]);

        return view('student.applications.progress', compact('application'));
    }
}
