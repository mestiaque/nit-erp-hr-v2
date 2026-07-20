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
                    <div class="col-md-2">
                        <label class="mb-1">Employee</label>
                        <input type="text" name="employee" class="form-control" placeholder="Name/ID/Mobile" value="{{ request('employee') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">Department</label>
                        <select name="department[]" class="form-control select2" multiple>
                            <option value="">All</option>
                            @foreach($options['departments'] as $dept)
                                <option value="{{ $dept->id }}" {{ in_array((string)$dept->id, (array) request('department')) ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">Section</label>
                        <select name="section[]" class="form-control select2" multiple>
                            <option value="">All</option>
                            @foreach($options['sections'] as $sec)
                                <option value="{{ $sec->id }}" {{ in_array((string)$sec->id, (array) request('section')) ? 'selected' : '' }}>{{ $sec->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">Sub-Section</label>
                        <select name="sub_section[]" class="form-control select2" multiple>
                            <option value="">All</option>
                            @foreach($options['subSections'] as $sub)
                                <option value="{{ $sub->id }}" {{ in_array((string)$sub->id, (array) request('sub_section')) ? 'selected' : '' }}>{{ $sub->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">Classification</label>
                        <select name="classification[]" class="form-control select2" multiple>
                            <option value="">All</option>
                            @foreach($options['classifications'] as $cls)
                                <option value="{{ $cls->id }}" {{ in_array((string)$cls->id, (array) request('classification')) ? 'selected' : '' }}>{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">Designation</label>
                        <select name="designation[]" class="form-control select2" multiple>
                            <option value="">All</option>
                            @foreach($options['designations'] as $des)
                                <option value="{{ $des->id }}" {{ in_array((string)$des->id, (array) request('designation')) ? 'selected' : '' }}>{{ $des->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach(['Present','Absent','Late'] as $s)
                                <option value="{{ $s }}" @if(request('status')==$s) selected @endif>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from', $dateFrom ?? '') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to', $dateTo ?? '') }}">
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <a href="{{ route('hr-center.attendances.index') }}" class="btn btn-secondary btn-sm">Reset</a>
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
                                    @if($shift) &bull; <i class="fa fa-clock"></i> {{ $shift->name }} @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-muted" style="font-size:12px;">{{ $rows->count() }} day(s)</div>
                    </div>

                    {{-- Attendance rows form --}}
                    <form method="POST" action="{{ route('hr-center.attendances.bulk-update', $empId) }}" class="js-att-form" data-shift-in="{{ $shift ? \Carbon\Carbon::parse($shift->start_time)->format('H:i') : '' }}" data-shift-out="{{ $shift ? \Carbon\Carbon::parse($shift->end_time)->format('H:i') : '' }}">
                        @csrf
                        {{-- Preserve filter params --}}
                        @foreach(['employee','status','date_from','date_to','department','section','sub_section','classification','designation'] as $p)
                            @if(request($p))
                                <input type="hidden" name="{{ $p }}" value="{{ request($p) }}">
                            @endif
                        @endforeach

                        <div style="overflow-x:auto;">
                            <table class="att-table">
                                <thead>
                                    <tr>
                                        <th style="text-align:center;">
                                            <input type="checkbox" class="js-att-check-all" @disabled(!$shift) title="Check/uncheck all">
                                        </th>
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
                                            $isWeekend = strtolower($dayName) === strtolower($emp->weekend ?? 'friday');

                                            $st = $row['status'];
                                            $badgeClass = match(true) {
                                                $st === 'Present'                  => 'badge-present',
                                                $st === 'Absent'                   => 'badge-absent',
                                                $st === 'Weekend'                  => 'badge-default',
                                                $st === 'Holiday'                  => 'badge-early',
                                                str_contains($st, 'Late') && str_contains($st, 'Punch') => 'badge-punch',
                                                str_contains($st, 'Late') && str_contains($st, 'Early') => 'badge-default',
                                                str_contains($st, 'Late')          => 'badge-late',
                                                str_contains($st, 'Punch')         => 'badge-punch',
                                                str_contains($st, 'Early')         => 'badge-early',
                                                default                            => 'badge-default',
                                            };
                                            $hasTime = !empty($row['attendance']->in_time) || !empty($row['attendance']->out_time);
                                        @endphp
                                        <tr>
                                            <td style="text-align:center;">
                                                <input type="checkbox" class="js-att-row-check" @disabled(!$shift || $hasTime)>
                                            </td>
                                            <td>{{ $row['date'] }}</td>
                                            <td class="{{ $isWeekend ? 'day-weekend' : '' }}">{{ $dayName }}</td>
                                            <td><span class="status-badge {{ $badgeClass }}">{{ $st }}</span></td>
                                            <td>
                                                <input type="hidden" name="rows[{{ $i }}][date]" value="{{ $row['date'] }}">
                                                <input type="time" class="js-att-in" name="rows[{{ $i }}][in_time]" value="{{ $row['attendance']->in_time ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="time" class="js-att-out" name="rows[{{ $i }}][out_time]" value="{{ $row['attendance']->out_time ?? '' }}">
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

@push('js')
<script>
    (function () {
        function fillRow(row, checked) {
            var inInput  = row.querySelector('.js-att-in');
            var outInput = row.querySelector('.js-att-out');
            var form     = row.closest('.js-att-form');
            if (!form) return;
            var shiftIn  = form.getAttribute('data-shift-in');
            var shiftOut = form.getAttribute('data-shift-out');

            if (checked) {
                if (inInput)  inInput.value  = shiftIn;
                if (outInput) outInput.value = shiftOut;
            } else {
                if (inInput)  inInput.value  = '';
                if (outInput) outInput.value = '';
            }
        }

        document.querySelectorAll('.js-att-row-check').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                fillRow(checkbox.closest('tr'), checkbox.checked);
            });
        });

        document.querySelectorAll('.js-att-check-all').forEach(function (checkAll) {
            checkAll.addEventListener('change', function () {
                var table = checkAll.closest('table');
                if (!table) return;
                table.querySelectorAll('.js-att-row-check:not(:disabled)').forEach(function (checkbox) {
                    checkbox.checked = checkAll.checked;
                    fillRow(checkbox.closest('tr'), checkbox.checked);
                });
            });
        });

        $('.select2').select2({
            placeholder: 'All',
            allowClear: true,
            width: '100%'
        });
    })();
</script>
@endpush
@endsection
