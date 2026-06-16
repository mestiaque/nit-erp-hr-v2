@extends('printMaster2')

@section('title', ($categoryLabel ?? 'Bonus') . ' Bonus Sheet')

@push('css')
<style>
.report-head { text-align:center; margin-bottom:10px; }
.report-head h3 { margin:0 0 2px; font-size:15px; }
.report-head p  { margin:0; font-size:11px; }
.sub-title  { font-size:12px; font-weight:700; margin:8px 0 4px; }
.dept-title { font-size:11px; font-weight:700; background:#dde6f0; padding:3px 6px; margin:10px 0 2px; }
.t { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:10px; }
.t th, .t td { border:1px solid #555; padding:3px 5px; }
.t th { background:#eef1d4; text-align:center; }
.tc { text-align:center; }
.tr { text-align:right; }
.summary-row td { background:#e8f5e9; font-weight:700; }
.photo-cell img { max-width:30px; max-height:35px; }
</style>
@endpush

@section('contents')
@php
    $company = general()->title ?? 'Company Name';
    $address = general()->address_one ?? '';
    $fmt     = fn($v) => number_format((float)$v, 2);
    $byDept  = $employees->groupBy('department_id');
@endphp

<div class="report-head">
    <h3>{{ $company }}</h3>
    <p>{{ $address }}</p>
</div>

<div class="sub-title">
    {{ $categoryLabel }} Bonus Sheet
    @if($bonusTitle) - {{ $bonusTitle->title }} @endif
    (Up To {{ $upToDateLabel }})
</div>

@forelse($byDept as $deptId => $deptEmps)
    <div class="dept-title">Department: {{ $departmentMap->get($deptId, 'N/A') }}</div>

    <table class="t">
        <thead>
            <tr>
                <th>SL</th>
                @if($withPicture)<th>Photo</th>@endif
                <th>Emp. ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Section</th>
                <th>Sub Section</th>
                <th>Designation</th>
                <th>Join Date</th>
                <th>Job Age</th>
                @if($hasPctPolicy)
                    <th>Gross Salary</th>
                    <th>Basic Salary</th>
                    <th>Present (%)</th>
                @endif
                <th>Stamp</th>
                <th>Bonus Amount</th>
                <th>Signature &amp; Stamp</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sl = 1;
                $totalBonus = 0;
                $totalColspan = ($withPicture ? 11 : 10) + ($hasPctPolicy ? 3 : 0);
            @endphp
            @foreach($deptEmps as $employee)
                @php
                    $bd      = $bonusData[$employee->id] ?? [];
                    $gross   = $bd['gross'] ?? 0;
                    $basic   = $bd['basic'] ?? 0;
                    $percent = $bd['percent'] ?? 0;
                    $bonus   = $bd['bonus'] ?? 0;
                    $jobAge  = $bd['job_age'] ?? 'N/A';
                    $totalBonus += $bonus;

                    $employeeDataFn = \App\Services\HrOptionsService::getOptionsForEmployee();
                    $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
                @endphp
                <tr>
                    {{-- @dd($employeeData) --}}
                    <td class="tc">{{ $sl++ }}</td>
                    @if($withPicture)
                        <td class="tc photo-cell">
                            @if($employeeData['employee_photo'])
                                <img src="{{ asset($employeeData['employee_photo']) }}" alt="">
                            @else
                                -
                            @endif
                        </td>
                    @endif
                    <td>{{ $employee->employee_id }}</td>
                    <td>{{ $employeeData['employee_name'] }}</td>
                    <td>{{ $employeeData['department'] }}</td>
                    <td>{{ $employeeData['section'] }}</td>
                    <td>{{ $employeeData['sub_section'] }}</td>
                    <td>{{ $employeeData['designation'] }}</td>
                    <td>{{ optional($employee->joining_date)->format('d-M-Y') ?? 'N/A' }}</td>
                    <td class="tc">{{ $jobAge }}</td>
                    @if($hasPctPolicy)
                        <td class="tr">{{ $fmt($gross) }}</td>
                        <td class="tr">{{ $fmt($basic) }}</td>
                        <td class="tc">{{ $percent }}%</td>
                    @endif
                    <td></td>
                    <td class="tr">{{ $fmt($bonus) }}</td>
                    <td></td>
                </tr>
            @endforeach
            <tr class="summary-row">
                <td colspan="{{ $totalColspan }}" class="tr">Total:</td>
                <td class="tr">{{ $fmt($totalBonus) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
@empty
    <p>No employees found.</p>
@endforelse

@endsection
