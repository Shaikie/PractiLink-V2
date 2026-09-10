<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::guard('web')->user();
        $student = Auth::guard('students')->user();

        if ($user) {
            return view('dashboard', [
                'accountType' => 'user',
                'account' => $user,
                'roles' => $user->roles()->pluck('name'),
            ]);
        }

        return view('dashboard', [
            'accountType' => 'student',
            'account' => $student,
            'roles' => collect(),
        ]);
    }
}
