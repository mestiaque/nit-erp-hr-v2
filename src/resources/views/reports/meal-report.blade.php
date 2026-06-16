@extends('admin.layouts.app')

@section('title')
<title>{{ $reportTitle }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $reportTitle }}</h4>
            <a href="{{ route('hr-center.reports.index') }}" class="btn btn-light btn-sm">Back</a>
        </div>
        <div class="card-body">
            <form method="get" action="{{ route('hr-center.reports.show', $reportKey) }}">
                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Date</label>
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ $request->date ?? date('Y-m-d') }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Classification</label>
                        <select name="classification" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['classifications'] as $item)
                                <option value="{{ $item->id }}" @selected((string)$request->classification === (string)$item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Salary Type</label>
                        <select name="salary_type" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="fixed_rate" @selected($request->salary_type === 'fixed_rate')>Fixed Rate</option>
                            <option value="price_rate" @selected($request->salary_type === 'price_rate')>Price Rate</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Department</label>
                        <select name="department" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['departments'] as $item)
                                <option value="{{ $item->id }}" @selected((string)$request->department === (string)$item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Section</label>
                        <select name="section" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['sections'] as $item)
                                <option value="{{ $item->id }}" @selected((string)$request->section === (string)$item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Sub-Section</label>
                        <select name="sub_section" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['subSections'] as $item)
                                <option value="{{ $item->id }}" @selected((string)$request->sub_section === (string)$item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Working Place</label>
                        <select name="working_place" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['workingPlaces'] as $item)
                                <option value="{{ $item->id }}" @selected((string)$request->working_place === (string)$item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Shift</label>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @foreach($options['shifts'] as $item)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="meal_shifts[]"
                                           id="ms_{{ $item->id }}" value="{{ $item->id }}"
                                           @checked(in_array((string)$item->id, (array)$request->meal_shifts))>
                                    <label class="form-check-label" for="ms_{{ $item->id }}">{{ $item->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Meal Type</label>
                        <select name="meal_type" class="form-control form-control-sm">
                            @foreach($mealTypes as $key => $label)
                                <option value="{{ $key }}" @selected(($request->meal_type ?? 'tiffin') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Report Type</label>
                        <select name="report_type" class="form-control form-control-sm">
                            @foreach($reportTypes as $key => $label)
                                <option value="{{ $key }}" @selected(($request->report_type ?? 'details') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                        <a href="{{ route('hr-center.reports.show', $reportKey) }}" class="btn btn-light btn-sm">Reset</a>
                        <button type="submit" name="print" value="1" formtarget="_blank" class="btn btn-primary btn-sm">
                            Report
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection
