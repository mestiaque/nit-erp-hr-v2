@extends('admin.layouts.app')

@php
    $bonusCategory = $request->bonus_category ?? 'fixed';
    $isFixed = $bonusCategory === 'fixed';
    $bonusCategoryLabel = $bonusCategories[$bonusCategory] ?? ucfirst($bonusCategory);
    $bonusPageTitle = $bonusCategoryLabel . ' Bonus Sheet';
@endphp

@section('title')
<title>{{ $bonusPageTitle }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $bonusPageTitle }}</h4>
            <a href="{{ route('hr-center.reports.index') }}" class="btn btn-light btn-sm">Back</a>
        </div>
        <div class="card-body">
@php
    // Use central HR options service for all lookups
    $hrOptions = \ME\Hr\Services\HrOptionsService::getOptions();
    $departmentMap = collect($hrOptions['departments'])->pluck('name', 'id');
    $sectionMap = collect($hrOptions['sections'])->pluck('name', 'id');
    $subSectionMap = collect($hrOptions['subSections'])->pluck('name', 'id');
    $designationMap = collect($hrOptions['designations'])->pluck('name', 'id');
@endphp

            <form method="get" action="{{ route('hr-center.reports.bonus-sheet.category', $bonusCategory) }}">
                <div class="row">

                    {{-- Fixed: up to date --}}
                    <div class="col-md-3 mb-3 bonus-fixed-field" style="{{ $isFixed ? '' : 'display:none;' }}">
                        <label class="mb-1">Up To Date</label>
                        <input type="date" name="up_to_date" class="form-control form-control-sm" value="{{ $request->up_to_date ?? date('Y-m-d') }}">
                    </div>

                    {{-- Production: from/to --}}
                    <div class="col-md-3 mb-3 bonus-production-field" style="{{ $isFixed ? 'display:none;' : '' }}">
                        <label class="mb-1">From</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ $request->from }}">
                    </div>
                    <div class="col-md-3 mb-3 bonus-production-field" style="{{ $isFixed ? 'display:none;' : '' }}">
                        <label class="mb-1">To</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ $request->to }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Bonus Title</label>
                        <select name="bonus_title" class="form-control form-control-sm">
                            <option value="">-- Select --</option>
                            @foreach($bonusTitles as $bt)
                                <option value="{{ $bt->id }}" @selected((string)$request->bonus_title === (string)$bt->id)>{{ $bt->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Employee ID(s) <small class="text-muted">(use , for multiple)</small></label>
                        <input type="text" name="employee_ids" class="form-control form-control-sm" value="{{ $request->employee_ids }}" placeholder="B00144,B00145">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Block / Line</label>
                        <select name="line_number[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['lines'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->line_number))>{{ $item->name }}{{ $item->slug ? ' - '.$item->slug : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Designation</label>
                        <select name="designation[]" class="form-control form-control-sm select2" multiple>
                            @foreach(\ME\Hr\Models\HrDesignation::orderBy('name')->get(['id','name']) as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->designation))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3 bonus-production-field" style="{{ $isFixed ? 'display:none;' : '' }}">
                        <label class="mb-1">Group By</label>
                        <select name="group_by" class="form-control form-control-sm">
                            <option value="department" @selected($request->group_by === 'department')>Department</option>
                            <option value="section" @selected($request->group_by === 'section')>Section</option>
                            <option value="designation" @selected($request->group_by === 'designation')>Designation</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Language</label>
                        <select name="language" class="form-control form-control-sm">
                            <option value="en" @selected(($request->language ?? 'en') === 'en')>English</option>
                            <option value="bn" @selected($request->language === 'bn')>বাংলা</option>
                        </select>
                    </div>

                    <input type="hidden" name="report_type" value="{{ $request->report_type ?? 'details' }}">

                    <div class="col-md-3 mb-3">
                        <label class="mb-1 d-block">With Picture</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" name="with_picture" value="1"
                                   id="withPicture" @checked($request->boolean('with_picture'))>
                            <label class="form-check-label" for="withPicture">Show Photo</label>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1 d-block">Lock Bonus</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" name="lock_bonus" value="1"
                                   id="lockBonus" @checked($request->boolean('lock_bonus'))>
                            <label class="form-check-label" for="lockBonus">Apply Lock</label>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Group By</label>
                        <select name="group_by" class="form-control form-control-sm">
                            @foreach($groupByOptions as $key => $label)
                                <option value="{{ $key }}" @selected(($request->group_by ?? 'department') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <div class="w-100 d-flex gap-2">
                            <a href="{{ route('hr-center.reports.bonus-sheet.category', $bonusCategory) }}" class="btn btn-light btn-sm w-50 mr-2"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                            <button type="submit" name="print" value="1" formtarget="_blank" class="btn btn-primary btn-sm w-50">
                                <i class="fa-solid fa-print"></i> Print
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'All',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush


