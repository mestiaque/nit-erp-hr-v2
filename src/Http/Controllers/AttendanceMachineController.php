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
