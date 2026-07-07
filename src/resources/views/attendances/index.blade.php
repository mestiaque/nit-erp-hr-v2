@extends(adminTheme().'layouts.app')

@section('title')
<title>{{ websiteTitle('Attendance List') }}</title>
@endsection

@push('css')
<style>
    .emp-card { border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 20px; overflow: hidden; }
    .emp-card-header { background: #f8f9fa; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #dee2e6; }
    .emp-info { display: flex; align-items: center; gap: 12px; }
    .emp-avatar { width: 40px; height: 40px; border-radius: 50%; background: #4a6fa5; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 15px; flex-shrink: 0; }
    .emp-name { font-weight: 600; font-size: 15px; margin-bottom: 1px; }
    .emp-meta { font-size: 12px; color: #6c757d; }
    .att-table { width: 100%; border-collapse: collapse; }
    .att-table th { background: #f1f3f5; font-size: 12px; font-weight: 600; color: #495057; padding: 8px 10px; border-bottom: 1px solid #dee2e6; text-align: left; }
    .att-table td { padding: 6px 10px; border-bottom: 1px solid #f1f3f5; font-size: 13px; vertical-align: middle; }
    .att-table tr:last-child td { border-bottom: none; }
    .att-table input[type="time"], .att-table input[type="text"] { height: 30px; padding: 2px 8px; font-size: 12px; border: 1px solid #ced4da; border-radius: 4px; width: 100%; min-width: 90px; }
    .badge-present    { background: #d4edda; color: #155724; }
    .badge-absent     { background: #f8d7da; color: #721c24; }
    .badge-late       { background: #fff3cd; color: #856404; }
    .badge-punch      { background: #fde8d8; color: #7d3a00; }
    .badge-early      { background: #d1ecf1; color: #0c5460; }
    .badge-default    { background: #e2e3e5; color: #383d41; }
    .status-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; }
    .day-weekend { color: #dc3545; font-weight: 600; }
    .save-row { padding: 10px 16px; background: #f8f9fa; border-top: 1px solid #dee2e6; display: flex; justify-content: flex-end; gap: 8px; align-items: center; }
    .save-msg { font-size: 12px; color: #28a745; display: none; }
</style>
@endpush

@section('contents')
<div class="flex-grow-1">
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Attendance List</h5>
            <a href="{{ route('hr-center.machine-logs.index') }}" class="btn btn-sm btn-outline-info">
                <i class="fa fa-list-alt mr-1"></i> Machine Log
            </a>
        </div>
        <div class="card-body">

            {{-- Filter --}}
            <form method="GET" action="" class="mb-4">
                <div class="row g-2">
                    <div class="col">
                        <input type="text" name="employee" class="form-control" placeholder="Employee Name/ID/Mobile" value="{{ request('employee') }}">
                    </div>
                    <div class="col">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            @foreach(['Present','Absent','Late','Punch Missing','Early Exit','Late and Early Exit','Late and Punch Missing'] as $s)
                                <option value="{{ $s }}" @if(request('status')==$s) selected @endif>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col">
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $dateFrom ?? '') }}">
                    </div>
                    <div class="col">
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $dateTo ?? '') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- Group rows by employee --}}
            @php
                $grouped = collect($attendanceList)->groupBy(fn($r) => $r['employee']->id);
            @endphp

            @forelse($grouped as $empId => $rows)
                @php
                    $emp   = $rows->first()['employee'];
                    $shift = $rows->first()['shift'];
                    $initials = collect(explode(' ', $emp->name))->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
                @endphp

                <div class="emp-card">
                    {{-- Card header --}}
                    <div class="emp-card-header">
                        <div class="emp-info">
                            <div class="emp-avatar">{{ $initials }}</div>
                            <div>
                                <div class="emp-name">{{ $emp->name }}</div>
                                <div class="emp-meta">
                                    {{ $emp->employee_id }}
                                    @if($emp->department) &bull; {{ $emp->department->name }} @endif
                                    @if($emp->section) &bull; {{ $emp->section->name }} @endif
                                    @if($shift) &bull; <i class="fa fa-clock-o"></i> {{ $shift->name }} @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-muted" style="font-size:12px;">{{ $rows->count() }} day(s)</div>
                    </div>

                    {{-- Attendance rows form --}}
                    <form method="POST" action="{{ route('hr-center.attendances.bulk-update', $empId) }}">
                        @csrf
                        {{-- Preserve filter params --}}
                        @foreach(['employee','status','date_from','date_to'] as $p)
                            @if(request($p))
                                <input type="hidden" name="{{ $p }}" value="{{ request($p) }}">
                            @endif
                        @endforeach

                        <div style="overflow-x:auto;">
                            <table class="att-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Status</th>
                                        <th>In Time</th>
                                        <th>Out Time</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $i => $row)
                                        @php
                                            $carbon  = \Carbon\Carbon::parse($row['date']);
                                            $dayName = $carbon->format('l');
                                            $isWeekend = in_array($dayName, ['Friday']);

                                            $st = $row['status'];
                                            $badgeClass = match(true) {
                                                $st === 'Present'                  => 'badge-present',
                                                $st === 'Absent'                   => 'badge-absent',
                                                str_contains($st, 'Late') && str_contains($st, 'Punch') => 'badge-punch',
                                                str_contains($st, 'Late') && str_contains($st, 'Early') => 'badge-default',
                                                str_contains($st, 'Late')          => 'badge-late',
                                                str_contains($st, 'Punch')         => 'badge-punch',
                                                str_contains($st, 'Early')         => 'badge-early',
                                                default                            => 'badge-default',
                                            };
                                        @endphp
                                        <tr>
                                            <td>{{ $row['date'] }}</td>
                                            <td class="{{ $isWeekend ? 'day-weekend' : '' }}">{{ $dayName }}</td>
                                            <td><span class="status-badge {{ $badgeClass }}">{{ $st }}</span></td>
                                            <td>
                                                <input type="hidden" name="rows[{{ $i }}][date]" value="{{ $row['date'] }}">
                                                <input type="time" name="rows[{{ $i }}][in_time]" value="{{ $row['attendance']->in_time ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="time" name="rows[{{ $i }}][out_time]" value="{{ $row['attendance']->out_time ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="text" name="rows[{{ $i }}][remarks]" value="{{ $row['attendance']->remarks ?? '' }}" placeholder="Remarks">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="save-row">
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="fa fa-save mr-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="text-center text-muted py-5">No attendance records found.</div>
            @endforelse

        </div>
    </div>
</div>
@endsection
