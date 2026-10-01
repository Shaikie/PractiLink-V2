@extends('layouts.admin')

@section('title', 'Edit '.$application->reference_number)
@section('page_title', 'Edit application')
@section('page_description', $application->reference_number.' · Update the details before resubmitting.')

@section('page_actions')
    <a href="{{ route('student.applications.show', $application) }}" class="btn btn-light"><i class="fas fa-arrow-left" aria-hidden="true"></i> Application details</a>
@endsection

@section('content')
    <div class="clay-page-narrow">
        <x-ui.validation-summary class="mb-3" />
        <x-student.application-form
            :application="$application"
            :action="route('student.applications.update', $application)"
            :cancel-route="route('student.applications.show', $application)"
            :windows="collect()"
            :departments="$departments"
            method="PUT"
            submit-label="Save changes"
        />
    </div>
@endsection
