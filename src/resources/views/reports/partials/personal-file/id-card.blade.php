@php
    $factory = general()->company_s_code;
@endphp

@if($factory == 'SFL')
    @include('hr::reports.partials.personal-file.id-card-sfl', ['employee' => $employee, 'request' => $request])
@elseif($factory == 'ANR')
    @include('hr::reports.partials.personal-file.id-card-anr', ['employee' => $employee, 'request' => $request])
@else
    @include('hr::reports.partials.personal-file.id-card-anr', ['employee' => $employee, 'request' => $request])
@endif