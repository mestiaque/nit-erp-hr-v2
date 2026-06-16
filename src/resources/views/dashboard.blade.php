@extends('admin.layouts.app')

@section('title')
<title>HR Center</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">

    {{-- Stats Row --}}
    <div class="row mb-4">
        <div class="col-sm-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                         style="width:50px;height:50px;background:#e8f4fd;flex-shrink:0;">
                        <i class="fas fa-users" style="color:#2196F3;font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="h4 mb-0 fw-bold">{{ $stats['totalEmployees'] ?? 0 }}</div>
                        <div class="text-muted small">Total Employees</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                         style="width:50px;height:50px;background:#e8f8f0;flex-shrink:0;">
                        <i class="fas fa-user-check" style="color:#4CAF50;font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="h4 mb-0 fw-bold">{{ $stats['presentToday'] ?? 0 }}</div>
                        <div class="text-muted small">Present Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                         style="width:50px;height:50px;background:#fdecea;flex-shrink:0;">
                        <i class="fas fa-user-times" style="color:#f44336;font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="h4 mb-0 fw-bold">{{ $stats['absentToday'] ?? 0 }}</div>
                        <div class="text-muted small">Absent Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                         style="width:50px;height:50px;background:#fff8e1;flex-shrink:0;">
                        <i class="fas fa-user-plus" style="color:#FF9800;font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="h4 mb-0 fw-bold">{{ $stats['newThisMonth'] ?? 0 }}</div>
                        <div class="text-muted small">New This Month</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">

            {{-- Department Breakdown --}}
            @if(($stats['departments'] ?? collect())->isNotEmpty())
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold">Employees by Department</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Department</th><th class="text-end">Employees</th></tr>
                        </thead>
                        <tbody>
                            @foreach($stats['departments'] as $dept)
                            <tr>
                                <td>{{ $dept->name }}</td>
                                <td class="text-end">
                                    <span class="badge bg-primary rounded-pill">{{ $dept->employees_count }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Basic Setup --}}
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold">Basic Setup</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($entities as $key => $entity)
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('hr-center.masters.index', $key) }}" class="btn btn-light border w-100 text-start">
                                    <strong>{{ $entity['title'] }}</strong><br>
                                    <small class="text-muted">Manage {{ strtolower($entity['title']) }}</small>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if(!empty($legacyLinks))
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold">Admin HR Setup</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($legacyLinks as $link)
                            <div class="col-md-6 mb-2">
                                <a href="{{ url($link['url']) }}" class="btn btn-outline-secondary w-100 text-start">
                                    <strong>{{ $link['title'] }}</strong><br>
                                    <small>{{ $link['description'] }}</small>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold">Quick Actions</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('hr-center.employees.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-users me-1"></i> Employees
                    </a>
                    <a href="{{ route('hr-center.masters.index', 'requisitions') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-file-alt me-1"></i> Requisitions
                    </a>
                    <a href="{{ route('hr-center.attendances.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-clock me-1"></i> Attendance
                    </a>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold">Reports</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($reports as $key => $label)
                            <li class="list-group-item px-3 py-2">
                                <a href="{{ route('hr-center.reports.show', $key) }}" class="text-decoration-none">{{ $label }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
