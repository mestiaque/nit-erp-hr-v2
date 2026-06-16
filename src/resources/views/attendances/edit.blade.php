@extends('layouts.app')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">Employee: {{ $employee->name ?? '' }} ({{ $employee->employee_id ?? '' }})</h5>
        <p class="card-text">
            <strong>Shift:</strong> {{ $shift->name ?? '-' }}<br>
            <strong>Date:</strong> {{ $date }}<br>
            <strong>Holiday:</strong> @if(isset($shift->is_holiday) && $shift->is_holiday) Yes @else No @endif
        </p>
    </div>
</div>
<form id="attendance-edit-form" method="POST" action="{{ route('hr-center.attendances.update', [$employee->id, $date]) }}">
    @csrf
    <div class="mb-3">
        <label for="in_time" class="form-label">In Time</label>
        <input type="time" class="form-control" id="in_time" name="in_time" value="{{ $attendance->in_time ?? '' }}">
    </div>
    <div class="mb-3">
        <label for="out_time" class="form-label">Out Time</label>
        <input type="time" class="form-control" id="out_time" name="out_time" value="{{ $attendance->out_time ?? '' }}">
    </div>
    <div class="mb-3">
        <label for="remarks" class="form-label">Remarks</label>
        <textarea class="form-control" id="remarks" name="remarks">{{ $attendance->remarks ?? '' }}</textarea>
    </div>
    <button type="submit" class="btn btn-success">Update Attendance</button>
</form>
@endsection
