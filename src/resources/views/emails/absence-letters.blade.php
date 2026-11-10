<!doctype html>
<html><head><meta charset="utf-8"></head>
<body style="font-family:Arial,Helvetica,sans-serif;color:#222;margin:0;padding:16px;background:#fff;">
    <div style="max-width:900px;margin:0 auto;">
        <div style="text-align:center;border-bottom:2px solid #1a3c8c;padding-bottom:6px;margin-bottom:10px;">
            <div style="font-size:18px;font-weight:bold;">{{ class_exists(\App\Models\General::class) ? \App\Models\General::query()->value('title') : config('app.name') }}</div>
            <div style="font-size:15px;font-weight:bold;margin-top:6px;">Absence Letters Issued — {{ $today->format('d M Y') }}</div>
        </div>
        <p style="font-size:13px;">Bangladesh Labour Act 2006, section 27(3A). A letter is issued after {{ $days }} days absent in a row, and again {{ $days }} days after each letter. With the 3rd letter the employee is marked <strong>Resign</strong> from the first day absent. Print each letter and send it by registered post.</p>
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead>
                <tr style="background:#eef2f9;">
                    <th style="border:1px solid #ccc;padding:6px;">SL</th>
                    <th style="border:1px solid #ccc;padding:6px;">ID</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:left;">Name</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:left;">Designation</th>
                    <th style="border:1px solid #ccc;padding:6px;text-align:left;">Department / Section</th>
                    <th style="border:1px solid #ccc;padding:6px;">Absent From</th>
                    <th style="border:1px solid #ccc;padding:6px;">Letter</th>
                    <th style="border:1px solid #ccc;padding:6px;">Status</th>
                    <th style="border:1px solid #ccc;padding:6px;">Print</th>
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
                        <td style="border:1px solid #ccc;padding:6px;text-align:center;">{{ \Carbon\Carbon::parse($row['absent_from'])->format('d M Y') }}</td>
                        <td style="border:1px solid #ccc;padding:6px;text-align:center;font-weight:bold;">{{ [1 => '1st', 2 => '2nd', 3 => '3rd'][$row['letter']] }}</td>
                        <td style="border:1px solid #ccc;padding:6px;text-align:center;">{!! $row['resigned'] ? '<span style="color:#b91c1c;font-weight:bold;">Resign</span>' : 'Absent' !!}</td>
                        <td style="border:1px solid #ccc;padding:6px;text-align:center;"><a href="{{ route('hr-center.employees.final-settlement.print.show', ['employee' => $emp->id, 'letter' => $row['letter']]) }}">Print letter</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p style="font-size:12px;margin-top:14px;">Sent {{ now()->format('d M Y h:i A') }}</p>
    </div>
</body></html>
