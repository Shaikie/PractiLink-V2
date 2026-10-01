<?php

namespace App\Http\Controllers;

use App\Models\Placement;
use App\Models\PlacementLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminPlacementLetterController extends Controller
{
    public function issue(Request $request, Placement $placement)
    {
        $letter = DB::transaction(function () use ($request, $placement): PlacementLetter {
            $lockedPlacement = Placement::query()
                ->lockForUpdate()
                ->findOrFail($placement->id);
            $version = ((int) $lockedPlacement->letters()->max('version')) + 1;
            $reference = 'PLL-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
            $content = view('admin.placements.letter', [
                'placement' => $lockedPlacement->load([
                    'student',
                    'organization',
                    'department',
                    'supervisor',
                    'application.applicationWindow.trainingType',
                ]),
                'reference' => $reference,
                'version' => $version,
            ])->render();

            return PlacementLetter::create([
                'placement_id' => $lockedPlacement->id,
                'version' => $version,
                'reference_number' => $reference,
                'issued_at' => today(),
                'content' => $content,
                'issued_by' => $request->user()->id,
            ]);
        });

        return response($letter->content)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function show(PlacementLetter $letter)
    {
        return response($letter->content)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
