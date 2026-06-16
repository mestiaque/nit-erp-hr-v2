@php
    $resignInfo = $resignInfo ?? [
        'resign_date' => data_get($employee, 'exited_at'),
        'remarks' => data_get($employee, 'remarks'),
    ];
@endphp

<h4 class="section-title">{{ $label('নিয়োগ সংক্রান্ত অতিরিক্ত নোট', 'Employment Notes') }}</h4>
<table class="two-col">
    <tr><th>{{ $label('অবস্থা', 'Status') }}</th><td>{{ $employee->status ?? 'N/A' }}</td></tr>
    <tr><th>{{ $label('যোগদানের তারিখ', 'Joining Date') }}</th><td>{{ $fmtDate($employee->joining_date) }}</td></tr>
    <tr><th>{{ $label('প্রবেশন শেষ', 'Probation End') }}</th><td>{{ $fmtDate(data_get($employee, 'probation_end_date')) }}</td></tr>
    <tr><th>{{ $label('চাকরির ধরন', 'Employment Type') }}</th><td>{{ data_get($employee, 'employment_type', 'N/A') }}</td></tr>
    <tr><th>{{ $label('রেজিগনেশন/রিলিজ তারিখ', 'Resignation/Release Date') }}</th><td>{{ $fmtDate(data_get($resignInfo, 'resign_date')) }}</td></tr>
    <tr><th>{{ $label('মন্তব্য', 'Remarks') }}</th><td>{{ data_get($resignInfo, 'remarks', data_get($employee, 'remarks', 'N/A')) }}</td></tr>
</table>
