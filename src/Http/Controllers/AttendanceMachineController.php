<?php

namespace ME\Hr\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use ME\Hr\Models\HrAttendance;
use ME\Hr\Models\HrAttendanceMachineLog;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrShift;

class AttendanceMachineController extends Controller
{
    public function fetchEmployees(): JsonResponse
    {
        $employees = HrEmployee::where('status', true)
            // ->whereIn('employee_id', ['A00004', 'A00005'])
            ->naturalOrderById()
            ->get(['id', 'employee_id', 'name'])
            ->map(fn($e) => [
                'uid'         => $e->employee_id,
                'employee_id' => $e->employee_id,
                'name'        => $e->name,
                'privilege'   => 0,
                'password'    => '',
                'card'        => '',
            ]);

        return response()->json(['data' => $employees]);
    }

    public function receiveData(Request $request): JsonResponse
    {
        try {
            Log::info('ZKTeco Data Received', ['payload' => $request->all()]);

            $userId     = $request->input('user_id');
            $timestamp  = $request->input('time') ?? $request->input('timestamp');
            $sn         = $request->input('device_sn');
            $verifyType = $request->input('type_name');
            $typeCode   = $request->input('type_code');

            if (!$userId || !$timestamp) {
                return response()->json(['status' => 'error', 'message' => 'Invalid Data'], 400);
            }

            $this->saveMachineLog($userId, $timestamp, $sn, $verifyType, $typeCode);
            $this->saveAttendance($userId, $timestamp, $sn, $verifyType);

            return response()->json(['status' => 'success', 'message' => 'Attendance Processed']);

        } catch (\Throwable $e) {
            Log::error('ZKTeco receiveData error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function receiveBulkData(Request $request): JsonResponse
    {
        try {
            $data = $request->input('data');
            if (!is_array($data)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid Data Format'], 400);
            }

            foreach ($data as $entry) {
                $userId     = $entry['user_id'] ?? null;
                $timestamp  = $entry['time'] ?? $entry['timestamp'] ?? null;
                $sn         = $entry['device_sn'] ?? null;
                $verifyType = $entry['type_name'] ?? null;
                $typeCode   = $entry['type_code'] ?? null;

                if ($userId && $timestamp) {
                    $this->saveMachineLog($userId, $timestamp, $sn, $verifyType, $typeCode);
                    $this->saveAttendance($userId, $timestamp, $sn, $verifyType);
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Bulk Attendance Processed']);

        } catch (\Throwable $e) {
            Log::error('ZKTeco receiveBulkData error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Receive ADMS-format bulk attendance records.
     *
     * Expected payload:
     * {
     *   "records": [
     *     { "deviceId", "employeeId", "time", "status", "verify",
     *       "verifyMethod", "workCode", "source", "receivedAt", "id", ... }
     *   ]
     * }
     *
     * All records are written to hr_attendance_machine_logs regardless of
     * whether the employee is found. If the employee IS found, attendance is
     * upserted for the date with the standard in/out-time logic:
     *   - earliest punch  → in_time
     *   - any later punch → out_time (always keeps the latest)
     */
    public function logs(Request $request)
    {
        $query = HrAttendanceMachineLog::query()->orderByDesc('log_time');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%$search%")
                  ->orWhere('device_sn', 'like', "%$search%");
            });
        }
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('log_time', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('log_time', '<=', $dateTo);
        }
        if ($source = $request->input('source')) {
            $query->where('source', $source);
        }
        if ($verifyMethod = $request->input('verify_method')) {
            $query->where('type_name', $verifyMethod);
        }

        $logs = $query->paginate(50)->withQueryString();

        // Map employee_id (device string) → hr_employees record for display
        $employeeIds = $logs->pluck('employee_id')->unique()->filter();
        $employees   = HrEmployee::whereIn('employee_id', $employeeIds)
                          ->get(['id', 'employee_id', 'name'])
                          ->keyBy('employee_id');

        $sources       = HrAttendanceMachineLog::select('source')->distinct()->whereNotNull('source')->pluck('source');
        $verifyMethods = HrAttendanceMachineLog::select('type_name')->distinct()->whereNotNull('type_name')->pluck('type_name');

        return view('hr::attendances.machineLog', compact('logs', 'employees', 'sources', 'verifyMethods'));
    }

    public function receiveAdmsRecords(Request $request): JsonResponse
    {
        try {
            $records = $request->input('records');

            if (!is_array($records) || empty($records)) {
                return response()->json(['status' => 'error', 'message' => 'No records provided.'], 400);
            }

            $loggedCount     = 0;
            $attendanceCount = 0;
            $skipped         = 0;

            foreach ($records as $record) {
                $employeeId = (string) ($record['employeeId'] ?? '');
                $timeStr    = $record['time'] ?? null;

                // Every record goes to the log regardless of validity
                if ($employeeId && $timeStr) {
                    $this->saveMachineLogAdms($record);
                    $loggedCount++;
                } else {
                    $skipped++;
                    continue;
                }

                // Try to process attendance if employee exists
                if ($this->processAdmsAttendance($record)) {
                    $attendanceCount++;
                }
            }

            Log::info('ADMS bulk received', [
                'total'      => count($records),
                'logged'     => $loggedCount,
                'attendance' => $attendanceCount,
                'skipped'    => $skipped,
            ]);

            return response()->json([
                'status'           => 'success',
                'message'          => 'ADMS records processed.',
                'total'            => count($records),
                'logged'           => $loggedCount,
                'attendance_synced'=> $attendanceCount,
                'skipped'          => $skipped,
            ]);

        } catch (\Throwable $e) {
            Log::error('receiveAdmsRecords error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function saveMachineLogAdms(array $record): void
    {
        try {
            HrAttendanceMachineLog::create([
                'device_sn'   => $record['deviceId']     ?? null,
                'employee_id' => $record['employeeId']   ?? null,
                'log_time'    => $record['time']          ?? null,
                'type_code'   => $record['status']        ?? null,
                'type_name'   => $record['verifyMethod']  ?? null,
                'source'      => $record['source']        ?? null,
                'external_id' => isset($record['id']) ? (string) $record['id'] : null,
                'work_code'   => $record['workCode']      ?? null,
                'received_at' => isset($record['receivedAt'])
                    ? Carbon::parse($record['receivedAt'])->toDateTimeString()
                    : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('saveMachineLogAdms failed: ' . $e->getMessage(), ['record' => $record]);
        }
    }

    private function processAdmsAttendance(array $record): bool
    {
        try {
            $employeeId = (string) ($record['employeeId'] ?? '');
            $timeStr    = $record['time'] ?? null;

            if (!$employeeId || !$timeStr) {
                return false;
            }

            $employee = HrEmployee::with('shift')->where('employee_id', $employeeId)->first();

            if (!$employee) {
                Log::info("ADMS: employee_id=$employeeId not found in users, log saved only.");
                return false;
            }

            $time  = Carbon::parse($timeStr, 'Asia/Dhaka');
            $shift = $employee->shift ?? null;

            // Resolve active shift from roster if employee has no default shift
            if (!$shift) {
                $shift = $this->resolveShiftFromRoster($employee, $time->toDateString());
            }

            $attendance = HrAttendance::where('employee_id', $employee->id)
                ->whereDate('date', $time->toDateString())
                ->first();

            if (!$attendance) {
                $attendance              = new HrAttendance();
                $attendance->employee_id = $employee->id;
                $attendance->date        = $time->toDateString();
                $attendance->device_sn   = $record['deviceId'] ?? null;
                $attendance->via         = 'machine';
                $attendance->verify_type = $record['verifyMethod'] ?? null;
            }

            // Earliest punch = in_time
            $currentIn = $this->toCarbon($attendance->date, $attendance->in_time);
            if (!$currentIn || $time->lt($currentIn)) {
                $attendance->in_time = $time->format('H:i:s');
                $currentIn           = $time;
            }

            // Any later punch updates out_time
            $currentOut = $this->toCarbon($attendance->date, $attendance->out_time);
            if ($currentIn && $time->gt($currentIn) && (!$currentOut || $time->gt($currentOut))) {
                $attendance->out_time = $time->format('H:i:s');
                $currentOut           = $time;
            }

            $attendance->status = $this->resolveStatus($shift, $currentIn);

            if ($currentIn && $currentOut) {
                $attendance->total_working_minute = (int) $currentIn->diffInMinutes($currentOut);
                $attendance->total_ot_minute      = $this->resolveOvertimeMinutes($shift, $currentIn, $currentOut);
            }

            $attendance->save();

            return true;

        } catch (\Throwable $e) {
            Log::error('processAdmsAttendance failed: ' . $e->getMessage(), ['record' => $record]);
            return false;
        }
    }

    /**
     * Try to find the employee's shift from the shift roster for a given date.
     * Returns null if no roster entry exists (falls back to no-shift logic).
     */
    private function resolveShiftFromRoster(HrEmployee $employee, string $date): ?HrShift
    {
        // Check if HrShiftRosterEmployee model exists
        if (!class_exists(\ME\Hr\Models\HrShiftRosterEmployee::class)) {
            return null;
        }

        $rosterEntry = \ME\Hr\Models\HrShiftRosterEmployee::where('employee_id', $employee->id)
            ->whereDate('date', $date)
            ->first();

        if (!$rosterEntry || !$rosterEntry->shift_id) {
            return null;
        }

        return HrShift::find($rosterEntry->shift_id);
    }

    public function import()
    {
        return view('hr::attendances.logImport');
    }

    public function importAction(Request $request)
    {
        $request->validate([
            'attendance_file' => 'required|file|mimes:txt,csv,dat|max:5120',
        ]);

        $fileData = file(
            $request->file('attendance_file')->getRealPath(),
            FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        $count = 0;
        foreach ($fileData as $line) {
            $parts = array_values(array_filter(preg_split('/\s+|,/', trim($line)), fn($p) => $p !== ''));

            if (count($parts) >= 2) {
                $userId    = $parts[0];
                $timestamp = count($parts) >= 3 ? ($parts[1] . ' ' . $parts[2]) : $parts[1];
                $sn        = $parts[3] ?? null;
                $typeCode  = $parts[4] ?? null;

                $this->saveMachineLog($userId, $timestamp, $sn, 'Manual_Import', $typeCode);
                $this->saveAttendance($userId, $timestamp, $sn, 'Manual_Import');
                $count++;
            }
        }

        return redirect()->back()->with('success', "$count টি ডাটা সফলভাবে প্রসেস করা হয়েছে।");
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function saveMachineLog(string $userId, string $timestamp, ?string $sn, ?string $verifyType, ?string $typeCode = null): void
    {
        try {
            HrAttendanceMachineLog::create([
                'device_sn'   => $sn,
                'employee_id' => $userId,
                'log_time'    => $timestamp,
                'type_name'   => $verifyType,
                'type_code'   => $typeCode,
            ]);
        } catch (\Throwable $e) {
            Log::error("saveMachineLog failed for $userId: " . $e->getMessage());
        }
    }

    private function saveAttendance(string $userId, string $timestamp, ?string $sn, ?string $verifyType): void
    {
        try {
            $employee = HrEmployee::with('shift')->where('employee_id', $userId)->first();

            if (!$employee) {
                Log::warning("Attendance skipped: employee_id=$userId not found. SN=$sn");
                return;
            }

            $time  = Carbon::parse($timestamp, 'Asia/Dhaka');
            $shift = $employee->shift;

            $attendance = HrAttendance::where('employee_id', $employee->id)
                ->whereDate('date', $time->toDateString())
                ->first();

            if (!$attendance) {
                $attendance                = new HrAttendance();
                $attendance->employee_id   = $employee->id;
                $attendance->date          = $time->toDateString();
                $attendance->device_sn     = $sn;
                $attendance->via           = 'machine';
                $attendance->verify_type   = $verifyType;
            }

            // Update in_time (keep earliest punch)
            $currentIn = $this->toCarbon($attendance->date, $attendance->in_time);
            if (!$currentIn || $time->lt($currentIn)) {
                $attendance->in_time = $time->format('H:i:s');
                $currentIn = $time;
            }

            // Update out_time (keep latest punch after in_time)
            $currentOut = $this->toCarbon($attendance->date, $attendance->out_time);
            if ($currentIn && $time->gt($currentIn) && (!$currentOut || $time->gt($currentOut))) {
                $attendance->out_time = $time->format('H:i:s');
                $currentOut = $time;
            }

            // Status: Present or Late based on shift start + late_allow_time
            $attendance->status = $this->resolveStatus($shift, $currentIn);

            // Working minutes
            if ($currentIn && $currentOut) {
                $attendance->total_working_minute = (int) $currentIn->diffInMinutes($currentOut);
                $attendance->total_ot_minute      = $this->resolveOvertimeMinutes($shift, $currentIn, $currentOut);
            }

            $attendance->save();

            Log::info("Attendance synced: employee=$userId date={$attendance->date} status={$attendance->status}");

        } catch (\Throwable $e) {
            Log::error("saveAttendance failed for $userId: " . $e->getMessage());
        }
    }

    private function resolveStatus(?HrShift $shift, ?Carbon $inTime): string
    {
        if (!$inTime || !$shift || !$shift->start_time) {
            return 'Present';
        }

        // late_allow_time is the absolute threshold time (e.g. 09:15:00); use it directly if set
        $lateThreshold = $shift->late_allow_time
            ? Carbon::parse($inTime->toDateString() . ' ' . $shift->late_allow_time, 'Asia/Dhaka')
            : Carbon::parse($inTime->toDateString() . ' ' . $shift->start_time, 'Asia/Dhaka');

        return $inTime->gt($lateThreshold) ? 'Late' : 'Present';
    }

    private function resolveOvertimeMinutes(?HrShift $shift, Carbon $inTime, Carbon $outTime): int
    {
        if (!$shift || !$shift->end_time) {
            return 0;
        }

        $shiftEnd = Carbon::parse($inTime->toDateString() . ' ' . $shift->end_time, 'Asia/Dhaka');

        if ($outTime->lte($shiftEnd)) {
            return 0;
        }

        // Cap OT at out_time_start if defined
        $otCap = $shift->out_time_start
            ? Carbon::parse($inTime->toDateString() . ' ' . $shift->out_time_start, 'Asia/Dhaka')
            : $outTime;

        $effectiveOut = $outTime->lt($otCap) ? $outTime : $otCap;

        return (int) $shiftEnd->diffInMinutes($effectiveOut);
    }

    private function toCarbon(?string $date, $timeValue): ?Carbon
    {
        if (!$timeValue) {
            return null;
        }

        $baseDate = $date ?: Carbon::today('Asia/Dhaka')->toDateString();
        $timePart = $timeValue instanceof Carbon
            ? $timeValue->format('H:i:s')
            : Carbon::parse($timeValue, 'Asia/Dhaka')->format('H:i:s');

        return Carbon::parse($baseDate . ' ' . $timePart, 'Asia/Dhaka');
    }
}
