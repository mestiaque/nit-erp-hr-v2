<?php

namespace ME\Hr\Services;

use App\Models\Approval;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use ME\Hr\Models\HrAdvanceApplication;
use ME\Hr\Models\HrEmployeeLeave;

/**
 * HR's link to the host app's central approvals (Approvals page + approval mail).
 * Optional: does nothing when the host has no ApprovalService, and a mail failure never
 * blocks saving the leave / application itself.
 */
class HrApprovals
{
    public const LEAVE = 'hr.leave';
    public const ADVANCE = 'hr.advance_application';

    public static function enabled(): bool
    {
        return class_exists(ApprovalService::class) && class_exists(Approval::class);
    }

    /** Emails of users holding any of these permissions. */
    public static function usersWith(array $permissions): array
    {
        return User::all()
            ->filter(fn (User $user) => collect($permissions)->contains(fn ($p) => $user->hasPermission($p)))
            ->pluck('email')
            ->filter()
            ->values()
            ->all();
    }

    public static function requestLeave(HrEmployeeLeave $leave): void
    {
        if (($leave->status ?: 'pending') !== 'pending' || self::pending(self::LEAVE, $leave)) {
            return;
        }

        $leave->loadMissing(['employee', 'leaveType']);
        $employee = $leave->employee;

        self::request([
            'module' => self::LEAVE,
            'approvable' => $leave,
            'title' => 'Leave Approval - '.($employee ? "{$employee->name} ({$employee->employee_id})" : "Leave #{$leave->id}"),
            'description' => ($leave->leaveType?->name ?? 'Leave').' for '.((float) $leave->total_days).' day(s): '
                .Carbon::parse($leave->leave_from)->format('d.m.Y').' to '.Carbon::parse($leave->leave_to)->format('d.m.Y').'.',
            'route_name' => 'hr-center.employees.leaves.page',
            'route_params' => [$leave->employee_id],
        ]);
    }

    public static function requestAdvance(HrAdvanceApplication $application): void
    {
        $application->loadMissing('employee');
        $employee = $application->employee;

        self::request([
            'module' => self::ADVANCE,
            'approvable' => $application,
            'title' => 'Salary Advance Approval - '.($employee ? "{$employee->name} ({$employee->employee_id})" : "Application #{$application->id}"),
            'description' => 'Salary advance of Tk '.number_format((float) $application->amount, 2).' applied on '
                .$application->application_date?->format('d.m.Y').'.',
            'route_name' => 'hr-center.employees.earnings.page',
            'route_params' => [$application->employee_id],
        ]);
    }

    /** The leave was approved / rejected on its own page: close its pending request the same way. */
    public static function closeLeave(HrEmployeeLeave $leave): void
    {
        if (! $approval = self::pending(self::LEAVE, $leave)) {
            return;
        }

        try {
            $leave->status === 'rejected'
                ? app(ApprovalService::class)->reject($approval, auth()->user(), 'Rejected from the employee Leave page.')
                : app(ApprovalService::class)->approve($approval, auth()->user(), 'Approved from the employee Leave page.');
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** The record was deleted: its pending request no longer needs an answer. */
    public static function cancel(string $module, Model $model): void
    {
        self::pending($module, $model)?->update(['status' => 'cancelled']);
    }

    /** Latest approval per record id (for status badges). */
    public static function latestFor(string $module, string $class, array $ids): Collection
    {
        if (! self::enabled() || $ids === []) {
            return collect();
        }

        return Approval::where('module', $module)->where('approvable_type', $class)->whereIn('approvable_id', $ids)
            ->orderBy('id')->get()->keyBy('approvable_id');
    }

    private static function pending(string $module, Model $model): ?Approval
    {
        if (! self::enabled()) {
            return null;
        }

        return Approval::where('module', $module)
            ->where('approvable_type', get_class($model))
            ->where('approvable_id', $model->getKey())
            ->where('status', 'pending')
            ->first();
    }

    private static function request(array $data): void
    {
        if (! self::enabled()) {
            return;
        }

        try {
            app(ApprovalService::class)->request($data + ['requested_by' => auth()->id()]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
