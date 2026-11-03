<!doctype html>
<html><head><meta charset="utf-8"></head>
<body style="font-family:Arial,Helvetica,sans-serif;color:#222;margin:0;padding:16px;background:#fff;">
    <div style="max-width:900px;margin:0 auto;">
        <div style="text-align:center;border-bottom:2px solid #1a3c8c;padding-bottom:6px;margin-bottom:10px;">
            <div style="font-size:18px;font-weight:bold;">{{ class_exists(\App\Models\General::class) ? \App\Models\General::query()->value('title') : config('app.name') }}</div>
            <div style="font-size:15px;font-weight:bold;margin-top:6px;">Absent {{ $days }} Days in a Row — up to {{ $asOf->format('d M Y') }}</div>
        </div>
        <p style="font-size:13px;">These employees have no attendance punch on their last {{ $days }} working days (weekend, holidays and approved leave are not counted):</p>
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead>
                <tr style="background:#eef2f9;">
                    <th style="border:1px solid #ccc;padding:6px;">SL</th>
                    <th style="border:1px solid #ccc;padding:6px;">ID</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:left;">Name</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:left;">Designation</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:left;">Department / Section</th>
                    <th style="border:1px solid #ccc;padding:6px;">Mobile</th>
                    <th style="border:1px solid #ccc;padding:6px;">Absent Dates</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    @php $emp = $row['employee']; @endphp
                    <tr>
                        <td style="border:1px solid #ccc;padding:6px;text-align:center;">{{ $loop->iteration }}</td>
                        <td style="border:1px solid #ccc;padding:6px;text-align:center;">{{ $emp->employee_id }}</td>
                        <td style="border:1px solid #ccc;padding:6px;">{{ $emp->name }}</td>
                        <td style="border:1px solid #ccc;padding:6px;">{{ $emp->designation?->name }}</td>
                        <td style="border:1px solid #ccc;padding:6px;">{{ $emp->department?->name }}{{ $emp->section?->name ? ' / ' . $emp->section->name : '' }}</td>
                        <td style="border:1px solid #ccc;padding:6px;text-align:center;">{{ $emp->mobile }}</td>
                        <td style="border:1px solid #ccc;padding:6px;text-align:center;">{{ collect($row['dates'])->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M'))->implode(', ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p style="font-size:12px;margin-top:14px;"><a href="{{ route('hr-center.attendances.index', ['date' => $asOf->toDateString(), 'status' => 'Absent']) }}">Open Daily Attendance</a> · Sent {{ now()->format('d M Y h:i A') }}</p>
    </div>
</body></html>
