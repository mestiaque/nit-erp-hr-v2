@extends('admin.layouts.app')

@section('title')
<title>Edit Shift Roster</title>
@endsection

@section('contents')
<div class="flex-grow-1">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Edit Shift Roster</h4>
            <a href="{{ route('hr-center.rosters.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
        </div>
        <div class="card-body">
            <form action="{{ route('hr-center.rosters.update', $roster->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label id="dateLabel">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ old('date', $existingRule->anchor_date ?? $roster->roster_date) }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Employee</label>
                        <select name="employee_id" class="form-control select2" required>
                            <option value="">-- Select --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected((string) old('employee_id', $roster->employee_id) === (string) $employee->id)>{{ $employee->employee_id }} — {{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label id="shiftLabel">Shift</label>
                        <select name="shift_id" class="form-control" required>
                            <option value="">-- Select --</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" @selected((string) old('shift_id', $existingRule->primary_shift_id ?? $roster->shift_id) === (string) $shift->id)>{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="{{ old('remarks', $roster->remarks) }}">
                    </div>
                </div>

                <hr>

                <div class="form-row align-items-end">
                    <div class="form-group col-md-3 mb-0">
                        <div class="custom-control custom-checkbox mt-4">
                            <input type="checkbox" class="custom-control-input" id="autoRoster" name="auto_roster" value="1" @checked(old('auto_roster', $existingRule->is_active ?? false))>
                            <label class="custom-control-label" for="autoRoster">Auto Roster</label>
                        </div>
                    </div>
                    <div class="form-group col-md-3 mb-0" id="altShiftGroup">
                        <label>Alternate Shift</label>
                        <select name="alt_shift_id" id="altShift" class="form-control">
                            <option value="">-- Select --</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" @selected((string) old('alt_shift_id', $existingRule->alt_shift_id ?? '') === (string) $shift->id)>{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 mb-0" id="dayOfWeekGroup">
                        <label>Day</label>
                        <select name="day_of_week" id="dayOfWeek" class="form-control">
                            @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $i => $dayName)
                                <option value="{{ $i }}" @selected((string) old('day_of_week', $existingRule->day_of_week ?? '5') === (string) $i)>{{ $dayName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <small class="text-muted d-block mb-3" id="autoRosterHint">
                    When on: <strong>Shift</strong> is this employee's regular shift and <strong>Date</strong> is the start date. From the first occurrence of the selected <strong>Day</strong> on/after that date, the shift alternates every week — Alternate Shift, then regular Shift, then Alternate again — and continues indefinitely.
                </small>

                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
    $('.select2').select2();

    (function () {
        var toggle = document.getElementById('autoRoster');
        var altShift = document.getElementById('altShift');
        var dayOfWeek = document.getElementById('dayOfWeek');
        var altShiftGroup = document.getElementById('altShiftGroup');
        var dayOfWeekGroup = document.getElementById('dayOfWeekGroup');
        var dateLabel = document.getElementById('dateLabel');
        var shiftLabel = document.getElementById('shiftLabel');
        var hint = document.getElementById('autoRosterHint');

        function sync() {
            var on = toggle.checked;
            altShiftGroup.style.display = on ? '' : 'none';
            dayOfWeekGroup.style.display = on ? '' : 'none';
            altShift.disabled = !on;
            dayOfWeek.disabled = !on;
            hint.style.display = on ? '' : 'none';
            dateLabel.textContent = on ? 'Start Date' : 'Date';
            shiftLabel.textContent = on ? 'Regular Shift' : 'Shift';
        }

        toggle.addEventListener('change', sync);
        sync();
    })();
</script>
@endpush
@endsection
