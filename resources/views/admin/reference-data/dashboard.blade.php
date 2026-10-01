@extends('layouts.admin')

@section('title', 'Configuration')
@section('page_title', 'Configuration')
@section('page_description', 'Manage the reference data that powers applications, registration and placement workflows.')

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div class="pl-next-callout">
                <span><i class="fas fa-database" aria-hidden="true"></i></span>
                <div>
                    <strong>Keep the workspace consistent</strong>
                    <p>Changes here affect the options available to students, reviewers and placement teams. Records with existing workflow history are retained and can be updated instead of deleted.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($resources as $resource)
            <div class="col-xl-3 col-md-6 mb-3">
                <a href="{{ route('admin.reference-data.'.$resource['type'].'.index') }}" class="pl-config-card">
                    <span class="pl-config-icon"><i class="fas {{ $resource['icon'] }}" aria-hidden="true"></i></span>
                    <span class="pl-config-copy">
                        <strong>{{ $resource['title'] }}</strong>
                        <small>{{ $resource['description'] }}</small>
                    </span>
                    <span class="pl-config-count">{{ $resource['count'] }}</span>
                    <i class="fas fa-chevron-right pl-config-arrow" aria-hidden="true"></i>
                </a>
            </div>
        @endforeach
    </div>
@endsection
