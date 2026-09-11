@extends('layouts.admin')

@section('title', 'My Applications')
@section('page_title', 'My Applications')
@section('page_description', 'Create and track your practical training applications.')

@section('content')
<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Application history</h3></div>
            <div class="card-body p-0">
                @if ($applications->isEmpty())
                    <div class="p-4 text-center text-muted"><i class="fas fa-folder-open fa-2x mb-3"></i><p class="mb-0">You have no applications yet.</p></div>
                @else
                    <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Reference</th><th>Training</th><th>Dates</th><th>Status</th><th></th></tr></thead><tbody>
                    @foreach ($applications as $application)
                        <tr><td class="font-weight-bold">{{ $application->reference_number }}</td><td>{{ $application->applicationWindow->trainingType->name }}</td><td>{{ $application->training_start_date?->format('d M Y') }} - {{ $application->training_end_date?->format('d M Y') }}</td><td><span class="badge badge-{{ $application->status === 'ACCEPTED' ? 'success' : 'primary' }}">{{ str_replace('_',' ',$application->status) }}</span></td><td class="text-right"><a href="{{ route('student.applications.show',$application) }}" class="btn btn-sm btn-outline-primary">View</a></td></tr>
                    @endforeach
                    </tbody></table></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card"><div class="card-header"><h3 class="card-title">Open application windows</h3></div><div class="card-body">
            @forelse ($windows as $window)
                <div class="border rounded p-3 mb-3">
                    <div class="font-weight-bold">{{ $window->name }}</div>
                    <div class="small text-muted mb-3">{{ $window->trainingType->name }} · closes {{ $window->closes_at->format('d M Y, H:i') }}</div>
                    <form method="POST" action="{{ route('student.applications.store') }}" class="js-training-form">@csrf
                        <input type="hidden" name="application_window_id" value="{{ $window->id }}">
                        <div class="form-group mb-2"><label class="small font-weight-bold">Training start date</label><input type="date" name="training_start_date" class="form-control form-control-sm js-start-date" min="{{ now()->format('Y-m-d') }}" required></div>
                        <div class="form-group mb-2"><label class="small font-weight-bold">Training end date</label><input type="date" name="training_end_date" class="form-control form-control-sm js-end-date" min="{{ now()->addDay()->format('Y-m-d') }}" required></div>
                        <div class="form-group mb-3"><label class="small font-weight-bold">Notes <span class="text-muted font-weight-normal">(optional)</span></label><textarea name="notes" class="form-control form-control-sm" rows="2" maxlength="5000"></textarea></div>
                        <button class="btn btn-primary btn-sm btn-block" type="submit">Start application</button>
                    </form>
                </div>
            @empty
                <p class="text-muted mb-0">There are no open application windows right now.</p>
            @endforelse
        </div></div>
    </div>
</div>
@push('scripts')
<script>
document.querySelectorAll('.js-training-form').forEach(form => {
    const start=form.querySelector('.js-start-date'), end=form.querySelector('.js-end-date');
    start.addEventListener('change',()=>{ end.min=start.value ? new Date(new Date(start.value+'T00:00:00').getTime()+86400000).toISOString().slice(0,10) : '{{ now()->addDay()->format('Y-m-d') }}'; if(end.value && end.value<=start.value) end.value=''; });
    form.addEventListener('submit',e=>{ if(!start.value || !end.value || end.value<=start.value){e.preventDefault(); alert('Training end date must be after the training start date.');} });
});
</script>
@endpush
@endsection
