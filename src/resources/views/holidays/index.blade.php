@extends('admin.layouts.app')

@section('title')
<title>Factory Holidays</title>
@endsection

@section('contents')
<div class="flex-grow-1">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Factory Holidays</h4>
            <a href="javascript:void(0)" class="btn btn-primary btn-sm"
               data-toggle="modal" data-target="#CreateHolidayModal">
                + Add
            </a>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- Filter --}}
            <form method="GET" class="row mb-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ $request->search }}"
                           class="form-control form-control-sm" placeholder="Purpose or type...">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All</option>
                        <option value="active"   @selected($request->status === 'active')>Active</option>
                        <option value="inactive" @selected($request->status === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary btn-sm w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('hr-center.holidays.index') }}" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">SL</th>
                            <th>Purpose</th>
                            <th>Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th width="90">Total Days</th>
                            <th width="80">Status</th>
                            <th width="100">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($holidays as $index => $holiday)
                        <tr>
                            <td>{{ $holidays->firstItem() + $index }}</td>
                            <td>{{ $holiday->title }}</td>
                            <td>{{ $holiday->type }}</td>
                            <td>{{ $holiday->from_date ? \Carbon\Carbon::parse($holiday->from_date)->format('d M Y') : '–' }}</td>
                            <td>{{ $holiday->to_date ? \Carbon\Carbon::parse($holiday->to_date)->format('d M Y') : '–' }}</td>
                            <td class="text-center">{{ $holiday->days }}</td>
                            <td class="text-center">
                                @if($holiday->status === 'active')
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-custom yellow btn-sm mr-1 btn-edit"
                                    data-id="{{ $holiday->id }}"
                                    data-title="{{ $holiday->title }}"
                                    data-type="{{ $holiday->type }}"
                                    data-from="{{ $holiday->from_date }}"
                                    data-to="{{ $holiday->to_date }}"
                                    data-days="{{ $holiday->days }}"
                                    data-remarks="{{ $holiday->remarks }}"
                                    data-status="{{ $holiday->status }}"
                                    data-toggle="modal" data-target="#EditHolidayModal">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <form action="{{ route('hr-center.holidays.destroy', $holiday->id) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this holiday?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-custom danger btn-xs">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No holidays found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $holidays->links() }}

        </div>
    </div>
</div>

{{-- ===================== CREATE MODAL ===================== --}}
<div class="modal fade" id="CreateHolidayModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('hr-center.holidays.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Holiday</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label class="form-label">Purpose <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-sm"
                               placeholder="e.g. Eid-ul-Fitr" required>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-control form-control-sm" required>
                            <option value="">— Select Type —</option>
                            @foreach($types as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label class="form-label">From <span class="text-danger">*</span></label>
                                <input type="date" name="from_date" id="create_from" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label class="form-label">To <span class="text-danger">*</span></label>
                                <input type="date" name="to_date" id="create_to" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label class="form-label">Days</label>
                                <input type="number" name="days" id="create_days" class="form-control form-control-sm" min="1" required readonly style="background:#f8f9fa">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Optional..."></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control form-control-sm" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===================== EDIT MODAL ===================== --}}
<div class="modal fade" id="EditHolidayModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="EditHolidayForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Holiday</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label class="form-label">Purpose <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit_title" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" id="edit_type" class="form-control form-control-sm" required>
                            <option value="">— Select Type —</option>
                            @foreach($types as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label class="form-label">From <span class="text-danger">*</span></label>
                                <input type="date" name="from_date" id="edit_from" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label class="form-label">To <span class="text-danger">*</span></label>
                                <input type="date" name="to_date" id="edit_to" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-2">
                                <label class="form-label">Days</label>
                                <input type="number" name="days" id="edit_days" class="form-control form-control-sm" min="1" required readonly style="background:#f8f9fa">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" id="edit_remarks" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_status" class="form-control form-control-sm" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
(function () {
    // Auto-calculate days from from_date + to_date (Create)
    function calcDays(fromId, toId, daysId) {
        var from = document.getElementById(fromId);
        var to   = document.getElementById(toId);
        var days = document.getElementById(daysId);
        function update() {
            if (from.value && to.value) {
                var d1  = new Date(from.value);
                var d2  = new Date(to.value);
                var diff = Math.round((d2 - d1) / 86400000) + 1;
                if (diff > 0) days.value = diff;
            }
        }
        from.addEventListener('change', update);
        to.addEventListener('change', update);
    }

    calcDays('create_from', 'create_to', 'create_days');
    calcDays('edit_from',   'edit_to',   'edit_days');

    // Populate Edit modal
    document.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.dataset.id;
            document.getElementById('EditHolidayForm').action =
                '{{ url("admin/hr-center/holidays") }}/' + id;

            document.getElementById('edit_title').value   = this.dataset.title;
            document.getElementById('edit_from').value    = this.dataset.from;
            document.getElementById('edit_to').value      = this.dataset.to;
            document.getElementById('edit_days').value    = this.dataset.days;
            document.getElementById('edit_remarks').value = this.dataset.remarks;
            document.getElementById('edit_status').value  = this.dataset.status;

            // Set select for type
            var typeSelect = document.getElementById('edit_type');
            for (var i = 0; i < typeSelect.options.length; i++) {
                if (typeSelect.options[i].value === this.dataset.type) {
                    typeSelect.selectedIndex = i;
                    break;
                }
            }
        });
    });
})();
</script>
@endpush
