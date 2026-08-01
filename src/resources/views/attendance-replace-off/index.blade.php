@extends('admin.layouts.app')

@section('title')
<title>Day Swap</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Day Swap (Replace Off)</h4>
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createModal">Add New Swap</button>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <p class="text-muted small">
                Use this when the office stays open on a normally weekend/holiday date (<strong>Worked Date</strong>)
                and everyone takes a compensatory day off on another date instead (<strong>Replace Date</strong>).
                Applying a swap moves every employee's attendance for Worked Date onto Replace Date — recalculated
                as a regular working day — and permanently blocks Worked Date from ever accepting attendance again
                (including future machine syncs).
            </p>

            <form method="get" class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All</option>
                        <option value="active" @selected($request->status === 'active')>Active</option>
                        <option value="cancelled" @selected($request->status === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Worked Date</label>
                    <input type="date" name="worked_date" class="form-control form-control-sm" value="{{ $request->worked_date }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary btn-sm w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('hr-center.attendance-replace-off.index') }}" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Worked Date</th>
                            <th>Replace Date</th>
                            <th>Status</th>
                            <th>Records Moved</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ optional($item->worked_date)->format('d-M-Y') }}</td>
                                <td>{{ optional($item->replace_date)->format('d-M-Y') }}</td>
                                <td>
                                    <span class="badge {{ $item->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>{{ $item->moved_attendance_count }}</td>
                                <td>{{ $item->remarks }}</td>
                                <td>
                                    @if($item->status === 'active')
                                        <form method="POST" action="{{ route('hr-center.attendance-replace-off.cancel', $item->id) }}"
                                              onsubmit="return confirm('Cancel this swap? This only un-blocks Worked Date for future attendance — it will NOT move already-relocated attendance back.');">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-custom red btn-sm"><i class="fa fa-ban"></i> Cancel</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No data found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $items->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade text-left" id="createModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form method="POST" action="{{ route('hr-center.attendance-replace-off.store') }}"
                onsubmit="return confirm('This will move every employee\'s attendance from Worked Date to Replace Date and permanently block Worked Date. Continue?');">
            @csrf
            <div class="modal-header">
              <h5 class="modal-title">Add Day Swap</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label>Worked Date <small class="text-muted">(office stayed open, e.g. a weekend)</small></label>
                <input type="date" name="worked_date" class="form-control" required value="{{ old('worked_date') }}">
              </div>
              <div class="mb-3">
                <label>Replace Date <small class="text-muted">(compensatory day off)</small></label>
                <input type="date" name="replace_date" class="form-control" required value="{{ old('replace_date') }}">
              </div>
              <div class="mb-3">
                <label>Remarks</label>
                <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary btn-sm">Apply Swap</button>
            </div>
          </form>
        </div>
      </div>
    </div>
</div>
@endsection
