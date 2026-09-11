<?php

namespace App\Http\Controllers;

use App\Models\PlacementLetter;
use Illuminate\Support\Facades\Auth;

class StudentPlacementController extends Controller
{
    public function letter(PlacementLetter $letter)
    {
        abort_unless($letter->placement->student_id === Auth::guard('students')->id(),403);
        return response($letter->content)->header('Content-Type','text/html; charset=UTF-8');
    }
}
