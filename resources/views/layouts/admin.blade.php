@php
$user=auth('web')->user();$student=auth('students')->user();$account=$user?:$student;$isStudent=$student!==null;$accountRoles=($roles??collect())->map(fn($role)=>is_object($role)?($role->name??'User'):$role);$unreadNotifications=$account?->unreadNotifications()->count()??0;
@endphp
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="robots" content="noindex,nofollow"><title>@yield('title','Dashboard') | PractiLink</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"><link rel="stylesheet" href="{{ asset('css/practilink.css') }}"><link rel="stylesheet" href="{{ asset('css/skeuomorphism.css') }}"></head><body class="hold-transition sidebar-mini layout-fixed"><div class="wrapper"><nav class="main-header navbar navbar-expand navbar-white navbar-light"><ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li></ul><ul class="navbar-nav ml-auto align-items-center"><li class="nav-item"><a class="nav-link position-relative" href="{{ route('notifications.index') }}"><i class="far fa-bell"></i>@if($unreadNotifications>0)<span class="badge badge-success navbar-badge">{{ $unreadNotifications>99?'99+':$unreadNotifications }}</span>@endif</a></li><li class="nav-item mr-2 d-none d-md-block"><span class="badge badge-{{ $isStudent?'success':'primary' }} px-3 py-2">{{ $isStudent?'Student':'Staff / Admin' }}</span></li><li class="nav-item dropdown"><a class="nav-link" data-toggle="dropdown" href="#"><i class="far fa-user-circle mr-1"></i>{{ $isStudent?$account->full_name:$account->fullname }}</a><div class="dropdown-menu dropdown-menu-right"><a href="{{ $isStudent?route('student.profile.edit'):route('profile.edit') }}" class="dropdown-item"><i class="fas fa-user-edit mr-2"></i> My Profile</a><div class="dropdown-divider"></div><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt mr-2"></i> Logout</button></form></div></li></ul></nav><aside class="main-sidebar sidebar-dark-primary elevation-4"><a href="{{ route('dashboard') }}" class="brand-link"><span class="brand-mark">P</span><span class="brand-text font-weight-bold">PractiLink</span></a><div class="sidebar"><div class="user-panel mt-3 pb-3 mb-3 d-flex"><div class="image"><i class="fas fa-user-circle fa-2x text-white"></i></div><div class="info"><a href="{{ $isStudent?route('student.profile.edit'):route('profile.edit') }}" class="d-block text-white">{{ $isStudent?$account->full_name:$account->fullname }}</a><small class="text-white-50">{{ $isStudent?$account->registration_number:($accountRoles->first()??'User') }}</small></div></div><nav class="mt-2"><ul class="nav nav-pills nav-sidebar flex-column"><li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link @if(request()->routeIs('dashboard')) active @endif"><i class="nav-icon fas fa-th-large"></i><p>Dashboard</p></a></li><li class="nav-item"><a href="{{ route('notifications.index') }}" class="nav-link @if(request()->routeIs('notifications.*')) active @endif"><i class="nav-icon far fa-bell"></i><p>Notifications @if($unreadNotifications>0)<span class="right badge badge-success">{{ $unreadNotifications }}</span>@endif</p></a></li>@if($isStudent)<li class="nav-header text-uppercase small">Student</li><li class="nav-item"><a href="{{ route('student.applications.index') }}" class="nav-link @if(request()->routeIs('student.applications.*')) active @endif"><i class="nav-icon fas fa-file-alt"></i><p>My Applications</p></a></li><li class="nav-item"><a href="{{ route('student.profile.edit') }}" class="nav-link @if(request()->routeIs('student.profile.*')) active @endif"><i class="nav-icon fas fa-user"></i><p>My Profile</p></a></li>@else<li class="nav-header text-uppercase small">Operations</li>@if($user->hasPermission('applications.view'))<li class="nav-item"><a href="{{ route('admin.applications.index') }}" class="nav-link @if(request()->routeIs('admin.applications.*')) active @endif"><i class="nav-icon fas fa-inbox"></i><p>Review Applications</p></a></li>@endif @if($user->hasPermission('applications.manage'))<li class="nav-item"><a href="{{ route('admin.application-windows.index') }}" class="nav-link @if(request()->routeIs('admin.application-windows.*')) active @endif"><i class="nav-icon fas fa-calendar-alt"></i><p>Application Windows</p></a></li>@endif @if($user->hasPermission('workflows.manage'))<li class="nav-item"><a href="{{ route('admin.workflows.index') }}" class="nav-link @if(request()->routeIs('admin.workflows.*')) active @endif"><i class="nav-icon fas fa-project-diagram"></i><p>Workflows</p></a></li>@endif @if($user->hasPermission('users.manage'))<li class="nav-item"><a href="{{ route('admin.staff.index') }}" class="nav-link @if(request()->routeIs('admin.staff.*')) active @endif"><i class="nav-icon fas fa-users-cog"></i><p>Staff & Roles</p></a></li>@endif @if($user->hasPermission('placements.manage'))<li class="nav-item"><a href="{{ route('admin.placements.index') }}" class="nav-link @if(request()->routeIs('admin.placements.*')) active @endif"><i class="nav-icon fas fa-map-marker-alt"></i><p>Placements</p></a></li>@endif<li class="nav-header text-uppercase small">Account</li><li class="nav-item"><a href="{{ route('profile.edit') }}" class="nav-link @if(request()->routeIs('profile.*')) active @endif"><i class="nav-icon fas fa-user"></i><p>My Profile</p></a></li>@endif</ul></nav></div></aside><div class="content-wrapper"><section class="content-header"><div class="container-fluid"><div class="row align-items-center mb-2"><div class="col-md-8"><h1>@yield('page_title','Dashboard')</h1>@hasSection('page_description')<p class="text-muted mb-0">@yield('page_description')</p>@endif</div><div class="col-md-4 mt-2 mt-md-0 text-md-right">@yield('page_actions')</div></div></div></section><section class="content pb-4"><div class="container-fluid">@if(session('success'))<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif @yield('content')</div></section></div><footer class="main-footer"><strong>PractiLink</strong> &copy; {{ date('Y') }}<span class="float-right d-none d-sm-inline">Practical Training Platform</span></footer></div><script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script><script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script><script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script><script src="https://cdn.jsdelivr.net/npm/flatpickr"></script><script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js"></script><script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.date-range-group').forEach(group => {
        const startInput = group.querySelector('[data-date-start]');
        const endInput = group.querySelector('[data-date-end]');
        if (!startInput || !endInput) return;
        const startPicker = flatpickr(startInput, {dateFormat:'Y-m-d', minDate:startInput.dataset.minDate || null, allowInput:true});
        const endPicker = flatpickr(endInput, {dateFormat:'Y-m-d', minDate:endInput.dataset.minDate || null, allowInput:true});
        startPicker.config.onChange.push(selected => {
            if (!selected.length) return;
            const min = new Date(selected[0]);
            min.setDate(min.getDate() + 1);
            endPicker.set('minDate', min);
            if (endPicker.selectedDates[0] && endPicker.selectedDates[0] <= selected[0]) endPicker.clear();
        });
    });

    document.querySelectorAll('.datetime-range-group').forEach(group => {
        const openInput = group.querySelector('[data-datetime-start]');
        const closeInput = group.querySelector('[data-datetime-end]');
        if (!openInput || !closeInput) return;
        const openPicker = flatpickr(openInput, {enableTime:true, dateFormat:'Y-m-d\\TH:i', time_24hr:true, minDate:openInput.dataset.minDate || null, allowInput:true});
        const closePicker = flatpickr(closeInput, {enableTime:true, dateFormat:'Y-m-d\\TH:i', time_24hr:true, allowInput:true});
        const syncClose = selected => {
            if (!selected.length) return;
            const min = new Date(selected[0].getTime() + 30 * 60 * 1000);
            closePicker.set('minDate', min);
            if (closePicker.selectedDates[0] && closePicker.selectedDates[0] <= min) closePicker.clear();
        };
        openPicker.config.onChange.push(syncClose);
        if (openPicker.selectedDates.length) syncClose(openPicker.selectedDates);
    });

    if (window.tinymce) {
        tinymce.init({
            selector: '.tinymce-editor',
            menubar: false,
            plugins: 'lists link',
            toolbar: 'undo redo | blocks | bold italic | bullist numlist | link | removeformat',
            branding: false,
            height: 300,
            promotion: false,
        });
    }
});
</script>@stack('scripts')</body></html>
