
@extends('printMaster2')

@section('title', $reportTitle)


@section('contents')


<div class="container mt-4">
    <div class="text-center mb-4">
        <h3>{{ $reportTitle }}</h3>
    </div>

    {{-- Employee Summary Table --}}
    <div class="mb-4">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Name</th>
                    <th>Employee ID</th>
                    <th>Designation</th>
                    <th>Section</th>
                    <th>Department</th>
                    <th>Sub Section</th>
                    <th>Working Place</th>
                    <th>Shift</th>
                    <th>Classification</th>
                    <th>Join Date</th>
                    <th>Block/Line</th>
                    <th>Salary Type</th>
                    <th>Employee Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ is_array($row) ? ($row['name'] ?? null) : data_get($row, 'name') }}</td>
                        <td>{{ is_array($row) ? ($row['employee_id'] ?? null) : data_get($row, 'employee_id') }}</td>
                        <td>{{ is_array($row) ? ($row['designation_name'] ?? null) : data_get($row, 'designation_name') }}</td>
                        <td>{{ is_array($row) ? ($row['section_name'] ?? null) : data_get($row, 'section_name') }}</td>
                        <td>{{ is_array($row) ? ($row['department_name'] ?? null) : data_get($row, 'department_name') }}</td>
                        <td>{{ is_array($row) ? ($row['sub_section_name'] ?? null) : data_get($row, 'sub_section_name') }}</td>
                        <td>{{ is_array($row) ? ($row['working_place_name'] ?? null) : data_get($row, 'working_place_name') }}</td>
                        <td>{{ is_array($row) ? ($row['shift_name'] ?? null) : data_get($row, 'shift_name') }}</td>
                        <td>{{ is_array($row) ? ($row['classification'] ?? null) : data_get($row, 'classification') }}</td>
                        <td>{{ is_array($row) ? ($row['join_date'] ?? null) : data_get($row, 'join_date') }}</td>
                        <td>{{ is_array($row) ? ($row['block_line'] ?? null) : data_get($row, 'block_line') }}</td>
                        <td>{{ is_array($row) ? ($row['salary_type'] ?? null) : data_get($row, 'salary_type') }}</td>
                        <td>{{ is_array($row) ? ($row['employee_status'] ?? null) : data_get($row, 'employee_status') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center">No data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Attendance Details Table (if available) --}}
    @if(isset($attendanceDetails) && is_array($attendanceDetails) && count($attendanceDetails))
        <div class="mb-4">
            <h5>Attendance Details</h5>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Day Name</th>
                        <th>In Time</th>
                        <th>Out Time</th>
                        <th>OT Hours</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendanceDetails as $i => $att)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $att['date'] ?? '' }}</td>
                            <td>{{ $att['shift'] ?? '' }}</td>
                            <td>{{ $att['day_name'] ?? '' }}</td>
                            <td>{{ $att['in_time'] ?? '' }}</td>
                            <td>{{ $att['out_time'] ?? '' }}</td>
                            <td>{{ $att['ot_hours'] ?? '' }}</td>
                            <td>{{ $att['status'] ?? '' }}</td>
                            <td>{{ $att['remarks'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
