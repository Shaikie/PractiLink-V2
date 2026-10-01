<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Dashboard') | PractiLink</title>

    @fonts
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('css/practilink.css') }}">
</head>

<body class="hold-transition sidebar-mini layout-fixed pl-app-shell">
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-light pl-topbar" aria-label="Primary navigation">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link pl-icon-button" data-widget="pushmenu" href="#" role="button" aria-label="Toggle navigation">
                        <i class="fas fa-bars" aria-hidden="true"></i>
                    </a>
                </li>
                <li class="nav-item d-lg-none">
                    <a href="{{ route('dashboard') }}" class="pl-mobile-brand" aria-label="PractiLink dashboard">
                        <span class="brand-mark">P</span>
                        <span>PractiLink</span>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto align-items-center pl-topbar-actions">
                <li class="nav-item">
                    <a href="{{ route('notifications.index') }}" class="nav-link pl-icon-button" aria-label="@if($unreadNotifications > 0) {{ $unreadNotifications }} unread notifications @else Notifications @endif">
                        <i class="far fa-bell" aria-hidden="true"></i>
                        @if($unreadNotifications > 0)
                            <span class="navbar-badge">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item d-none d-md-block">
                    <span class="account-type-badge {{ $appIsStudent ? 'is-student' : 'is-staff' }}">
                        {{ $appIsStudent ? 'Student' : 'Staff / Admin' }}
                    </span>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link pl-account-menu" href="#" id="account-menu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="account-avatar" aria-hidden="true">{{ $appAccountInitials }}</span>
                        <span class="account-menu-copy d-none d-sm-flex">
                            <strong>{{ $appAccountDisplayName }}</strong>
                            <small>{{ $appAccountMeta }}</small>
                        </span>
                        <i class="fas fa-chevron-down account-menu-chevron" aria-hidden="true"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right pl-account-dropdown" aria-labelledby="account-menu">
                        <div class="dropdown-header">
                            <strong>{{ $appAccountDisplayName }}</strong>
                            <small>{{ $appAccountMeta }}</small>
                        </div>
                        <a href="{{ route($appProfileRoute) }}" class="dropdown-item">
                            <i class="fas fa-user-edit mr-2" aria-hidden="true"></i> My profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt mr-2" aria-hidden="true"></i> Log out
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ route('dashboard') }}" class="brand-link pl-brand-link">
                <span class="brand-mark">P</span>
                <span class="brand-text font-weight-bold">Practi<span>Link</span></span>
            </a>

            <div class="sidebar">
                <div class="user-panel pl-user-panel">
                    <div class="image">
                        <span class="account-avatar account-avatar-lg" aria-hidden="true">{{ $appAccountInitials }}</span>
                    </div>
                    <div class="info">
                        <a href="{{ route($appProfileRoute) }}" class="d-block text-white">{{ $appAccountDisplayName }}</a>
                        <small>{{ $appAccountMeta }}</small>
                    </div>
                </div>

                <nav class="mt-2" aria-label="Workspace navigation">
                    <ul class="nav nav-pills nav-sidebar flex-column">
                        <x-layouts.sidebar-link
                            :route="route('dashboard')"
                            pattern="dashboard"
                            icon="fa-solid fa-th-large"
                            label="Overview"
                        />
                        <x-layouts.sidebar-link
                            :route="route('notifications.index')"
                            pattern="notifications.*"
                            icon="fa-regular fa-bell"
                            label="Notifications"
                            :badge="$unreadNotifications"
                        />

                        @if($appIsStudent)
                            <li class="nav-header">Student workspace</li>
                            <x-layouts.sidebar-link
                                :route="route('student.applications.index')"
                                pattern="student.applications.*"
                                icon="fa-regular fa-file-alt"
                                label="My applications"
                            />
                            <x-layouts.sidebar-link
                                :route="route('student.profile.edit')"
                                pattern="student.profile.*"
                                icon="fa-regular fa-user"
                                label="My profile"
                            />
                        @else
                            <li class="nav-header">Operations</li>
                            @if($appPermissions->contains('applications.view'))
                                <x-layouts.sidebar-link
                                    :route="route('admin.applications.index')"
                                    pattern="admin.applications.*"
                                    icon="fa-solid fa-inbox"
                                    label="Review applications"
                                />
                            @endif
                            @if($appPermissions->contains('applications.manage'))
                                <x-layouts.sidebar-link
                                    :route="route('admin.application-windows.index')"
                                    pattern="admin.application-windows.*"
                                    icon="fa-regular fa-calendar"
                                    label="Application windows"
                                />
                            @endif
                            @if($appPermissions->contains('workflows.manage'))
                                <x-layouts.sidebar-link
                                    :route="route('admin.workflows.index')"
                                    pattern="admin.workflows.*"
                                    icon="fa-solid fa-project-diagram"
                                    label="Workflows"
                                />
                            @endif
                            @if($appPermissions->contains('users.manage'))
                                <x-layouts.sidebar-link
                                    :route="route('admin.staff.index')"
                                    pattern="admin.staff.*"
                                    icon="fa-solid fa-users-cog"
                                    label="Staff & roles"
                                />
                            @endif
                            @if($appPermissions->contains('placements.manage'))
                                <x-layouts.sidebar-link
                                    :route="route('admin.placements.index')"
                                    pattern="admin.placements.*"
                                    icon="fa-solid fa-map-marker-alt"
                                    label="Placements"
                                />
                            @endif
                            @if($appPermissions->contains('users.manage') || $appPermissions->contains('organizations.manage') || $appPermissions->contains('students.manage'))
                                <li class="nav-header">Configuration</li>
                                @if($appPermissions->contains('users.manage'))
                                    <x-layouts.sidebar-link
                                        :route="route('admin.reference-data.index')"
                                        pattern="admin.reference-data.*"
                                        icon="fa-solid fa-sliders-h"
                                        label="Reference data"
                                    />
                                @endif
                                @if($appPermissions->contains('organizations.manage'))
                                    <x-layouts.sidebar-link
                                        :route="route('admin.organizations.index')"
                                        pattern="admin.organizations.*"
                                        icon="fa-solid fa-building"
                                        label="Organizations"
                                    />
                                @endif
                                @if($appPermissions->contains('students.manage'))
                                    <x-layouts.sidebar-link
                                        :route="route('admin.students.index')"
                                        pattern="admin.students.*"
                                        icon="fa-solid fa-graduation-cap"
                                        label="Students"
                                    />
                                @endif
                            @endif

                            <li class="nav-header">Account</li>
                            <x-layouts.sidebar-link
                                :route="route('profile.edit')"
                                pattern="profile.*"
                                icon="fa-regular fa-user"
                                label="My profile"
                            />
                        @endif
                    </ul>
                </nav>
            </div>
        </aside>

        <div class="content-wrapper">
            <section class="content-header pl-page-header">
                <div class="container-fluid">
                    <div class="row align-items-end">
                        <div class="col-lg-8">
                            <div class="pl-page-eyebrow">PractiLink workspace</div>
                            <h1>@yield('page_title', 'Dashboard')</h1>
                            @hasSection('page_description')
                                <p>@yield('page_description')</p>
                            @endif
                        </div>
                        @hasSection('page_actions')
                            <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right pl-page-actions">
                                @yield('page_actions')
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <main id="main-content" class="content pb-4" tabindex="-1">
                <div class="container-fluid">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show pl-flash-message" role="status" aria-live="polite">
                            <i class="fas fa-check-circle mr-2" aria-hidden="true"></i>{{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Dismiss notification">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>

        <footer class="main-footer pl-footer">
            <strong>PractiLink</strong>
            <span>&copy; {{ now()->year }}</span>
            <span class="float-right d-none d-sm-inline">Practical training, connected.</span>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js"></script>
    @vite('resources/js/app.js')
    @stack('scripts')
</body>

</html>
