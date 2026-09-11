<?php

namespace App\Http\Controllers;

use App\Models\Placement;
use App\Models\PlacementLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPlacementLetterController extends Controller
{
    public function issue(Request $request, Placement $placement)
    {
        $version=((int)$placement->letters()->max('version'))+1;
        $reference='PLL-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        $content=view('admin.placements.letter',['placement'=>$placement->load(['student','organization','department','application.applicationWindow.trainingType']),'reference'=>$reference,'version'=>$version])->render();
        $letter=PlacementLetter::create(['placement_id'=>$placement->id,'version'=>$version,'reference_number'=>$reference,'issued_at'=>today(),'content'=>$content,'issued_by'=>$request->user()->id]);
        return response($letter->content)->header('Content-Type','text/html; charset=UTF-8');
    }

    public function show(PlacementLetter $letter)
    {
        return response($letter->content)->header('Content-Type','text/html; charset=UTF-8');
    }
}
