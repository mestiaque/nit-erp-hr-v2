@extends('admin.layouts.app')

@section('title')
<title>Assign Shift Roster (Bulk)</title>
@endsection

@section('contents')
<div class="flex-grow-1">
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Assign Shift Roster — Bulk</h4>
            <a href="{{ route('hr-center.rosters.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
        </div>
        <div class="card-body">
            {{-- Filter (GET, reloads this page with the filtered employee list) --}}
            <form method="GET" action="{{ route('hr-center.rosters.assign') }}" class="mb-3">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-3">
                        <label>Department</label>
                        <select name="department_id" class="form-control">
                            <option value="">All</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Section</label>
                        <select name="section_id" class="form-control">
                            <option value="">All</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" @selected((string) request('section_id') === (string) $section->id)>{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Sub Section</label>
                        <select name="sub_section_id" class="form-control">
                            <option value="">All</option>
                            @foreach($subSections as $subSection)
                                <option value="{{ $subSection->id }}" @selected((string) request('sub_section_id') === (string) $subSection->id)>{{ $subSection->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <button type="submit" class="btn btn-secondary w-100"><i class="fa fa-filter mr-1"></i> Filter</button>
                    </div>
                </div>
            </form>

            @if(request('sub_section_id') && $defaultShiftId)
                <div class="alert alert-info py-2" style="font-size:.85rem;">
                    This sub section's default Roster Shift has been pre-selected below — you can still change it.
                </div>
            @endif

            {{-- Bulk assignment form --}}
            <form method="POST" action="{{ route('hr-center.rosters.bulk-store') }}">
                @csrf

                <div class="form-row align-items-end mb-2">
                    <div class="form-group col-md-3 mb-0">
                        <label>Date</label>
                        <input type="date" name="date" id="rosterDate" class="form-control" value="{{ old('date') }}" required>
                    </div>
                    <div class="form-group col-md-3 mb-0">
                        <div class="custom-control custom-checkbox mt-4">
                            <input type="checkbox" class="custom-control-input" id="autoFriday">
                            <label class="custom-control-label" for="autoFriday">Auto Friday (next upcoming Friday)</label>
                        </div>
                    </div>
                    <div class="form-group col-md-3 mb-0">
                        <label>Shift</label>
                        <select name="shift_id" class="form-control" required>
                            <option value="">-- Select --</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" @selected((string) old('shift_id', $defaultShiftId) === (string) $shift->id)>{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 mb-0">
                        <label>Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}">
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:36px;"><input type="checkbox" id="selectAllEmployees"></th>
                                <th>Emp ID</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Section</th>
                                <th>Sub Section</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                            <tr>
                                <td><input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" class="employee-checkbox"></td>
                                <td>{{ $employee->employee_id }}</td>
                                <td>{{ $employee->name }}</td>
                                <td>{{ $employee->department->name ?? '-' }}</td>
                                <td>{{ $employee->section->name ?? '-' }}</td>
                                <td>{{ $employee->subSection->name ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center">No employees found for this filter.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <button type="submit" class="btn btn-primary" @disabled($employees->isEmpty())>Assign to Selected</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('selectAllEmployees').addEventListener('change', function () {
        document.querySelectorAll('.employee-checkbox').forEach(function (cb) {
            cb.checked = this.checked;
        }.bind(this));
    });

    document.getElementById('autoFriday').addEventListener('change', function () {
        var dateInput = document.getElementById('rosterDate');
        if (this.checked) {
            var today = new Date();
            var day = today.getDay(); // 0=Sun..6=Sat, Friday=5
            var daysUntilFriday = (5 - day + 7) % 7;
            if (daysUntilFriday === 0) {
                daysUntilFriday = 7; // "upcoming" Friday — if today IS Friday, jump to next week
            }
            var nextFriday = new Date(today);
            nextFriday.setDate(today.getDate() + daysUntilFriday);
            var yyyy = nextFriday.getFullYear();
            var mm = String(nextFriday.getMonth() + 1).padStart(2, '0');
            var dd = String(nextFriday.getDate()).padStart(2, '0');
            dateInput.value = yyyy + '-' + mm + '-' + dd;
            // readonly, not disabled: a disabled input is excluded from form submission
            // entirely, which would silently drop `date` from the POST and fail validation.
            dateInput.readOnly = true;
        } else {
            dateInput.readOnly = false;
        }
    });
</script>
@endsection
