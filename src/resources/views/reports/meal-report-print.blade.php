@extends('printMaster2')

@section('title', $mealTypeLabel . ' Report - ' . $dateLabel)

@push('css')
<style>
.report-head { text-align:center; margin-bottom:10px; }
.report-head h3 { margin:0 0 2px; font-size:15px; }
.report-head p  { margin:0; font-size:11px; }
.sub-title  { font-size:12px; font-weight:700; margin:8px 0 4px; }
.section-title { font-size:11px; font-weight:700; background:#dde6f0; padding:3px 6px; margin:10px 0 2px; }
.t { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:10px; }
.t th, .t td { border:1px solid #555; padding:3px 5px; }
.t th { background:#eef1d4; text-align:center; }
.tc { text-align:center; }
.tr { text-align:right; }
.summary-row td { background:#e8f5e9; font-weight:700; }
</style>
@endpush

@section('contents')
@php
    $company = hr_factory('name') ?? 'Company Name';
    $address = hr_factory('address') ?? '';

    // Meal threshold mapping from shift config
    $mealMinutesKey = match($mealType) {
        'tiffin' => 'minimum_tiffin_hour',
        'dinner' => 'minimum_dinner_hour',
        'night'  => 'minimum_night_hour',
        default  => 'minimum_tiffin_hour',
    };

    // Determine if employee qualifies for meal
    $qualifies = function($employee, $att) use ($shifts, $mealType, $mealMinutesKey) {
        if (!$att || !$att->in_time) return false;
        $shift = $shifts->get($employee->shift_id);
        if (!$shift) return true; // no shift config = include
        $minHours = (float)($shift->{$mealMinutesKey} ?? 0);
        $workedMin = (int)($att->in_minutes ?? 0);
        return $minHours <= 0 || $workedMin >= ($minHours * 60);
    };

    $allowanceKey = match($mealType) {
        'tiffin' => 'tiffin_allowance',
        'dinner' => 'dinner_allowance',
        'night'  => 'night_allowance',
        default  => 'tiffin_allowance',
    };

    $mealAmount = function($employee, $eligible) use ($designationInfoMap, $allowanceKey) {
        if (!$eligible) return 0;
        $designation = $designationInfoMap->get($employee->designation_id);
        return (float) data_get($designation, $allowanceKey, 0);
    };
@endphp

<div class="report-head">
    @if(!blank(general()->logo()))
        <img src="{{ asset(general()->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
    @endif
    <h3>{{ $company }}</h3>
    <p>{{ $address }}</p>
</div>

<div class="sub-title">{{ $mealTypeLabel }} Report — {{ $dateLabel }}</div>

@php
    $bySection = $employees->groupBy('section_id');
@endphp

@if($reportType === 'details')
    @forelse($bySection as $sectionId => $sectionEmps)
        <div class="section-title">Section: {{ $sectionMap->get($sectionId, 'N/A') }}</div>

        <table class="t">
            <thead>
                <tr>
                    <th>SI</th>
                    <th>Emp. ID</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Sub-Section</th>
                    <th>Shift</th>
                    <th>In Time</th>
                    <th>Work Min</th>
                    <th>{{ $mealTypeLabel }} Amount</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @php $sl = 1; $totalCount = 0; $totalAmount = 0; @endphp
                @foreach($sectionEmps as $employee)
                    @php
                        $att = $attendanceMap->get($employee->id);
                        $eligible = $qualifies($employee, $att);
                        $amount = $mealAmount($employee, $eligible);
                        if ($eligible) $totalCount++;
                        $totalAmount += $amount;
                    @endphp
                    <tr>
                        <td class="tc">{{ $sl++ }}</td>
                        <td>{{ $employee->employee_id }}</td>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $designationMap->get($employee->designation_id, 'N/A') }}</td>
                        <td>{{ $subSectionMap->get($employee->hr_sub_section_id ?? $employee->sub_section_id ?? null, 'N/A') }}</td>
                        <td class="tc">{{ $shiftMap->get($employee->shift_id, '-') }}</td>
                        <td class="tc">{{ $att && $att->in_time ? \Carbon\Carbon::parse($att->in_time)->format('h:i A') : '-' }}</td>
                        <td class="tc">{{ $att ? (int)($att->in_minutes ?? 0) : '-' }}</td>
                        <td class="tc">{{ $eligible ? number_format($amount, 2) : '0.00' }}</td>
                        <td></td>
                    </tr>
                @endforeach
                <tr class="summary-row">
                    <td colspan="7" class="tr">Total {{ $mealTypeLabel }} Count:</td>
                    <td class="tc">{{ $totalCount }}</td>
                    <td class="tc">{{ number_format($totalAmount, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    @empty
        <p>No employees found.</p>
    @endforelse

@else {{-- summary --}}
    <table class="t">
        <thead>
            <tr>
                <th>SI</th>
                <th>Section</th>
                <th>Sub-Section</th>
                <th>Total Employees</th>
                <th>Present</th>
                <th>{{ $mealTypeLabel }} Count</th>
                <th>{{ $mealTypeLabel }} Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $sl = 1; @endphp
            @forelse($bySection as $sectionId => $sectionEmps)
                @php
                    $present = 0;
                    $mealCount = 0;
                    $subSectionGroups = $sectionEmps->groupBy(fn($e) => $e->hr_sub_section_id ?? $e->sub_section_id ?? 0);
                @endphp
                @foreach($subSectionGroups as $subId => $subEmps)
                    @php
                        $subPresent   = 0;
                        $subMealCount = 0;
                        $subMealAmount = 0;
                        foreach($subEmps as $emp) {
                            $att = $attendanceMap->get($emp->id);
                            if ($att && $att->in_time) {
                                $subPresent++;
                                if ($qualifies($emp, $att)) {
                                    $subMealCount++;
                                    $subMealAmount += $mealAmount($emp, true);
                                }
                            }
                        }
                        $present   += $subPresent;
                        $mealCount += $subMealCount;
                    @endphp
                    <tr>
                        <td class="tc">{{ $sl++ }}</td>
                        <td>{{ $sectionMap->get($sectionId, 'N/A') }}</td>
                        <td>{{ $subSectionMap->get($subId, 'N/A') }}</td>
                        <td class="tc">{{ $subEmps->count() }}</td>
                        <td class="tc">{{ $subPresent }}</td>
                        <td class="tc">{{ $subMealCount }}</td>
                        <td class="tc">{{ number_format($subMealAmount, 2) }}</td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="7" class="tc">No data.</td></tr>
            @endforelse
        </tbody>
    </table>
@endif

@endsection
