@extends('admin.layouts.app')

@section('title')
<title>Shift Rostering</title>
@endsection

@php
    $dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
@endphp

@section('contents')
<div class="flex-grow-1 p-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{--
        ============================== ONE-OFF SHIFT ASSIGNMENTS ==============================
        Hidden for now (kept fully working underneath — routes, controller, modals below are
        all intact) so the page focuses on Auto Roster Rules only. Delete this comment block
        (and the matching one around the create/edit modals further down) to bring it back.

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Shift Assignments</h4>
                <small class="text-muted">Put one employee on a specific shift for a single date — a one-time override, does not repeat.</small>
            </div>
            <a href="javascript:void(0)" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createRosterModal">+ Assign Shift</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th>SL</th>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Section</th>
                            <th>Sub Section</th>
                            <th>Shift</th>
                            <th>Remarks</th>
                            <th style="width:90px;">Action</th>
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
                            <td><span class="badge badge-info">{{ $roster->shift->name ?? '-' }}</span></td>
                            <td>{{ $roster->remarks }}</td>
                            <td>
                                <a href="javascript:void(0)" class="btn-custom yellow" data-toggle="modal" data-target="#editRosterModal_{{ $roster->id }}" title="Edit"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('hr-center.rosters.destroy', $roster->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Delete this roster assignment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-custom danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No one-off shift assignments yet. Click "+ Assign Shift" to add one.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                {{ $rosters->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
    --}}

    {{-- ============================== AUTO ROSTER RULES ============================== --}}
    <div class="card roster-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0"><i class="fas fa-calendar-alt text-primary mr-2"></i>Auto Roster Rules</h4>
                <small class="text-muted">A standing, repeating rule per employee: a regular shift every day, except on one chosen weekday which rotates through a sequence of alternative shifts (in the order you add them), then back to the regular shift, then repeats.</small>
            </div>
            <a href="javascript:void(0)" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createRuleModal"><i class="fas fa-plus mr-1"></i>Add Auto Roster Rule</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th>Employee</th>
                            <th>Regular Shift</th>
                            <th>Start Date</th>
                            <th>Rotation Day</th>
                            <th>Rotation Sequence <small class="text-muted font-weight-normal">(on that day, repeats weekly)</small></th>
                            <th>Active</th>
                            <th style="width:90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $rule)
                        <tr>
                            <td>{{ $rule->employee->name ?? '-' }} <br><span>{{ $rule->employee->employee_id ?? '-' }}</span> </td>
                            <td><span class="badge badge-info">{{ $rule->primaryShift->name ?? '-' }}</span></td>
                            <td>{{ $rule->anchor_date }}</td>
                            <td>{{ $dayNames[$rule->day_of_week] ?? '-' }}</td>
                            <td>
                                @foreach($rule->alternateShifts as $alt)
                                    <span class="badge badge-warning">{{ $alt->shift->name ?? '-' }}</span>{{ !$loop->last ? ' → ' : '' }}
                                @endforeach
                                @if($rule->alternateShifts->isNotEmpty())
                                    → <span class="badge badge-info">{{ $rule->primaryShift->name ?? '-' }}</span> → <small class="text-muted">(repeats)</small>
                                @endif
                            </td>
                            <td>
                                @if($rule->is_active)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                <a href="javascript:void(0)" class="btn-custom yellow" data-toggle="modal" data-target="#editRuleModal_{{ $rule->id }}" title="Edit"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('hr-center.rosters.rules.destroy', $rule->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Delete this Auto Roster rule? This employee will fall back to their default shift.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-custom danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No Auto Roster rules configured yet. Click "Add Auto Roster Rule" to add one.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{--
    ============================== MODAL: Assign Shift (create + edit) ==============================
    Hidden along with the "Shift Assignments" section above — fully functional underneath,
    just not linked to from anywhere while that section is hidden.

<div class="modal fade" id="createRosterModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content roster-modal">
            <form method="post" action="{{ route('hr-center.rosters.store') }}">
                @csrf
                <div class="modal-header roster-modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-day mr-2"></i>Assign Shift (one date)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Employee</label>
                            <select name="employee_id" class="form-control select2-modal" required>
                                <option value="">-- Select --</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_id }} — {{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Shift</label>
                            <select name="shift_id" class="form-control" required>
                                <option value="">-- Select --</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Remarks <small class="text-muted">(optional)</small></label>
                            <input type="text" name="remarks" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($rosters as $roster)
<div class="modal fade" id="editRosterModal_{{ $roster->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content roster-modal">
            <form method="post" action="{{ route('hr-center.rosters.update', $roster->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header roster-modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-day mr-2"></i>Edit Shift Assignment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Employee</label>
                            <select name="employee_id" class="form-control select2-modal" required>
                                <option value="">-- Select --</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected($roster->employee_id == $employee->id)>{{ $employee->employee_id }} — {{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Shift</label>
                            <select name="shift_id" class="form-control" required>
                                <option value="">-- Select --</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" @selected($roster->shift_id == $shift->id)>{{ $shift->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" value="{{ \Illuminate\Support\Carbon::parse($roster->roster_date)->format('Y-m-d') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Remarks <small class="text-muted">(optional)</small></label>
                            <input type="text" name="remarks" class="form-control" value="{{ $roster->remarks }}">
                        </div>
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
--}}

{{-- ============================== MODAL: Auto Roster Rule (create) ============================== --}}
<div class="modal fade" id="createRuleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content roster-modal">
            <form method="post" action="{{ route('hr-center.rosters.store') }}">
                @csrf
                <input type="hidden" name="auto_roster" value="1">
                <div class="modal-header roster-modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-alt mr-2"></i>Add Auto Roster Rule</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="roster-section-label"><i class="fas fa-user mr-1"></i>Employee &amp; Regular Schedule</div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label class="w-100">Employee</label>
                            <select name="employee_id" class="form-control select2-modal w-100" required>
                                <option value="">-- Select --</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->employee_id }} — {{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Regular Shift <small class="text-muted">(every day except the rotation day below)</small></label>
                            <select name="shift_id" class="form-control" required>
                                <option value="">-- Select --</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label>Rotation Day <small class="text-muted">(which weekday rotates)</small></label>
                            <select name="day_of_week" class="form-control" required>
                                @foreach($dayNames as $i => $dayName)
                                    <option value="{{ $i }}" @selected($i == 5)>{{ $dayName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label>Start Date <small class="text-muted">(first rotation-day occurrence on/after this date)</small></label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                    </div>

                    <div class="roster-section-label mt-4"><i class="fas fa-sync-alt mr-1"></i>Alternative Shifts <span class="font-weight-normal text-muted text-lowercase">— rotation order, first added runs first</span></div>
                    <div class="alt-shifts-editor">
                        <div class="alt-shift-rows">
                            @include('hr::rosters.partials.alt-shift-row', ['shifts' => $shifts, 'selected' => null, 'order' => 1])
                        </div>
                        <button type="button" class="add-alt-shift-btn"><i class="fas fa-plus mr-1"></i>Add Alternative Shift</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================== MODAL: Auto Roster Rule (edit, one per row) ============================== --}}
@foreach($rules as $rule)
<div class="modal fade" id="editRuleModal_{{ $rule->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content roster-modal">
            <form method="post" action="{{ route('hr-center.rosters.update', $rule->id) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="auto_roster" value="1">
                <div class="modal-header roster-modal-header">
                    <h5 class="modal-title"><i class="fas fa-calendar-alt mr-2"></i>Edit Auto Roster Rule — {{ $rule->employee->name ?? '' }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="roster-section-label"><i class="fas fa-user mr-1"></i>Employee &amp; Regular Schedule</div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Employee</label>
                            <select name="employee_id" class="form-control select2-modal" required>
                                <option value="">-- Select --</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected($rule->employee_id == $employee->id)>{{ $employee->employee_id }} — {{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Regular Shift <small class="text-muted">(every day except the rotation day below)</small></label>
                            <select name="shift_id" class="form-control" required>
                                <option value="">-- Select --</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" @selected($rule->primary_shift_id == $shift->id)>{{ $shift->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label>Rotation Day <small class="text-muted">(which weekday rotates)</small></label>
                            <select name="day_of_week" class="form-control" required>
                                @foreach($dayNames as $i => $dayName)
                                    <option value="{{ $i }}" @selected($rule->day_of_week == $i)>{{ $dayName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6 mb-0">
                            <label>Start Date <small class="text-muted">(first rotation-day occurrence on/after this date)</small></label>
                            <input type="date" name="date" class="form-control" value="{{ \Illuminate\Support\Carbon::parse($rule->anchor_date)->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="roster-section-label mt-4"><i class="fas fa-sync-alt mr-1"></i>Alternative Shifts <span class="font-weight-normal text-muted text-lowercase">— rotation order, first added runs first</span></div>
                    <div class="alt-shifts-editor">
                        <div class="alt-shift-rows">
                            @forelse($rule->alternateShifts as $i => $alt)
                                @include('hr::rosters.partials.alt-shift-row', ['shifts' => $shifts, 'selected' => $alt->shift_id, 'order' => $i + 1])
                            @empty
                                @include('hr::rosters.partials.alt-shift-row', ['shifts' => $shifts, 'selected' => null, 'order' => 1])
                            @endforelse
                        </div>
                        <button type="button" class="add-alt-shift-btn"><i class="fas fa-plus mr-1"></i>Add Alternative Shift</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@push('css')
<style>
    .roster-card .card-header { background: #fff; border-bottom: 1px solid #eef0f5; }

    .roster-modal .modal-content { border: none; border-radius: 12px; overflow: hidden; box-shadow: 0 12px 40px rgba(0,0,0,.18); }
    .roster-modal-header {
        background: linear-gradient(135deg, #2c5cc5, #5b8def);
        border-bottom: none;
        padding: 16px 20px;
    }
    .roster-modal-header .modal-title { color: #fff; font-weight: 600; font-size: 17px; }
    .roster-modal-header .close { color: #fff; opacity: .85; text-shadow: none; }
    .roster-modal-header .close:hover { opacity: 1; }
    .roster-modal .modal-body { padding: 20px 22px; }
    .roster-modal .modal-footer { border-top: 1px solid #eef0f5; }
    .roster-modal label { font-size: 13px; font-weight: 600; color: #3a3f51; }
    .roster-modal .form-control { border-radius: 6px; }

    .roster-section-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #8892a6;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .alt-shifts-editor {
        background: #f7f9fc;
        border: 1px solid #e7eaf3;
        border-radius: 10px;
        padding: 14px 16px 12px;
    }
    .alt-shift-row {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        border: 1px solid #e7eaf3;
        border-radius: 8px;
        padding: 6px 8px 6px 6px;
        margin-bottom: 8px;
        transition: box-shadow .15s ease, border-color .15s ease;
    }
    .alt-shift-row:hover { border-color: #FFC107; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
    .alt-shift-row:last-child { margin-bottom: 0; }
    .alt-shift-badge {
        flex-shrink: 0;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #FFC107;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
    }
    .alt-shift-row select {
        flex: 1;
        border: 1px solid #dfe3ec;
        border-radius: 6px;
    }
    .remove-alt-shift-btn {
        flex-shrink: 0;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: none;
        background: #f9d0d9;
        color: #e1000a;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        cursor: pointer;
        transition: .2s;
    }
    .remove-alt-shift-btn:hover { background: #e1000a; color: #fff; }
    .add-alt-shift-btn {
        display: inline-flex;
        align-items: center;
        border: 1.5px dashed #FFC107;
        background: #fff8e6;
        color: #8a6300;
        border-radius: 6px;
        padding: 6px 14px;
        font-size: 12.5px;
        font-weight: 600;
        margin-top: 4px;
        cursor: pointer;
        transition: .2s;
    }
    .add-alt-shift-btn:hover { background: #FFC107; color: #fff; border-color: #FFC107; }
    .select2-container { width: 100% !important; }
</style>
@endpush

@push('js')
<script>
    // Bootstrap modals clip select2's dropdown oddly when it's rendered in
    // <body> (its default), so scope each dropdown to its own modal instead.
    $('.select2-modal').each(function () {
        $(this).select2({ dropdownParent: $(this).closest('.modal') });
    });

    (function () {
        function renumber(rowsContainer) {
            rowsContainer.querySelectorAll('.alt-shift-row').forEach(function (row, i) {
                row.querySelector('.alt-shift-badge').textContent = i + 1;
            });
        }

        document.addEventListener('click', function (e) {
            if (e.target.closest('.add-alt-shift-btn')) {
                var editor = e.target.closest('.alt-shifts-editor');
                var rows = editor.querySelector('.alt-shift-rows');
                var templateSelect = rows.querySelector('select');
                var optionsHtml = templateSelect ? templateSelect.innerHTML : '';

                var row = document.createElement('div');
                row.className = 'alt-shift-row';
                row.innerHTML =
                    '<span class="alt-shift-badge"></span>' +
                    '<select name="alt_shift_ids[]" class="form-control form-control-sm" required>' + optionsHtml + '</select>' +
                    '<button type="button" class="remove-alt-shift-btn" title="Remove this step"><i class="fas fa-times"></i></button>';
                rows.appendChild(row);
                renumber(rows);
            }

            if (e.target.closest('.remove-alt-shift-btn')) {
                var editor = e.target.closest('.alt-shifts-editor');
                var rows = editor.querySelector('.alt-shift-rows');
                if (rows.querySelectorAll('.alt-shift-row').length > 1) {
                    e.target.closest('.alt-shift-row').remove();
                    renumber(rows);
                }
            }
        });
    })();
</script>
@endpush
@endsection
