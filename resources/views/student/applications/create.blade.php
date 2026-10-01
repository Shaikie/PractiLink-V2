@extends('layouts.admin')

@section('title', 'Start application')
@section('page_title', 'Start new application')
@section('page_description', 'Choose an open training window and save a draft before submitting it for review.')

@section('content')
    <div class="clay-page-narrow">
        <x-ui.validation-summary class="mb-3" />

        @if($windows->isEmpty())
            <div class="card">
                <div class="card-body">
                    <x-ui.empty-state
                        icon="fa-calendar-times"
                        title="No open application windows"
                        message="There are currently no training windows accepting applications. Check back later or contact the placement office."
                    >
                        <a href="{{ route('student.applications.index') }}" class="btn btn-light">Back to applications</a>
                    </x-ui.empty-state>
                </div>
            </div>
        @else
            <x-student.application-form
                :action="route('student.applications.store')"
                :cancel-route="route('student.applications.index')"
                :windows="$windows"
                :departments="$departments"
                submit-label="Save draft"
            />
        @endif
    </div>
@endsection
