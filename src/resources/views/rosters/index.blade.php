@extends('admin.layouts.app')

@section('title')
<title>Shift Rosters</title>
@endsection

@section('contents')
<div class="flex-grow-1">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Shift Rosters</h4>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#rosterModal">+ Assign Roster</button>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>SL</th>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Section</th>
                            <th>Sub Section</th>
                            <th>Shift</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rosters as $index => $roster)
                        <tr>
                            <td>{{ $rosters->firstItem() + $index }}</td>
                            <td>{{ $roster->date }}</td>
                            <td>{{ $roster->employee->name ?? '-' }}</td>
                            <td>{{ $roster->section->name ?? '-' }}</td>
                            <td>{{ $roster->subSection->name ?? '-' }}</td>
                            <td>{{ $roster->shift->name ?? '-' }}</td>
                            <td>{{ $roster->remarks }}</td>
                            <td>
                                <form action="{{ route('hr-center.rosters.destroy', $roster->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this roster?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No data found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-2">
                {{ $rosters->links() }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rosterModal" tabindex="-1" role="dialog" aria-labelledby="rosterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rosterModalLabel">Assign Roster</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('hr-center.rosters.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Employee</label>
                        <select name="employee_id" class="form-control">
                            <option value="">-- Select --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Section</label>
                        <select name="section_id" class="form-control">
                            <option value="">-- Select --</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sub Section</label>
                        <select name="sub_section_id" class="form-control">
                            <option value="">-- Select --</option>
                            @foreach($subSections as $subSection)
                                <option value="{{ $subSection->id }}">{{ $subSection->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label>Shift</label>
                        {{-- @dd($shifts); --}}
                        <select name="shift_id" class="form-control" required>
                            <option value="">-- Select --</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-3 mb-0">
                        <label>Remarks</label>
                        <input type="text" name="remarks" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
