<?php

namespace ME\Hr\Approvals;

use App\Approvals\BaseApprovalHandler;
use App\Models\Approval;
use Illuminate\Database\Eloquent\Model;
use ME\Hr\Models\HrAdvanceApplication;
use ME\Hr\Services\AdvanceExpenseService;
use ME\Hr\Services\HrApprovals;

/**
 * Module 'hr.advance_application' (registered by HrServiceProvider::registerApprovalModules()).
 *
 * A saved salary advance application asks HR and Accounts to approve it: mail goes to every
 * user with hr_advance_application.approve (HR) or ac_expense.approve (Accounts, who pay it).
 * Once approved, a pending acc-sfl Salary Advance expense is created (AdvanceExpenseService);
 * Accounts approves that expense manually, which pays and posts the advance.
 */
class AdvanceApplicationApprovalHandler extends BaseApprovalHandler
{
    public function recipients(?Model $approvable, Approval $approval): array
    {
        return HrApprovals::usersWith(['hr_advance_application.approve', 'ac_expense.approve']);
    }

    /** Approved: record a pending Salary Advance expense in Accounts (approved there manually). */
    public function onApproved(Approval $approval): void
    {
        $application = $approval->approvable;

        if (! $application instanceof HrAdvanceApplication) {
            return;
        }

        try {
            AdvanceExpenseService::createFor($application);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function mailContent(?Model $approvable, Approval $approval): array
    {
        if (! $approvable instanceof HrAdvanceApplication) {
            return [];
        }

        $approvable->loadMissing(['employee.department', 'employee.designation', 'employee.classification']);
        $employee = $approvable->employee;

        return [
            'badge' => 'SALARY ADVANCE',
            'date' => $approvable->application_date?->format('d.m.Y'),
            'meta' => [
                'Employee' => $employee ? trim("{$employee->name} ({$employee->employee_id})") : null,
                'Department' => $employee?->department?->name,
                'Designation' => $employee?->designation?->name,
            ] + ($employee?->serviceMailMeta() ?? []) + [
                'Deduct From' => $approvable->deduction_month?->format('F Y') ?? 'Next month',
                'Requested by' => $approval->requestedBy?->name,
            ],
            'total' => ['label' => 'Advance Amount', 'value' => (float) $approvable->amount, 'money' => true],
            'notes' => ['Reason' => (string) $approvable->reason],
        ];
    }
}
