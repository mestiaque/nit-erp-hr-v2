@extends('admin.layouts.app')

@section('title')
<title>No-OT Dates</title>
@endsection

@section('contents')
<div class="flex-grow-1">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">No-OT Dates</h4>
            @if(hasChildPermission('hr_no_ot_date', 'add'))
                <a href="javascript:void(0)" class="btn btn-sm ui-act ui-act-create" data-toggle="modal" data-target="#CreateNoOtDateModal" title="Add"><i class="fa-solid fa-plus"></i> Add</a>
            @endif
        </div>
        <div class="card-body">
            <p class="text-muted small">
                Nobody earns OT on these dates — the Job Card, OT Sheet, OT Summary, Salary Sheet and every other
                attendance report count 0 OT for the day. Attendance itself (present / late / absent) is not changed.
                A date inside a locked salary month can't be added or removed until that month is unlocked.
            </p>

            <form method="GET" class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Year</label>
                    <input type="number" name="year" value="{{ $request->year }}" class="form-control form-control-sm" placeholder="e.g. {{ now()->year }}">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-sm ui-act ui-act-filter" title="Filter"><i class="fa-solid fa-filter"></i> Filter</button>
                    <a href="{{ route('hr-center.no-ot-dates.index') }}" class="btn btn-sm ui-act ui-act-reset" title="Reset"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">SL</th>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Reason</th>
                            <th width="80">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dates as $index => $row)
                            <tr>
                                <td>{{ $dates->firstItem() + $index }}</td>
                                <td>{{ $row->date->format('d M Y') }}</td>
                                <td>{{ $row->date->format('l') }}</td>
                                <td>{{ $row->reason ?: '—' }}</td>
                                <td>
                                    @if(hasChildPermission('hr_no_ot_date', 'delete'))
                                        <form action="{{ route('hr-center.no-ot-dates.destroy', $row->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Remove {{ $row->date->format('d M Y') }}? OT for that day will be counted again.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-custom ui-act ui-act-delete" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No No-OT dates.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $dates->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@if(hasChildPermission('hr_no_ot_date', 'add'))
<div class="modal fade" id="CreateNoOtDateModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('hr-center.no-ot-dates.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add No-OT Date</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" value="{{ old('date') }}" class="form-control form-control-sm" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason</label>
                    <input type="text" name="reason" value="{{ old('reason') }}" maxlength="255" class="form-control form-control-sm" placeholder="e.g. buyer audit, management decision">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm ui-act ui-act-cancel" data-dismiss="modal" title="Cancel"><i class="fa-solid fa-ban"></i> Cancel</button>
                <button type="submit" class="btn btn-sm ui-act ui-act-save" title="Save"><i class="fa-solid fa-floppy-disk"></i> Save</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
