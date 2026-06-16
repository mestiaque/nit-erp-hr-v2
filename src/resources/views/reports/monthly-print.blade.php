@extends('printMaster2')

@section('title', $reportTitle . ' - ' . $reportTypeLabel)

@push('css')
<style>
    .head {
        text-align: center;
        margin-bottom: 12px;
    }
    .head h3 {
        margin: 0 0 4px;
    }
    .sub {
        font-size: 12px;
        margin-bottom: 10px;
    }
    .table-report {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
    }
    .table-report th,
    .table-report td {
        border: 1px solid #222;
        padding: 4px 6px;
        font-size: 11px;
        vertical-align: top;
    }
    .table-report th {
        background: #eef1d4;
        text-align: center;
    }
    .text-right {
        text-align: right;
    }
    .text-center {
        text-align: center;
    }
    .summary-row td {
        background: #d8f3c8;
        font-weight: 700;
    }
    .flash {
        margin: 6px 0 12px;
        padding: 6px 10px;
        border: 1px solid transparent;
        font-size: 12px;
    }
    .flash-success {
        background: #e8f6e8;
        border-color: #9bd19b;
        color: #1f6b1f;
    }
    .flash-error {
        background: #fdeaea;
        border-color: #e0a2a2;
        color: #8b1f1f;
    }
</style>
@endpush

@section('contents')
@php
    $fromLabel = $request->filled('from') ? \Illuminate\Support\Carbon::parse($request->from)->format('d-M-Y') : 'Start';
    $toLabel = $request->filled('to') ? \Illuminate\Support\Carbon::parse($request->to)->format('d-M-Y') : 'Today';
    $fmtMoney = fn ($value) => number_format((float) $value, 2);
@endphp

<div class="head">
    <h3>{{ general()->title ?? 'Company Name' }}</h3>
    <div>{{ general()->address_one ?? data_get(general(), 'address') }}</div>
</div>

@if(session('success'))
    <div class="flash flash-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash flash-error">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="flash flash-error">{{ $errors->first() }}</div>
@endif

@if($reportType === 'recruitment')
    <div class="sub"><strong>Employee Recruitment Report-{{ $fromLabel }} To {{ $toLabel }}</strong></div>
    <table class="table-report">
        <thead>
            <tr>
                <th>SL</th>
                <th>Emp. ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Section</th>
                <th>Join Date</th>
                <th>Contact</th>
                <th>Classification</th>
                <th>Designation</th>
                <th>Grade</th>
                <th>Gross Salary(TK)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row['employee_id'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td>{{ $row['section'] }}</td>
                    <td>{{ $row['join_date'] }}</td>
                    <td>{{ $row['contact'] }}</td>
                    <td>{{ $row['classification'] }}</td>
                    <td>{{ $row['designation'] }}</td>
                    <td>{{ $row['grade'] }}</td>
                    <td class="text-right">{{ $fmtMoney($row['gross_salary']) }}</td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center">No data found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="sub"><strong>Employee Recruitment Summary- {{ $fromLabel }} To {{ $toLabel }}</strong></div>
    <table class="table-report">
        <thead>
            <tr>
                <th>SL</th>
                <th>Department</th>
                <th>Section</th>
                <th>Classification</th>
                <th>Designation</th>
                <th>Total Employees</th>
                <th>Total Gross Salary(TK)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['summary_rows'] as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td>{{ $row['section'] }}</td>
                    <td>{{ $row['classification'] }}</td>
                    <td>{{ $row['designation'] }}</td>
                    <td class="text-center">{{ $row['total_employees'] }}</td>
                    <td class="text-right">{{ $fmtMoney($row['total_gross_salary']) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">No data found.</td></tr>
            @endforelse
        </tbody>
    </table>
@elseif($reportType === 'migration')
    <div class="sub"><strong>Employee Migration Report-{{ $fromLabel }} To {{ $toLabel }}</strong></div>
    <table class="table-report">
        <thead>
            <tr>
                <th>SL</th>
                <th>Emp. ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Section</th>
                <th>Designation</th>
                <th>Migration Type</th>
                <th>Migration Date</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row['employee_id'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td>{{ $row['section'] }}</td>
                    <td>{{ $row['designation'] }}</td>
                    <td>{{ $row['migration_type'] }}</td>
                    <td>{{ $row['migration_date'] }}</td>
                    <td>{{ $row['remarks'] }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center">No data found.</td></tr>
            @endforelse
        </tbody>
    </table>
@elseif($reportType === 'long-absent')
    <div class="sub"><strong>Long absent Report ({{ $fromLabel }} To {{ $toLabel }})</strong></div>
    <table class="table-report">
        <thead>
            <tr>
                <th>SI</th>
                <th>Emp. ID</th>
                <th>Name</th>
                <th>DOJ</th>
                <th>Designation</th>
                <th>Department</th>
                <th>Section</th>
                <th>Absent Days</th>
                <th>Absent Date</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row['employee_id'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['doj'] }}</td>
                    <td>{{ $row['designation'] }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td>{{ $row['section'] }}</td>
                    <td class="text-center">{{ $row['absent_days'] }}</td>
                    <td>{{ $row['absent_date'] }}</td>
                    <td>{{ $row['remarks'] }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center">No data found.</td></tr>
            @endforelse
        </tbody>
    </table>
@elseif($reportType === 'increment')
    <div class="sub"><strong>Salary Increment ({{ $fromLabel }} To {{ $toLabel }})</strong></div>
    <form method="post" action="{{ route('hr-center.reports.monthly.lock-increment') }}" class="sub" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        @csrf
        <input type="hidden" name="from" value="{{ $request->from }}">
        <input type="hidden" name="to" value="{{ $request->to }}">
        <input type="hidden" name="classification" value="{{ $request->classification }}">
        <input type="hidden" name="department" value="{{ $request->department }}">
        <input type="hidden" name="section" value="{{ $request->section }}">
        <input type="hidden" name="sub_section" value="{{ $request->sub_section }}">
        <input type="hidden" name="working_place" value="{{ $request->working_place }}">
        <input type="hidden" name="salary_type" value="{{ $request->salary_type }}">
        <input type="hidden" name="designation" value="{{ $request->designation }}">
        <input type="hidden" name="line_number" value="{{ $request->line_number }}">
        <input type="hidden" name="report_type" value="increment">
        <input type="hidden" name="increment_percent" value="{{ $request->increment_percent }}">

        <label for="effective_date"><strong>Effective Date</strong></label>
        <input id="effective_date" type="date" name="effective_date" value="{{ $request->effective_date }}" required style="height:30px;">
        <button type="submit" style="height:30px;padding:0 10px;">Lock Increment</button>
    </form>
    <table class="table-report">
        <thead>
            <tr>
                <th>SL</th>
                <th>Emp. ID</th>
                <th>Name</th>
                <th>Service Length</th>
                <th>Department</th>
                <th>Section</th>
                <th>Sub-Section</th>
                <th>Designation</th>
                <th>Grade</th>
                <th>Classification</th>
                <th>Line/ Block</th>
                <th>Join Date</th>
                <th>Last Inc Date</th>
                <th>Last Inc Value</th>
                <th>Gross Salary</th>
                <th>Inc (%)</th>
                <th>Inc Value</th>
                <th>Final Gross</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row['employee_id'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['service_length'] }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td>{{ $row['section'] }}</td>
                    <td>{{ $row['sub_section'] }}</td>
                    <td>{{ $row['designation'] }}</td>
                    <td>{{ $row['grade'] }}</td>
                    <td>{{ $row['classification'] }}</td>
                    <td>{{ $row['line_block'] }}</td>
                    <td>{{ $row['join_date'] }}</td>
                    <td>{{ $row['last_inc_date'] }}</td>
                    <td class="text-right">{{ $fmtMoney($row['last_inc_value']) }}</td>
                    <td class="text-right">{{ $fmtMoney($row['gross_salary']) }}</td>
                    <td class="text-right">{{ $fmtMoney($row['inc_percent']) }}</td>
                    <td class="text-right">{{ $fmtMoney($row['inc_value']) }}</td>
                    <td class="text-right">{{ $fmtMoney($row['final_gross']) }}</td>
                </tr>
            @empty
                <tr><td colspan="18" class="text-center">No data found.</td></tr>
            @endforelse
        </tbody>
    </table>
@else
    <div class="sub"><strong>Salary Increment Report({{ $fromLabel }} To {{ $toLabel }})</strong></div>
    <table class="table-report">
        <thead>
            <tr>
                <th>SL</th>
                <th>Emp. ID</th>
                <th>Name</th>
                <th>Service Length</th>
                <th>Department</th>
                <th>Section</th>
                <th>Sub-Section</th>
                <th>Designation</th>
                <th>Grade</th>
                <th>Classification</th>
                <th>Line/ Block</th>
                <th>Join Date</th>
                <th>Last Inc Date</th>
                <th>Last Inc Value</th>
                <th>Gross Salary</th>
                <th>Inc (%)</th>
                <th>Inc Value</th>
                <th>Final Gross</th>
                <th>Effective Date</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rows'] as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row['employee_id'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['service_length'] }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td>{{ $row['section'] }}</td>
                    <td>{{ $row['sub_section'] }}</td>
                    <td>{{ $row['designation'] }}</td>
                    <td>{{ $row['grade'] }}</td>
                    <td>{{ $row['classification'] }}</td>
                    <td>{{ $row['line_block'] }}</td>
                    <td>{{ $row['join_date'] }}</td>
                    <td>{{ $row['last_inc_date'] }}</td>
                    <td class="text-right">{{ $fmtMoney($row['last_inc_value']) }}</td>
                    <td class="text-right">{{ $fmtMoney($row['gross_salary']) }}</td>
                    <td class="text-right">{{ $fmtMoney($row['inc_percent']) }}</td>
                    <td class="text-right">{{ $fmtMoney($row['inc_value']) }}</td>
                    <td class="text-right">{{ $fmtMoney($row['final_gross']) }}</td>
                    <td>{{ $row['effective_date'] }}</td>
                    <td>{{ $row['remarks'] }}</td>
                </tr>
            @empty
                <tr><td colspan="20" class="text-center">No data found.</td></tr>
            @endforelse
            <tr class="summary-row">
                <td colspan="15">Grand Total</td>
                <td class="text-right">Employee: {{ $data['summary']['employee_count'] ?? 0 }}</td>
                <td class="text-right">{{ $fmtMoney($data['summary']['total_increment_value'] ?? 0) }}</td>
                <td class="text-right">{{ $fmtMoney($data['summary']['total_final_gross'] ?? 0) }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
@endif
@endsection
