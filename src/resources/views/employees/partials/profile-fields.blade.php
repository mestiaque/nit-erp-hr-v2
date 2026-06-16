@php
    $other = is_array($employee->other_information) ? $employee->other_information : json_decode($employee->other_information, true);
    $profile = is_array($other) ? data_get($other, 'profile', []) : [];
    $selectedSubSection = old('sub_section_id', $employee->sub_section_id ?? data_get($profile, 'sub_section_id'));
    $selectedWorkingPlace = old('working_place_id', $employee->working_place_id ?? data_get($profile, 'working_place_id'));
    $selectedWeekend = old('weekend', $employee->weekend ?? data_get($profile, 'weekend'));
    $selectedActive01 = old('is_active_01', $employee->is_active_01 ?? data_get($profile, 'is_active_01', 1));
    $selectedActive02 = old('is_active_02', $employee->is_active_02 ?? data_get($profile, 'is_active_02', 1));
    $selectedStatus = old('status', ((string) ($employee->status ?? '1') === '1' || (string) ($employee->status ?? '') === 'active') ? 'active' : 'inactive');
    $weekendDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $isStatusActive = (string) $selectedStatus === 'active';
    $isActive01On = (string) $selectedActive01 === '1';
    $isActive02On = (string) $selectedActive02 === '1';
@endphp

<div class="row">
    @if(!empty($employee->id))
    <div class="col-md-6 mb-2">
        <label class="mb-1">Image</label>
        <input type="file" name="profile_image" accept="image/*" class="form-control form-control-sm">
        <small class="text-muted d-block mt-1">jpg, jpeg, png, gif, webp (max 2MB)</small>
        <img src="{{ asset($employee->image()) }}" alt="Employee Image" style="width:50px;height:50px;object-fit:cover;border:1px solid #ddd;margin-top:6px;">
    </div>
    @endif
    <div class="col-md-6 mb-2"><label class="mb-1">Name</label><input type="text" name="name" value="{{ old('name', $employee->name) }}" class="form-control form-control-sm" required></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Name Bangla</label><input type="text" name="bn_name" value="{{ old('bn_name', $employee->bn_name) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Employee ID</label><input type="text" name="employee_id" value="{{ old('employee_id', $employee->employee_id) }}" class="form-control form-control-sm" required></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Join Date</label><input type="date" name="joining_date" value="{{ old('joining_date', optional($employee->joining_date)->format('Y-m-d')) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Classification</label><select name="employee_type" class="form-control form-control-sm"><option value="">Select</option>@foreach($options['classifications'] as $row)<option value="{{ $row->id }}" @selected((string) old('employee_type', $employee->employee_type) === (string) $row->id)>{{ $row->name }}</option>@endforeach</select></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Department</label><select name="department_id" class="form-control form-control-sm"><option value="">Select</option>@foreach($options['departments'] as $row)<option value="{{ $row->id }}" @selected((string) old('department_id', $employee->department_id) === (string) $row->id)>{{ $row->name }}</option>@endforeach</select></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Section</label><select name="section_id" class="form-control form-control-sm"><option value="">Select</option>@foreach($options['sections'] as $row)<option value="{{ $row->id }}" @selected((string) old('section_id', $employee->section_id) === (string) $row->id)>{{ $row->name }}</option>@endforeach</select></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Sub-Section</label><select name="sub_section_id" class="form-control form-control-sm"><option value="">Select</option>@foreach($options['subSections'] as $row)<option value="{{ $row->id }}" @selected((string) $selectedSubSection === (string) $row->id)>{{ $row->name }}</option>@endforeach</select></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Block/Line</label><select name="line_number" class="form-control form-control-sm"><option value="">Select</option>@foreach($options['lines'] as $row)<option value="{{ $row->id }}" @selected((string) old('line_number', $employee->line_number) === (string) $row->id)>{{ trim(($row->name ?? '') . (filled($row->slug ?? null) ? ' - ' . $row->slug : '')) }}</option>@endforeach</select></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Designation</label><select name="designation_id" class="form-control form-control-sm"><option value="">Select</option>@foreach($options['designations'] as $row)<option value="{{ $row->id }}" @selected((string) old('designation_id', $employee->designation_id) === (string) $row->id)>{{ $row->name }}</option>@endforeach</select></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Working Place</label><select name="working_place_id" class="form-control form-control-sm"><option value="">Select</option>@foreach($options['workingPlaces'] as $row)<option value="{{ $row->id }}" @selected((string) $selectedWorkingPlace === (string) $row->id)>{{ $row->name }}</option>@endforeach</select></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Shift</label><select name="shift_id" class="form-control form-control-sm"><option value="">Select</option>@foreach($options['shifts'] as $row)<option value="{{ $row->id }}" @selected((string) old('shift_id', $employee->shift_id) === (string) $row->id)>{{ $row->name }}</option>@endforeach</select></div>
    <div class="col-md-6 mb-2">
        <label class="mb-1">Weekend</label>
        <select name="weekend" class="form-control form-control-sm">
            <option value="">Select</option>
            @foreach($weekendDays as $day)
                <option value="{{ $day }}" @selected((string) $selectedWeekend === (string) $day)>{{ $day }}</option>
            @endforeach
            @if(!empty($selectedWeekend) && !in_array((string) $selectedWeekend, $weekendDays, true))
                <option value="{{ $selectedWeekend }}" selected>{{ $selectedWeekend }}</option>
            @endif
        </select>
    </div>
    <div class="col-md-6 mb-2"><label class="mb-1">Personal Contact</label><input type="text" name="mobile" value="{{ old('mobile', $employee->mobile) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Emergency Contact</label><input type="text" name="emergency_mobile" value="{{ old('emergency_mobile', $employee->emergency_mobile) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2">
        <label class="mb-1 d-block">Is Active</label>
        <input type="hidden" name="status" value="inactive">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="status_switch_{{ $employee->id ?? 'new' }}" name="status" value="active" @checked($isStatusActive)>
            <label class="custom-control-label" for="status_switch_{{ $employee->id ?? 'new' }}">{{ $isStatusActive ? 'Active' : 'Inactive' }}</label>
        </div>
    </div>
    <div class="col-md-6 mb-2">
        <label class="mb-1 d-block">Is Active-01</label>
        <input type="hidden" name="is_active_01" value="0">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="active01_switch_{{ $employee->id ?? 'new' }}" name="is_active_01" value="1" @checked($isActive01On)>
            <label class="custom-control-label" for="active01_switch_{{ $employee->id ?? 'new' }}">{{ $isActive01On ? 'Active' : 'Inactive' }}</label>
        </div>
    </div>
    <div class="col-md-6 mb-2">
        <label class="mb-1 d-block">Is Active-02</label>
        <input type="hidden" name="is_active_02" value="0">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="active02_switch_{{ $employee->id ?? 'new' }}" name="is_active_02" value="1" @checked($isActive02On)>
            <label class="custom-control-label" for="active02_switch_{{ $employee->id ?? 'new' }}">{{ $isActive02On ? 'Active' : 'Inactive' }}</label>
        </div>
    </div>
</div>
