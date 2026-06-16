@extends('admin.layouts.app')

@section('title')
<title>Others Earnings & Deductions</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Others Earnings & Deductions: {{ $employee->name }}</h4>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="javascript:void(0)" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#CreateEarningsDeductionModal">Add Entry</a>
                <a href="{{ route('hr-center.employees.index') }}" class="btn btn-light btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

            <div class="card border mb-3">
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

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Year</th>
                            <th>Month</th>
                            <th>Advance/IOU</th>
                            <th>OT(+/-)</th>
                            <th>Days</th>
                            <th>Earnings</th>
                            <th>Deductions</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ data_get($row, 'year') }}</td>
                                <td>{{ data_get($row, 'month') }}</td>
                                <td>{{ number_format((float) data_get($row, 'advance_iou', 0), 2) }}</td>
                                <td>{{ number_format((float) data_get($row, 'ot', 0), 2) }}</td>
                                <td>{{ number_format((float) data_get($row, 'day', 0), 2) }}</td>
                                <td>{{ number_format((float) data_get($row, 'earnings', 0), 2) }}</td>
                                <td>{{ number_format((float) data_get($row, 'deductions', 0), 2) }}</td>
                                <td>{{ data_get($row, 'remarks') ?: '-' }}</td>
                                <td>
                                    <a href="javascript:void(0)" class="btn-custom yellow" data-toggle="modal" data-target="#EditEarningsDeductionModal_{{ $loop->index }}" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                    <form method="post" action="{{ route('hr-center.employees.earnings.delete', $employee->id) }}" style="display:inline-block" onsubmit="return confirm('Delete this entry?');">
                                        @csrf
                                        @method('delete')
                                        <input type="hidden" name="identifier" value="{{ data_get($row, 'identifier') }}">
                                        <button type="submit" class="btn-custom danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center">No data found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="CreateEarningsDeductionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('hr-center.employees.earnings.store', $employee->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Others Earnings & Deductions</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-2"><label class="mb-1">Date</label><input type="date" name="date" value="{{ old('date') }}" class="form-control form-control-sm" required></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Advance/IOU</label><input type="number" step="0.01" name="advance_iou" value="{{ old('advance_iou') }}" class="form-control form-control-sm"></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">OT(+/-)</label><input type="number" step="1" name="ot" value="{{ old('ot') }}" class="form-control form-control-sm"></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Day(+/-)</label><input type="number" step="1" name="day" value="{{ old('day') }}" class="form-control form-control-sm"></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Earnings</label><input type="number" step="0.01" name="earnings" value="{{ old('earnings') }}" class="form-control form-control-sm"></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Deductions</label><input type="number" step="0.01" name="deductions" value="{{ old('deductions') }}" class="form-control form-control-sm"></div>
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
<div class="modal fade" id="EditEarningsDeductionModal_{{ $loop->index }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('hr-center.employees.earnings.update', $employee->id) }}">
                @csrf
                @method('put')
                <input type="hidden" name="identifier" value="{{ data_get($row, 'identifier') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Others Earnings & Deductions</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-2"><label class="mb-1">Date</label><input type="date" name="date" value="{{ data_get($row, 'date') }}" class="form-control form-control-sm" required></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Advance/IOU</label><input type="number" step="0.01" name="advance_iou" value="{{ data_get($row, 'advance_iou') }}" class="form-control form-control-sm"></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">OT(+/-)</label><input type="number" step="1" name="ot" value="{{ data_get($row, 'ot') }}" class="form-control form-control-sm"></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Day(+/-)</label><input type="number" step="1" name="day" value="{{ data_get($row, 'day') }}" class="form-control form-control-sm"></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Earnings</label><input type="number" step="0.01" name="earnings" value="{{ data_get($row, 'earnings') }}" class="form-control form-control-sm"></div>
                        <div class="col-md-6 mb-2"><label class="mb-1">Deductions</label><input type="number" step="0.01" name="deductions" value="{{ data_get($row, 'deductions') }}" class="form-control form-control-sm"></div>
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
