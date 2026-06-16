@extends('admin.layouts.app')

@section('title')
<title>{{ $reportTitle }}</title>
@endsection
@section('contents')
<div class="flex-grow-1 p-4">
    @php
        $classificationMap = collect($options['classifications'] ?? [])->pluck('name', 'id');
        $departmentMap = collect($options['departments'] ?? [])->pluck('name', 'id');
        $sectionMap = collect($options['sections'] ?? [])->pluck('name', 'id');
        $subSectionMap = collect($options['subSections'] ?? [])->pluck('name', 'id');
        $designationMap = collect($options['designations'] ?? [])->pluck('name', 'id');
        $workingPlaceMap = collect($options['workingPlaces'] ?? [])->pluck('name', 'id');
        $lineMap = collect($options['lines'] ?? [])->mapWithKeys(fn ($row) => [
            $row->id => trim(($row->name ?? '') . (filled($row->slug ?? null) ? ' - ' . $row->slug : '')),
        ]);
        $selectedReportType = $request->report_type ?: 'database';
    @endphp

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $reportTitle }}</h4>
            <a href="{{ route('hr-center.reports.index') }}" class="btn btn-light btn-sm">Back</a>
        </div>

        <div class="card-body">
            <form method="get" action="{{ route('hr-center.reports.show', $reportKey) }}" target="_self">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Employee ID</label>
                        <input type="text" name="employee_id" class="form-control form-control-sm" value="{{ $request->employee_id }}" placeholder="Type employee id">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Classification</label>
                        <select name="classification" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['classifications'] as $item)
                                <option value="{{ $item->id }}" @selected((string) $request->classification === (string) $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Department</label>
                        <select name="department" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['departments'] as $item)
                                <option value="{{ $item->id }}" @selected((string) $request->department === (string) $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Section</label>
                        <select name="section" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['sections'] as $item)
                                <option value="{{ $item->id }}" @selected((string) $request->section === (string) $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Sub-Section</label>
                        <select name="sub_section" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['subSections'] as $item)
                                <option value="{{ $item->id }}" @selected((string) $request->sub_section === (string) $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Working Place</label>
                        <select name="working_place" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['workingPlaces'] as $item)
                                <option value="{{ $item->id }}" @selected((string) $request->working_place === (string) $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Block/Line</label>
                        <select name="line_number" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['lines'] as $item)
                                <option value="{{ $item->id }}" @selected((string) $request->line_number === (string) $item->id)>{{ trim(($item->name ?? '') . (filled($item->slug ?? null) ? ' - ' . $item->slug : '')) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Salary Type</label>
                        <select name="salary_type" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['salaryTypes'] as $item)
                                <option value="{{ $item['id'] }}" @selected((string) $request->salary_type === (string) $item['id'])>{{ $item['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Designation</label>
                        <select name="designation" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['designations'] as $item)
                                <option value="{{ $item->id }}" @selected((string) $request->designation === (string) $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Gender</label>
                        <select name="gender" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['gender'] as $item)
                                <option value="{{ $item }}" @selected((string) $request->gender === (string) $item)>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Employee Status</label>
                        <select name="employee_status" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($options['employeeStatuses'] as $item)
                                <option value="{{ $item['id'] }}" @selected((string) $request->employee_status === (string) $item['id'])>{{ $item['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Language</label>
                        <select name="language" class="form-control form-control-sm">
                            <option value="bn" @selected(($request->language ?? $language) === 'bn')>Bangla</option>
                            <option value="en" @selected(($request->language ?? $language) === 'en')>English</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Report Type</label>
                        <select name="report_type" class="form-control form-control-sm">
                            @foreach($reportTypes as $key => $label)
                                <option value="{{ $key }}" @selected($selectedReportType === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 align-self-end mb-3">
                        <div class="">
                            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                            <a href="{{ route('hr-center.reports.show', $reportKey) }}" class="btn btn-light btn-sm">Reset</a>
                            <button type="submit" name="print" value="1" class="btn btn-primary btn-sm" formtarget="_blank">Open Print</button>
                            {{-- <span class="text-muted small align-self-center">No filter selected means all employees will be printed.</span> --}}
                        </div>
                    </div>
                </div>


            </form>

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Filtered Employee List</h6>
                <span class="badge badge-light">{{ $employees->count() }} Employee(s)</span>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Working Place</th>
                            <th>Name</th>
                            <th>Emp. ID</th>
                            <th>Classification</th>
                            <th>Department</th>
                            <th>Section</th>
                            <th>Sub-Section</th>
                            <th>Designation</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            @php
                                $profile = is_array($employee->other_information) ? data_get($employee->other_information, 'profile', []) : [];
                                $employmentStatus = (string) ($employee->employment_status ?? 'regular');
                                if ($employmentStatus === '') {
                                    $employmentStatus = 'regular';
                                }
                                if ($employmentStatus === 'left') {
                                    $employmentStatus = 'lefty';
                                }
                                if ($employmentStatus === 'resigned') {
                                    $employmentStatus = 'resign';
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $workingPlaceMap->get($employee->working_place_id ?? data_get($profile, 'working_place_id')) }}</td>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $employee->employee_id }}</td>
                                <td>{{ $classificationMap->get($employee->employee_type) }}</td>
                                <td>{{ $departmentMap->get($employee->department_id) }}</td>
                                <td>{{ $sectionMap->get($employee->section_id) }}</td>
                                <td>{{ $subSectionMap->get($employee->sub_section_id ?? data_get($profile, 'sub_section_id')) }}</td>
                                <td>{{ $designationMap->get($employee->designation_id) }}</td>
                                <td>{{ ucfirst($employmentStatus) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No employee found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
