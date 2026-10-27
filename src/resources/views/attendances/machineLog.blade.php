@extends(adminTheme().'layouts.app')

@section('title')
<title>{{ websiteTitle('Machine Attendance Log') }}</title>
@endsection

@push('css')
<style>
    .badge-finger  { background:#e0f4f8; color:#155e75; }
    .badge-face    { background:#efe9fb; color:#5b21b6; }
    .badge-manual  { background:#eef1f5; color:#334155; }
    .badge-matched { background:#e3f6ea; color:#166534; }
    .badge-unmatched { background:#fde8e8; color:#b91c1c; }
</style>
@endpush

@section('contents')
@include(adminTheme().'alerts')

<div class="flex-grow-1">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0"><i class="fa fa-list-alt mr-2 text-info"></i> Machine Attendance Log</h5>
            <a href="{{ route('hr-center.attendances.index') }}" class="btn btn-sm ui-act ui-act-nav" title="Attendance">
                <i class="fa fa-calendar-check"></i> Attendance
            </a>
        </div>

        <div class="card-body pb-1">
            <!-- Filters -->
            <form method="GET" action="" class="filter-bar">
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control"
                               placeholder="Employee ID / Device SN"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control"
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control"
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="verify_method" class="form-control">
                            <option value="">All Verify Method</option>
                            @foreach($verifyMethods as $vm)
                                <option value="{{ $vm }}" @selected(request('verify_method') == $vm)>{{ ucfirst($vm) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="source" class="form-control">
                            <option value="">All Source</option>
                            @foreach($sources as $src)
                                <option value="{{ $src }}" @selected(request('source') == $src)>{{ strtoupper($src) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-sm ui-act ui-act-filter" title="Filter"><i class="fa-solid fa-filter"></i> Filter</button>
                        <a href="{{ route('hr-center.machine-logs.index') }}" class="btn btn-sm ui-act ui-act-reset" title="Reset"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                    </div>
                </div>
            </form>

            <!-- Summary -->
            <div class="d-flex align-items-center mb-2" style="font-size:.85rem;color:#666;">
                <span>Total: <strong>{{ $logs->total() }}</strong> logs</span>
                <span class="mx-3">|</span>
                <span>Match: <strong class="text-success">{{ $logs->filter(fn($l) => isset($employees[$l->employee_id]))->count() }}</strong></span>
                <span class="mx-3">|</span>
                <span>Unmatched: <strong class="text-danger">{{ $logs->filter(fn($l) => !isset($employees[$l->employee_id]))->count() }}</strong></span>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered log-table">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Log Time</th>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Device SN</th>
                            <th>Verify Method</th>
                            <th>Source</th>
                            <th>Received At</th>
                            <th>Match</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php $emp = $employees[$log->employee_id] ?? null; @endphp
                            <tr>
                                <td class="text-muted">{{ $logs->firstItem() + $loop->index }}</td>
                                <td>
                                    @if($log->log_time)
                                        <span>{{ \Carbon\Carbon::parse($log->log_time)->format('d M Y') }}</span><br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($log->log_time)->format('h:i:s A') }}</small>
                                    @else —
                                    @endif
                                </td>
                                <td><code>{{ $log->employee_id ?? '—' }}</code></td>
                                <td>
                                    @if($emp)
                                        {{ $emp->name }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $log->device_sn ?? '—' }}</small></td>
                                <td>
                                    @php $vm = strtolower($log->type_name ?? ''); @endphp
                                    @if(str_contains($vm, 'finger'))
                                        <span class="badge badge-finger"><i class="fa fa-hand-pointer mr-1"></i>{{ $log->type_name }}</span>
                                    @elseif(str_contains($vm, 'face'))
                                        <span class="badge badge-face"><i class="fa fa-user-circle mr-1"></i>{{ $log->type_name }}</span>
                                    @elseif($log->type_name)
                                        <span class="badge badge-manual">{{ $log->type_name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td><small>{{ strtoupper($log->source ?? '—') }}</small></td>
                                <td>
                                    @if($log->received_at)
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($log->received_at)->format('d M Y') }}</small> <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($log->received_at)->format('h:i:s A') }}</small>
                                    @else
                                        <small class="text-muted">—</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($emp)
                                        <span class="badge badge-matched"><i class="fa fa-check"></i></span>
                                    @else
                                        <span class="badge badge-unmatched"><i class="fa fa-times"></i></span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap">
                <small class="text-muted">
                    Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}
                </small>
                {{ $logs->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
