@extends('admin.layouts.app')

@section('title')
<title>Salary Increment Info</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Salary Increment Info: {{ $employee->name }}</h4>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="javascript:void(0)" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#CreateIncrementModal">Add Increment</a>
                <a href="{{ route('hr-center.employees.index') }}" class="btn btn-light btn-sm">Back</a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Emp ID</th>
                            <th>Name</th>
                            <th>Classification</th>
                            <th>Department</th>
                            <th>Section</th>
                            <th>Designation</th>
                            <th>Previous Salary</th>
                            <th>Increment Amount</th>
                            <th>New Salary</th>
                            <th>Increment Date</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $employee->employee_id ?? '-' }}</td>
                            <td>{{ $employee->name ?? '-' }}</td>
                            <td>{{ $employeeMeta['classification'] ?? '-' }}</td>
                            <td>{{ $employeeMeta['department'] ?? '-' }}</td>
                            <td>{{ $employeeMeta['section'] ?? '-' }}</td>
                            <td>{{ $employeeMeta['designation'] ?? '-' }}</td>
                            <td>{{ number_format((float) data_get($row, 'previous_salary', 0), 2) }}</td>
                            <td>{{ number_format((float) data_get($row, 'increment_amount', 0), 2) }}</td>
                            <td>{{ number_format((float) data_get($row, 'new_salary', 0), 2) }}</td>
                            <td>{{ data_get($row, 'increment_date') ?: '-' }}</td>
                            <td>
                                @if($loop->index === 0)
                                    <a href="javascript:void(0)" class="btn-custom yellow" data-toggle="modal" data-target="#EditIncrementModal_{{ $loop->index }}" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                @endif  
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">No increment data found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="CreateIncrementModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('hr-center.employees.increments.store', $employee->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Increment</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="mb-1">Gross Salary (Increment)</label>
                        <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="mb-1">Increment Date</label>
                        <input type="date" name="increment_date" value="{{ old('increment_date') }}" class="form-control form-control-sm" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($rows as $row)
<div class="modal fade" id="EditIncrementModal_{{ $loop->index }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('hr-center.employees.increments.update', $employee->id) }}">
                @csrf
                @method('put')
                <input type="hidden" name="source" value="{{ data_get($row, 'source') }}">
                <input type="hidden" name="identifier" value="{{ data_get($row, 'identifier') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Increment</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="mb-1">Increment Amount</label>
                        <input type="number" step="0.01" name="amount" value="{{ data_get($row, 'increment_amount') }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="mb-1">Increment Date</label>
                        <input type="date" name="increment_date" value="{{ data_get($row, 'increment_date') }}" class="form-control form-control-sm" required>
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
