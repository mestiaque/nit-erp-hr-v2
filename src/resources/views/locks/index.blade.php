@extends('admin.layouts.app')

@section('title')
<title>Period Locking</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Period Locking</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <ul class="nav nav-tabs mb-3">
                @foreach(['increment' => 'Increment', 'attendance' => 'Attendance', 'salary' => 'Salary'] as $m => $label)
                    <li class="nav-item">
                        <a class="nav-link {{ $module === $m ? 'active' : '' }}" href="{{ route('hr-center.locks.index', ['module' => $m, 'year' => $year, 'month' => $month]) }}">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>

            <form method="GET" action="{{ route('hr-center.locks.index') }}" class="form-row align-items-end mb-3">
                <input type="hidden" name="module" value="{{ $module }}">
                <div class="form-group col-md-2">
                    <label>Year</label>
                    <input type="number" name="year" value="{{ $year }}" class="form-control">
                </div>
                <div class="form-group col-md-3">
                    <label>Month</label>
                    <select name="month" class="form-control">
                        @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $name)
                            <option value="{{ $i + 1 }}" @selected($month === $i + 1)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
            </form>

            <p class="text-muted">
                Locked = the only data reports use for this period; anything added or edited after locking
                stays a draft and never shows in a report until it (or the whole period) is locked too.
            </p>

            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Scope</th>
                            <th>Status</th>
                            <th>Locked At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-light">
                            <td><strong>Whole Factory (all departments)</strong></td>
                            <td>
                                @if($wholeLocked)
                                    <span class="badge badge-success">Locked</span>
                                @else
                                    <span class="badge badge-secondary">Unlocked</span>
                                @endif
                            </td>
                            <td>{{ $wholeLockAt ? \Carbon\Carbon::parse($wholeLockAt)->format('d-M-Y h:i A') : '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('hr-center.locks.toggle') }}" onsubmit="return confirm('{{ $wholeLocked ? 'Unlock' : 'Lock' }} the whole factory for this period?')">
                                    @csrf
                                    <input type="hidden" name="module" value="{{ $module }}">
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    <input type="hidden" name="action" value="{{ $wholeLocked ? 'unlock' : 'lock' }}">
                                    <button type="submit" class="btn btn-sm {{ $wholeLocked ? 'btn-outline-secondary' : 'btn-success' }}">{{ $wholeLocked ? 'Unlock' : 'Lock' }}</button>
                                </form>
                            </td>
                        </tr>
                        @foreach($rows as $row)
                            <tr>
                                <td>{{ $row['department']->name }}</td>
                                <td>
                                    @if($row['is_locked'])
                                        <span class="badge badge-success">Locked</span>
                                    @else
                                        <span class="badge badge-secondary">Unlocked</span>
                                    @endif
                                </td>
                                <td>{{ $row['locked_at'] ? \Carbon\Carbon::parse($row['locked_at'])->format('d-M-Y h:i A') : '-' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('hr-center.locks.toggle') }}" onsubmit="return confirm('{{ $row['is_locked'] ? 'Unlock' : 'Lock' }} {{ $row['department']->name }} for this period?')">
                                        @csrf
                                        <input type="hidden" name="module" value="{{ $module }}">
                                        <input type="hidden" name="year" value="{{ $year }}">
                                        <input type="hidden" name="month" value="{{ $month }}">
                                        <input type="hidden" name="department_id" value="{{ $row['department']->id }}">
                                        <input type="hidden" name="action" value="{{ $row['is_locked'] ? 'unlock' : 'lock' }}">
                                        <button type="submit" class="btn btn-sm {{ $row['is_locked'] ? 'btn-outline-secondary' : 'btn-success' }}">{{ $row['is_locked'] ? 'Unlock' : 'Lock' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
