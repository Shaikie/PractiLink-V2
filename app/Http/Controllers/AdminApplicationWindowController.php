<?php

namespace App\Http\Controllers;

use App\Models\ApplicationWindow;
use App\Models\TrainingType;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AdminApplicationWindowController extends Controller
{
    public function index(){return view('admin.application-windows.index',['windows'=>ApplicationWindow::with('trainingType')->latest('opens_at')->get()]);}
    public function create(){return view('admin.application-windows.form',['window'=>new ApplicationWindow(),'trainingTypes'=>TrainingType::orderBy('name')->get()]);}
    public function store(Request $request){$validated=$this->validateWindow($request);$window=ApplicationWindow::create($validated);AuditLogger::record('application_window.created',$window,null,$window->toArray());return redirect()->route('admin.application-windows.index')->with('success','Application window created.');}
    public function edit(ApplicationWindow $applicationWindow){return view('admin.application-windows.form',['window'=>$applicationWindow,'trainingTypes'=>TrainingType::orderBy('name')->get()]);}
    public function update(Request $request,ApplicationWindow $applicationWindow){$validated=$this->validateWindow($request,$applicationWindow);$old=$applicationWindow->getOriginal();$applicationWindow->update($validated);AuditLogger::record('application_window.updated',$applicationWindow,$old,$applicationWindow->fresh()->toArray());return redirect()->route('admin.application-windows.index')->with('success','Application window updated.');}
    public function destroy(ApplicationWindow $applicationWindow){abort_if($applicationWindow->applications()->exists(),422,'This window cannot be deleted because it has applications.');$old=$applicationWindow->toArray();$applicationWindow->delete();AuditLogger::record('application_window.deleted',null,$old,null);return back()->with('success','Application window deleted.');}
    private function validateWindow(Request $request,?ApplicationWindow $existing=null): array
    {
        $validated=$request->validate(['name'=>['required','string','max:255'],'training_type_id'=>['required','integer','exists:training_types,id'],'opens_at'=>['required','date_format:Y-m-d\\TH:i'],'closes_at'=>['required','date_format:Y-m-d\\TH:i','after:opens_at'],'is_active'=>['nullable','boolean']]);
        $open=Carbon::createFromFormat('Y-m-d\\TH:i',$validated['opens_at']);$close=Carbon::createFromFormat('Y-m-d\\TH:i',$validated['closes_at']);
        if(!$existing&&$open->isPast())throw ValidationException::withMessages(['opens_at'=>'The opening date and time cannot be in the past.']);
        if($close->lessThanOrEqualTo($open))throw ValidationException::withMessages(['closes_at'=>'Closing must be after opening.']);
        if($open->diffInMinutes($close)<30)throw ValidationException::withMessages(['closes_at'=>'An application window must remain open for at least 30 minutes.']);
        $overlap=ApplicationWindow::where('training_type_id',$validated['training_type_id'])->when($existing,fn($q)=>$q->where('id','!=',$existing->id))->where('opens_at','<',$close)->where('closes_at','>',$open)->exists();
        if($overlap)throw ValidationException::withMessages(['opens_at'=>'This window overlaps another window for the same training type.']);
        $validated['opens_at']=$open;$validated['closes_at']=$close;$validated['is_active']=$request->boolean('is_active');return $validated;
    }
}
