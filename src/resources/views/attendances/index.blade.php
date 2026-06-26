@extends(adminTheme().'layouts.app')

@section('title')
<title>{{ websiteTitle('Attendance List') }}</title>
@endsection

@section('contents')

<div class="flex-grow-1">
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Attendance List</h5>
            <a href="{{ route('hr-center.machine-logs.index') }}" class="btn btn-sm btn-outline-info">
                <i class="fa fa-list-alt mr-1"></i> Machine Log
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="row mb-3">
                    <div class="col">
                        <input type="text" name="employee" class="form-control" placeholder="Employee Name/ID/Email/Mobile" value="{{ request('employee') }}">
                    </div>
                    <div class="col">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="Present" @if(request('status')=='Present') selected @endif>Present</option>
                            <option value="Absent" @if(request('status')=='Absent') selected @endif>Absent</option>
                            <option value="Late" @if(request('status')=='Late') selected @endif>Late</option>
                            <option value="Punch Missing" @if(request('status')=='Punch Missing') selected @endif>Punch Missing</option>
                        </select>
                    </div>
                    <div class="col">
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col">
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Employee ID</th>
                        <th>Department</th>
                        <th>Section</th>
                        <th>Shift</th>
                        <td>Date</td>
                        <th>Day</th>
                        <th>In Time</th>
                        <th>Out Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                     @foreach($attendanceList as $row)
                         <tr>
                             <td>{{ $row['employee']->name ?? '' }}</td>
                             <td>{{ $row['employee']->employee_id ?? '' }}</td>
                             <td>{{ $row['employee']->department->name ?? '-' }}</td>
                             <td>{{ $row['employee']->section->name ?? '-' }}</td>
                             <td>{{ $row['shift']->name ?? '-' }}</td>
                             <td>{{ $row['date'] }}</td>
                             <td>{{ \Carbon\Carbon::parse($row['date'])->format('l') }}</td>
                             <td>{{ $row['attendance']->in_time ?? '-' }}</td>
                             <td>{{ $row['attendance']->out_time ?? '-' }}</td>
                             <td>{{ $row['status'] }}</td>
                             <td>
                                     <!-- Edit Button triggers unique modal -->
                                     <button type="button" class="btn btn-custom yellow" data-toggle="modal" data-target="#attendanceEditModal-{{ $row['employee']->id }}-{{ $row['date'] }}"><i class="fas fa-edit"></i></button>

                                     <!-- Modal for this row -->
                                     <div class="modal fade" id="attendanceEditModal-{{ $row['employee']->id }}-{{ $row['date'] }}" tabindex="-1" role="dialog" aria-labelledby="attendanceEditModalLabel-{{ $row['employee']->id }}-{{ $row['date'] }}" aria-hidden="true">
                                         <div class="modal-dialog" role="document">
                                             <div class="modal-content">
                                                 <div class="modal-header">
                                                     <h5 class="modal-title" id="attendanceEditModalLabel-{{ $row['employee']->id }}-{{ $row['date'] }}">Edit Attendance</h5>
                                                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                         <span aria-hidden="true">&times;</span>
                                                     </button>
                                                 </div>
                                                 <div class="modal-body">
                                                     <form method="POST" action="{{ route('hr-center.attendances.update', [$row['employee']->id, $row['date']]) }}">
                                                         @csrf
                                                         <!-- Preserve filter parameters -->
                                                         <input type="hidden" name="employee" value="{{ request('employee') }}">
                                                         <input type="hidden" name="status" value="{{ request('status') }}">
                                                         <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                                                         <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                                                         <div class="mb-2">
                                                                     <label class="form-label">In Time</label>
                                                                     <input type="time" name="in_time" class="form-control" value="{{ $row['attendance']->in_time ?? '' }}">
                                                         </div>
                                                         <div class="mb-2">
                                                                     <label class="form-label">Out Time</label>
                                                                     <input type="time" name="out_time" class="form-control" value="{{ $row['attendance']->out_time ?? '' }}">
                                                         </div>
                                                         <div class="mb-2">
                                                                     <label class="form-label">Remarks</label>
                                                                     <input type="text" name="remarks" class="form-control" value="{{ $row['attendance']->remarks ?? '' }}">
                                                         </div>
                                                         <button type="submit" class="btn btn-success">Save</button>
                                                     </form>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                             </td>
                         </tr>
                     @endforeach
                 </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
