@switch($reportType)
    @case('id-card')
        @include('hr::reports.partials.personal-file.id-card')
        @break

    @case('application')
        @include('hr::reports.partials.personal-file.application')
        @break

    @case('appointment-letter')
        @include('hr::reports.partials.personal-file.appointment-letter')
        @break

    @case('employment-letter')
        @include('hr::reports.partials.personal-file.employment-letter')
        @break

    @case('nominee')
        @include('hr::reports.partials.personal-file.nominee')
        @break

    @case('age-verification')
        @include('hr::reports.partials.personal-file.age-verification')
        @break

    @case('job-responsibility')
        @include('hr::reports.partials.personal-file.job-responsibility')
        @break

    @case('appraisal-letter')
        @include('hr::reports.partials.personal-file.appraisal-letter')
        @break

    @case('joining-letter')
        @include('hr::reports.partials.personal-file.joining-letter')
        @break

    @case('increment-letter')
        @include('hr::reports.partials.personal-file.increment-letter')
        @break
@endswitch
