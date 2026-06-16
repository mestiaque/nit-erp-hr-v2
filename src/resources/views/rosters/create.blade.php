@extends('admin.layouts.app')

@section('title')
<title>Assign Shift Roster</title>
@endsection

@section('contents')
<div class="flex-grow-1">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Assign Shift Roster</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('hr-center.rosters.store') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Employee</label>
                        <select name="employee_id" class="form-control">
                            <option value="">-- Select --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Section</label>
                        <select name="section_id" class="form-control">
                            <option value="">-- Select --</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Sub Section</label>
                        <select name="sub_section_id" class="form-control">
                            <option value="">-- Select --</option>
                            @foreach($subSections as $subSection)
                                <option value="{{ $subSection->id }}">{{ $subSection->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Shift</label>
                        <select name="shift_id" class="form-control" required>
                            <option value="">-- Select --</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Remarks</label>
                        <input type="text" name="remarks" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Assign</button>
                <a href="{{ route('hr-center.rosters.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
