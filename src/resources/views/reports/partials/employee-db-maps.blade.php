@php
    /*
    |--------------------------------------------------------------
    | Employee Report — Shared Option Maps
    |--------------------------------------------------------------
    | Include this partial at the top of any employee print view
    | that iterates over a $employees collection directly.
    |
    | Provides:
    |   $classificationMap  — employee_type  id → name
    |   $departmentMap      — department_id  id → name
    |   $sectionMap         — section_id     id → name
    |   $subSectionMap      — sub_section_id id → object (access ->name)
    |   $designationMap     — designation_id id → name
    |   $workingPlaceMap    — working_place_id id → name
    |   $shiftMap           — shift_id       id → shift name
    |   $lineMap            — line_number    id → "name - slug"
    |   $gradeMap           — grade_lavel    id → grade name
    |   $fmtDate($value)    — format a date as d-m-Y or 'N/A'
    |   $fmtMoney($value)   — format a number with 2 decimal places
    |--------------------------------------------------------------
    */

    $classificationMap = collect($options['classifications'] ?? [])->pluck('name', 'id');
    $departmentMap     = collect($options['departments'] ?? [])->pluck('name', 'id');
    $sectionMap        = collect($options['sections'] ?? [])->pluck('name', 'id');
    $subSectionMap     = collect($options['subSections'] ?? [])->keyBy('id');
    $designationMap    = collect($options['designations'] ?? [])->pluck('name', 'id');
    $workingPlaceMap   = collect($options['workingPlaces'] ?? [])->pluck('name', 'id');
    $shiftMap          = collect($options['shifts'] ?? [])->pluck('name', 'id');
    $lineMap           = collect($options['lines'] ?? [])->mapWithKeys(
        fn ($row) => [$row->id => trim(($row->name ?? '') . (filled($row->slug ?? null) ? ' - ' . $row->slug : ''))]
    );

    // Grade names from package-provided options (kept empty when grade source is unavailable)
    $gradeMap = collect($options['grades'] ?? [])->pluck('name', 'id');

    // Format a date value → "d-m-Y" string, or 'N/A' if blank
    $fmtDate = function ($value) {
        if (blank($value)) {
            return 'N/A';
        }
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('d-m-Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    // Format a numeric value with 2 decimal places
    $fmtMoney = fn ($value) => number_format((float) $value, 2);
@endphp
