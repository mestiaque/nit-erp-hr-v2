@extends('admin.layouts.app')

@section('title')
<title>Employee Gate Pass</title>
@endsection

@push('css')
<style>
    /* .gp-card { border-radius: 12px; border: 1px solid #eef0f4; }
    .gp-card .card-header { border-radius: 12px 12px 0 0; background: #fff; }
    .badge-soft-active { background: #e7f1ff; color: #0d6efd; font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 11px; }
    .badge-soft-returned { background: #eaf7ee; color: #198754; font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 11px; }
    .table-gp thead th { background: #f8f9fb; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; color: #6c757d; border-top: none; }
    .table-gp td { vertical-align: middle; font-size: 13px; } */
</style>
@endpush

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card gp-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Employee Gate Pass</h4>
            <a href="javascript:void(0)" class="btn btn-primary btn-sm rounded-pill px-3"
               data-toggle="modal" data-target="#CreateGatePassModal">
                <i class="fa-solid fa-plus"></i> Create Gate Pass
            </a>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- Search --}}
            <form method="GET" class="row mb-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ $request->search }}"
                           class="form-control form-control-sm" placeholder="Pass No, Employee ID or Name...">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary btn-sm w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('hr-center.gate-passes.index') }}" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-sm table-gp">
                    <thead>
                        <tr>
                            <th width="50">SL</th>
                            <th>Pass No</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Out Time</th>
                            <th>In Time</th>
                            <th width="90">Duration</th>
                            <th>Reason</th>
                            <th width="90">Status</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gatePasses as $index => $gatePass)
                        <tr>
                            <td>{{ $gatePasses->firstItem() + $index }}</td>
                            <td>{{ $gatePass->pass_no }}</td>
                            <td>{{ $gatePass->employee->employee_id ?? '-' }} &mdash; {{ $gatePass->employee->name ?? 'N/A' }}</td>
                            <td>{{ $gatePass->employee->department->name ?? 'N/A' }}</td>
                            <td>{{ optional($gatePass->out_time)->format('d M Y, h:i A') }}</td>
                            <td>{{ optional($gatePass->in_time)->format('d M Y, h:i A') }}</td>
                            <td>{{ $gatePass->duration_minutes }} min</td>
                            <td>{{ $gatePass->reason }}</td>
                            <td>
                                @if($gatePass->status === 'Active')
                                    <span class="badge-soft-active">Active</span>
                                @else
                                    <span class="badge-soft-returned">Returned</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn-custom" title="View"
                                        data-toggle="modal" data-target="#ViewGatePassModal"
                                        data-pass-no="{{ $gatePass->pass_no }}"
                                        data-employee="{{ ($gatePass->employee->employee_id ?? '-') . ' — ' . ($gatePass->employee->name ?? 'N/A') }}"
                                        data-department="{{ $gatePass->employee->department->name ?? 'N/A' }}"
                                        data-out="{{ optional($gatePass->out_time)->format('d M Y, h:i A') }}"
                                        data-in="{{ optional($gatePass->in_time)->format('d M Y, h:i A') }}"
                                        data-duration="{{ $gatePass->duration_minutes }}"
                                        data-reason="{{ $gatePass->reason }}"
                                        data-remarks="{{ $gatePass->remarks }}"
                                        data-status="{{ $gatePass->status }}">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <a href="{{ route('hr-center.gate-passes.print', $gatePass->id) }}" class="btn-custom" title="Print" target="_blank">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <button type="button" class="btn-custom yellow" title="Edit"
                                        data-toggle="modal" data-target="#EditGatePassModal"
                                        data-id="{{ $gatePass->id }}"
                                        data-out="{{ optional($gatePass->out_time)->format('Y-m-d\TH:i') }}"
                                        data-in="{{ optional($gatePass->in_time)->format('Y-m-d\TH:i') }}"
                                        data-duration="{{ $gatePass->duration_minutes }}"
                                        data-reason="{{ $gatePass->reason }}"
                                        data-remarks="{{ $gatePass->remarks }}"
                                        data-status="{{ $gatePass->status }}">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">No gate pass found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $gatePasses->links() }}

        </div>
    </div>
</div>

{{-- ===================== CREATE MODAL ===================== --}}
<div class="modal fade" id="CreateGatePassModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('hr-center.gate-passes.store') }}" method="POST">
            @csrf
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-header">
                    <h5 class="modal-title">Create Gate Pass</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control form-control-sm select2" required>
                            <option value="">— Select Employee —</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->employee_id }} — {{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="create_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label">Out Time <span class="text-danger">*</span></label>
                                <input type="time" name="out_time" id="create_out_time" class="form-control form-control-sm" value="{{ now()->format('H:i') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label">Duration (Minutes)</label>
                                <input type="number" min="1" name="duration_minutes" id="create_duration" class="form-control form-control-sm" placeholder="e.g. 60">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">In Time <span class="text-danger">*</span></label>
                        <input type="time" name="in_time" id="create_in_time" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <select name="reason" class="form-control form-control-sm" required>
                            <option value="">— Select Reason —</option>
                            @foreach($reasons as $reason)
                                <option value="{{ $reason }}">{{ $reason }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save &amp; Print</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===================== EDIT MODAL ===================== --}}
<div class="modal fade" id="EditGatePassModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="EditGatePassForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Gate Pass</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label">Out Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="out_time" id="edit_out_time" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="form-label">Duration (Minutes)</label>
                                <input type="number" min="1" name="duration_minutes" id="edit_duration" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">In Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="in_time" id="edit_in_time" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <select name="reason" id="edit_reason" class="form-control form-control-sm" required>
                            @foreach($reasons as $reason)
                                <option value="{{ $reason }}">{{ $reason }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" id="edit_remarks" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_status" class="form-control form-control-sm" required>
                            <option value="Active">Active</option>
                            <option value="Returned">Returned</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===================== VIEW MODAL ===================== --}}
<div class="modal fade" id="ViewGatePassModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:12px;">
            <div class="modal-header">
                <h5 class="modal-title">Gate Pass Details</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Pass No</td><td id="view_pass_no" class="font-weight-bold"></td></tr>
                    <tr><td class="text-muted">Employee</td><td id="view_employee"></td></tr>
                    <tr><td class="text-muted">Department</td><td id="view_department"></td></tr>
                    <tr><td class="text-muted">Out Time</td><td id="view_out"></td></tr>
                    <tr><td class="text-muted">In Time</td><td id="view_in"></td></tr>
                    <tr><td class="text-muted">Duration</td><td id="view_duration"></td></tr>
                    <tr><td class="text-muted">Reason</td><td id="view_reason"></td></tr>
                    <tr><td class="text-muted">Remarks</td><td id="view_remarks"></td></tr>
                    <tr><td class="text-muted">Status</td><td id="view_status"></td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
(function () {
    // dropdownParent is required here — Select2's search box loses keyboard input
    // inside a Bootstrap modal otherwise, since Select2 attaches its dropdown to
    // <body> by default and the modal's focus-trap intercepts the keystrokes.
    $('#CreateGatePassModal .select2').select2({
        placeholder: 'Search by Employee ID or Name...',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#CreateGatePassModal')
    });

    // Date & Out Time always reflect the actual moment the Create modal is opened —
    // reset fresh every time (not just when empty), so reopening the modal later
    // doesn't leave a stale time from an earlier open.
    document.getElementById('CreateGatePassModal').addEventListener('shown.bs.modal', function () {
        var now = new Date(Date.now() - new Date().getTimezoneOffset() * 60000);
        document.getElementById('create_date').value = now.toISOString().slice(0, 10);
        document.getElementById('create_out_time').value = now.toISOString().slice(11, 16);
    });

    // Keep Duration <-> In Time synchronized against Out Time on the Edit modal
    // (datetime-local fields, so date and time move together).
    function wireDurationSync(outId, durationId, inId) {
        var out = document.getElementById(outId);
        var duration = document.getElementById(durationId);
        var inTime = document.getElementById(inId);
        var updating = false;

        function calcInFromDuration() {
            if (updating || !out.value || !duration.value) return;
            updating = true;
            var outDate = new Date(out.value);
            outDate.setMinutes(outDate.getMinutes() + parseInt(duration.value, 10));
            inTime.value = new Date(outDate.getTime() - outDate.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
            updating = false;
        }

        function calcDurationFromIn() {
            if (updating || !out.value || !inTime.value) return;
            updating = true;
            var diffMs = new Date(inTime.value) - new Date(out.value);
            if (diffMs > 0) {
                duration.value = Math.round(diffMs / 60000);
            }
            updating = false;
        }

        out.addEventListener('change', calcInFromDuration);
        duration.addEventListener('input', calcInFromDuration);
        inTime.addEventListener('change', calcDurationFromIn);
    }

    // Same idea, but for Create's plain time (HH:MM) fields, kept in sync against a
    // separate Date field rather than a combined datetime-local value.
    function wireTimeDurationSync(outId, durationId, inId) {
        var out = document.getElementById(outId);
        var duration = document.getElementById(durationId);
        var inTime = document.getElementById(inId);
        var updating = false;

        function toMinutes(hhmm) {
            var parts = hhmm.split(':');
            return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
        }
        function toHHMM(totalMinutes) {
            totalMinutes = ((totalMinutes % 1440) + 1440) % 1440;
            var h = Math.floor(totalMinutes / 60);
            var m = totalMinutes % 60;
            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
        }

        function calcInFromDuration() {
            if (updating || !out.value || !duration.value) return;
            updating = true;
            inTime.value = toHHMM(toMinutes(out.value) + parseInt(duration.value, 10));
            updating = false;
        }

        function calcDurationFromIn() {
            if (updating || !out.value || !inTime.value) return;
            updating = true;
            var diff = toMinutes(inTime.value) - toMinutes(out.value);
            if (diff <= 0) diff += 1440; // gate pass crosses midnight
            duration.value = diff;
            updating = false;
        }

        out.addEventListener('change', calcInFromDuration);
        duration.addEventListener('input', calcInFromDuration);
        inTime.addEventListener('change', calcDurationFromIn);
    }

    wireTimeDurationSync('create_out_time', 'create_duration', 'create_in_time');
    wireDurationSync('edit_out_time', 'edit_duration', 'edit_in_time');

    // Populate Edit modal
    document.querySelectorAll('[data-target="#EditGatePassModal"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('EditGatePassForm').action =
                '{{ url("admin/hr-center/gate-passes") }}/' + this.dataset.id;

            document.getElementById('edit_out_time').value = this.dataset.out;
            document.getElementById('edit_in_time').value = this.dataset.in;
            document.getElementById('edit_duration').value = this.dataset.duration;
            document.getElementById('edit_reason').value = this.dataset.reason;
            document.getElementById('edit_remarks').value = this.dataset.remarks;
            document.getElementById('edit_status').value = this.dataset.status;
        });
    });

    // Populate View modal
    document.querySelectorAll('[data-target="#ViewGatePassModal"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('view_pass_no').textContent = this.dataset.passNo;
            document.getElementById('view_employee').textContent = this.dataset.employee;
            document.getElementById('view_department').textContent = this.dataset.department;
            document.getElementById('view_out').textContent = this.dataset.out;
            document.getElementById('view_in').textContent = this.dataset.in;
            document.getElementById('view_duration').textContent = this.dataset.duration + ' min';
            document.getElementById('view_reason').textContent = this.dataset.reason;
            document.getElementById('view_remarks').textContent = this.dataset.remarks || '-';
            document.getElementById('view_status').textContent = this.dataset.status;
        });
    });

    @if(session('printed_gate_pass_id'))
        window.open('{{ route("hr-center.gate-passes.print", session("printed_gate_pass_id")) }}', '_blank');
    @endif
})();
</script>
@endpush
