<?php

namespace ME\Hr\Approvals;

use App\Approvals\BaseApprovalHandler;
use App\Models\Approval;
use Illuminate\Database\Eloquent\Model;
use ME\Hr\Models\HrAdvanceApplication;
use ME\Hr\Services\HrApprovals;

/**
 * Module 'hr.advance_application' (registered by HrServiceProvider::registerApprovalModules()).
 *
 * A saved salary advance application asks HR and Accounts to approve it: mail goes to every
 * user with hr_advance_application.approve (HR) or ac_expense.approve (Accounts, who pay it).
 * The decision is kept on the approval itself - the application stays record + print only;
 * the advance is still paid and posted through an acc-sfl Salary Advance expense.
 */
class AdvanceApplicationApprovalHandler extends BaseApprovalHandler
{
    public function recipients(?Model $approvable, Approval $approval): array
    {
        return HrApprovals::usersWith(['hr_advance_application.approve', 'ac_expense.approve']);
    }

    public function mailContent(?Model $approvable, Approval $approval): array
    {
        if (! $approvable instanceof HrAdvanceApplication) {
            return [];
        }

        $approvable->loadMissing(['employee.department', 'employee.designation']);
        $employee = $approvable->employee;

        return [
            'badge' => 'SALARY ADVANCE',
            'date' => $approvable->application_date?->format('d.m.Y'),
            'meta' => [
                'Employee' => $employee ? trim("{$employee->name} ({$employee->employee_id})") : null,
                'Department' => $employee?->department?->name,
                'Designation' => $employee?->designation?->name,
                'Deduct From' => $approvable->deduction_month?->format('F Y') ?? 'Next month',
                'Requested by' => $approval->requestedBy?->name,
            ],
            'total' => ['label' => 'Advance Amount', 'value' => (float) $approvable->amount, 'money' => true],
            'notes' => ['Reason' => (string) $approvable->reason],
        ];
    }
}
