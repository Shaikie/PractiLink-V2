<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Department;
use App\Models\Organization;
use App\Models\Placement;
use App\Models\PlacementStatusHistory;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminPlacementController extends Controller
{
    public function index()
    {
        return view('admin.placements.index', ['placements'=>Placement::with(['student','organization','department','application'])->latest()->get()]);
    }

    public function create(Application $application)
    {
        abort_unless($application->status === 'ACCEPTED', 422, 'Only accepted applications can be placed.');
        abort_if($application->placement()->exists(), 422, 'This application already has a placement.');
        return view('admin.placements.form', ['application'=>$application->load('student','applicationWindow.trainingType'),'organizations'=>Organization::where('is_active',true)->orderBy('name')->get(),'departments'=>Department::orderBy('name')->get(),'supervisors'=>User::orderBy('name')->get()]);
    }

    public function store(Request $request, Application $application)
    {
        abort_unless($application->status === 'ACCEPTED', 422, 'Only accepted applications can be placed.');
        abort_if($application->placement()->exists(), 422, 'This application already has a placement.');
        $data=$request->validate([
            'organization_id'=>['required','exists:organizations,id'],'department_id'=>['nullable','exists:departments,id'],'supervisor_user_id'=>['nullable','exists:users,id'],
            'start_date'=>['required','date_format:Y-m-d','after_or_equal:today'],'end_date'=>['required','date_format:Y-m-d','after:start_date'],'location'=>['nullable','string','max:500'],'notes'=>['nullable','string','max:5000'],
        ]);
        if($data['start_date'] !== optional($application->training_start_date)->format('Y-m-d') || $data['end_date'] !== optional($application->training_end_date)->format('Y-m-d')) throw ValidationException::withMessages(['start_date'=>'Placement dates must match the approved application training dates.']);
        $placement=Placement::create($data + ['application_id'=>$application->id,'student_id'=>$application->student_id,'reference_number'=>$this->reference(),'status'=>'ALLOCATED']);
        PlacementStatusHistory::create(['placement_id'=>$placement->id,'to_status'=>'ALLOCATED','changed_by'=>$request->user()->id,'changed_at'=>now()]);
        AuditLogger::record('placement.created',$placement,null,$placement->toArray());
        return redirect()->route('admin.placements.index')->with('success','Placement allocated successfully.');
    }

    public function updateStatus(Request $request, Placement $placement)
    {
        $data=$request->validate(['status'=>['required','in:ALLOCATED,ACTIVE,COMPLETED,CANCELLED'],'comment'=>['nullable','string','max:5000']]);
        $old=$placement->status; $placement->update(['status'=>$data['status']]);
        PlacementStatusHistory::create(['placement_id'=>$placement->id,'from_status'=>$old,'to_status'=>$placement->status,'changed_by'=>$request->user()->id,'comment'=>$data['comment']??null,'changed_at'=>now()]);
        AuditLogger::record('placement.status_updated',$placement,['status'=>$old],['status'=>$placement->status]);
        return back()->with('success','Placement status updated.');
    }

    private function reference(): string
    {
        do { $reference='PLM-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)); } while(Placement::where('reference_number',$reference)->exists());
        return $reference;
    }
}
