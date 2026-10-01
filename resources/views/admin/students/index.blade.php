@extends('layouts.admin')

@section('title', 'Students')
@section('page_title', 'Student directory')
@section('page_description', 'Review student records, academic information and account access.')

@section('content')
    <x-ui.validation-summary class="mb-3" />

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">All students</h3>
                <p class="mb-0 mt-1 text-muted small">{{ $students->total() }} registered {{ \Illuminate\Support\Str::plural('student', $students->total()) }}</p>
            </div>
        </div>
        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('admin.students.index') }}" class="pl-filter-form">
                <div class="form-group mb-2">
                    <label for="student-search">Search</label>
                    <div class="pl-input-wrap">
                        <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                        <input id="student-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Name, registration number or email">
                    </div>
                </div>
                <div class="form-group mb-2">
                    <label for="student-status">Account status</label>
                    <select id="student-status" name="status" class="form-control">
                        <option value="">All statuses</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="pl-filter-actions">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Apply</button>
                    @if(array_filter($filters))<a href="{{ route('admin.students.index') }}" class="btn btn-light">Reset</a>@endif
                </div>
            </form>
        </div>

        @if($students->isEmpty())
            <div class="card-body">
                <x-ui.empty-state
                    icon="fa-users"
                    title="No students found"
                    message="Try a different search or account-status filter."
                    compact
                />
            </div>
        @else
            <div class="table-responsive">
                <table class="table clay-table mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Registration</th>
                            <th>Academic details</th>
                            <th>Email</th>
                            <th>Account</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="account-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::of($student->full_name)->explode(' ')->take(2)->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->implode('') }}</span>
                                        <strong>{{ $student->full_name }}</strong>
                                    </div>
                                </td>
                                <td>{{ $student->registration_number }}</td>
                                <td>
                                    <span class="d-block">{{ $student->course->name }}</span>
                                    <span class="small text-muted">{{ $student->institution->name }} · {{ $student->studyLevel->name }}</span>
                                </td>
                                <td>{{ $student->email }}</td>
                                <td><x-ui.status-badge :status="$student->is_active ? 'Active' : 'Inactive'" :tone="$student->is_active ? 'success' : 'neutral'" /></td>
                                <td class="text-right"><a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline-primary">Edit <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($students->hasPages())
                <div class="px-3 py-3 border-top">{{ $students->links() }}</div>
            @endif
        @endif
    </div>
@endsection
