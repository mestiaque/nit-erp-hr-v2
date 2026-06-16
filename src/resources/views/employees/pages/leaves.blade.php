@extends('admin.layouts.app')

@section('title')
<title>Leave Table</title>
@endsection

@section('contents')

<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Leave Table: {{ $employee->name }}</h4>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="javascript:void(0)" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#CreateLeaveModal">Add Leave</a>
                <a href="{{ route('hr-center.employees.index') }}" class="btn btn-light btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

            <div class="row mb-3">
                <div class="col-lg-6 mb-3">
                    <div class="card h-100 border">
                        <div class="card-body py-3">
                            <table class="table table-sm table-borderless mb-0">
                                <tr><th style="width: 150px;">Employee ID</th><td>: {{ $employee->employee_id ?? '-' }}</td></tr>
                                <tr><th>Name</th><td>: {{ $employee->name ?? '-' }}</td></tr>
                                <tr><th>Join Date</th><td>: {{ optional($employee->joining_date)->format('Y-m-d') ?? (is_string($employee->joining_date) ? $employee->joining_date : '-') }}</td></tr>
                                <tr><th>Department</th><td>: {{ $employeeMeta['department'] ?? '-' }}</td></tr>
                                <tr><th>Designation</th><td>: {{ $employeeMeta['designation'] ?? '-' }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card h-100 border">
                        <div class="card-body py-3">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Leave Code</th>
                                            <th>Leave Type</th>
                                            <th>Remaining Days</th>
                                            <th>Taken Days</th>
                                            <th>Available Days</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($leaveSummary as $leaveItem)
                                        <tr>
                                            <td>{{ data_get($leaveItem, 'code') }}</td>
                                            <td>{{ data_get($leaveItem, 'name') }}</td>
                                            <td>{{ data_get($leaveItem, 'remaining_days', 0) }}</td>
                                            <td>{{ data_get($leaveItem, 'taken_days', 0) }}</td>
                                            <td>{{ data_get($leaveItem, 'available_days', 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center">No leave info found.</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Leave Code</th>
                            <th>Leave Type</th>
                            <th>Application Date</th>
                            <th>Purpose</th>
                            <th>Leave From</th>
                            <th>Leave To</th>
                            <th>Total Days</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ data_get($row, 'leave_code') ?: '-' }}</td>
                            <td>{{ data_get($row, 'leave_type') ?: '-' }}</td>
                            <td>{{ data_get($row, 'application_date') ?: '-' }}</td>
                            <td>{{ data_get($row, 'purpose') ?: '-' }}</td>
                            <td>{{ data_get($row, 'leave_from') ?: '-' }}</td>
                            <td>{{ data_get($row, 'leave_to') ?: '-' }}</td>
                            <td>{{ data_get($row, 'total_days') ?: 0 }}</td>
                            <td>
                                <a href="javascript:void(0)" class="btn-custom yellow" data-toggle="modal" data-target="#EditLeaveModal_{{ $loop->index }}" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                <form method="post" action="{{ route('hr-center.employees.leaves.delete', $employee->id) }}" style="display:inline-block" onsubmit="return confirm('Delete this leave?');">
                                    @csrf
                                    @method('delete')
                                    <input type="hidden" name="source" value="{{ data_get($row, 'source') }}">
                                    <input type="hidden" name="identifier" value="{{ data_get($row, 'identifier') }}">
                                    <button type="submit" class="btn-custom danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">No leave data found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="CreateLeaveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('hr-center.employees.leaves.store', $employee->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Leave</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-2"><label class="mb-1">Application Date</label><input type="date" name="application_date" value="{{ old('application_date') }}" class="form-control form-control-sm" required></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Application No.</label><input type="text" name="application_no" value="{{ old('application_no') }}" class="form-control form-control-sm"></div>
                        <div class="col-md-6 mb-2">
                            <label class="mb-1">Leave Type</label>
                            <select name="leave_type_id" class="form-control form-control-sm" required>
                                <option value="">Select</option>
                                @foreach($leaveTypes as $leaveType)
                                    <option value="{{ $leaveType->id }}" @selected(old('leave_type_id') == $leaveType->id)>{{ $leaveType->code }} - {{ $leaveType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Leave From</label><input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control form-control-sm" required></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Leave To</label><input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control form-control-sm" required></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Reason</label><input type="text" name="reason" value="{{ old('reason') }}" class="form-control form-control-sm"></div>
                        <div class="col-md-12 mb-2"><label class="mb-1">Remarks</label><textarea name="remarks" class="form-control form-control-sm" rows="3">{{ old('remarks') }}</textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($rows as $row)
<div class="modal fade" id="EditLeaveModal_{{ $loop->index }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('hr-center.employees.leaves.update', $employee->id) }}">
                @csrf
                @method('put')
                <input type="hidden" name="source" value="{{ data_get($row, 'source') }}">
                <input type="hidden" name="identifier" value="{{ data_get($row, 'identifier') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Leave</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-2"><label class="mb-1">Application Date</label><input type="date" name="application_date" value="{{ data_get($row, 'application_date') }}" class="form-control form-control-sm" required></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Application No.</label><input type="text" name="application_no" value="{{ data_get($row, 'application_no') }}" class="form-control form-control-sm"></div>
                        <div class="col-md-6 mb-2">
                            <label class="mb-1">Leave Type</label>
                            <select name="leave_type_id" class="form-control form-control-sm" required>
                                <option value="">Select</option>
                                @foreach($leaveTypes as $leaveType)
                                    <option value="{{ $leaveType->id }}" @selected(data_get($row, 'leave_type_id') == $leaveType->id)>{{ $leaveType->code }} - {{ $leaveType->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Leave From</label><input type="date" name="start_date" value="{{ data_get($row, 'leave_from') }}" class="form-control form-control-sm" required></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Leave To</label><input type="date" name="end_date" value="{{ data_get($row, 'leave_to') }}" class="form-control form-control-sm" required></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Reason</label><input type="text" name="reason" value="{{ data_get($row, 'purpose') }}" class="form-control form-control-sm"></div>
                        <div class="col-md-12 mb-2"><label class="mb-1">Remarks</label><textarea name="remarks" class="form-control form-control-sm" rows="3">{{ data_get($row, 'remarks') }}</textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
