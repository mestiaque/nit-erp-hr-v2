<?php

namespace ME\Hr\Approvals;

use App\Approvals\BaseApprovalHandler;
use App\Models\Approval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use ME\Hr\Models\HrEmployeeLeave;
use ME\Hr\Services\HrApprovals;

/**
 * Module 'hr.leave' (registered by HrServiceProvider::registerApprovalModules()).
 *
 * A pending leave raises this request; approving / rejecting it here (Approvals page)
 * sets the leave's status. Approving on the employee's Leave page closes the request too
 * (HrApprovals::closeLeave()). Mail goes to every user with hr_leave_approval.approve.
 */
class LeaveApprovalHandler extends BaseApprovalHandler
{
    public function recipients(?Model $approvable, Approval $approval): array
    {
        return HrApprovals::usersWith(['hr_leave_approval.approve']);
    }

    public function onApproved(Approval $approval): void
    {
        $this->decide($approval, 'approved');
    }

    public function onRejected(Approval $approval): void
    {
        $this->decide($approval, 'rejected');
    }

    private function decide(Approval $approval, string $status): void
    {
        $leave = $approval->approvable;

        if (! $leave instanceof HrEmployeeLeave || ($leave->status ?: 'pending') !== 'pending') {
            return;
        }

        $leave->status = $status;
        $leave->save();
    }

    public function mailContent(?Model $approvable, Approval $approval): array
    {
        if (! $approvable instanceof HrEmployeeLeave) {
            return [];
        }

        $approvable->loadMissing(['employee.department', 'employee.designation', 'leaveType']);
        $employee = $approvable->employee;
        $date = fn ($d) => $d ? Carbon::parse($d)->format('d.m.Y') : null;

        return [
            'badge' => 'LEAVE APPLICATION',
            'number' => $approvable->application_no,
            'date' => $date($approvable->application_date),
            'meta' => [
                'Employee' => $employee ? trim("{$employee->name} ({$employee->employee_id})") : null,
                'Department' => $employee?->department?->name,
                'Designation' => $employee?->designation?->name,
                'Leave Type' => $approvable->leaveType
                    ? trim($approvable->leaveType->name.($approvable->leaveType->code ? " ({$approvable->leaveType->code})" : '')) : null,
                'From' => $date($approvable->leave_from),
                'To' => $date($approvable->leave_to),
                'Total Days' => $approvable->total_days !== null ? (string) (float) $approvable->total_days : null,
                'Requested by' => $approval->requestedBy?->name,
            ],
            'notes' => [
                'Reason' => strip_tags((string) $approvable->reason),
                'Remarks' => strip_tags((string) $approvable->remarks),
            ],
        ];
    }
}
