<?php

namespace ME\Hr\Services;

use App\Services\ApprovalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ME\AccSfl\Models\AcAccount;
use ME\AccSfl\Models\AcExpense;
use ME\AccSfl\Models\AcParticular;
use ME\AccSfl\Models\AcPaymentMethod;
use ME\Hr\Models\HrAdvanceApplication;

/**
 * When a salary advance application is approved, record a pending acc-sfl Salary Advance
 * expense for it. The expense is only created here - Accounts reviews it (account, payment
 * method can still be changed while it is pending) and approves it manually, which is what
 * posts it to the ledger and to the employee's Earnings & Deductions.
 *
 * acc-sfl is optional: nothing happens when it is not installed.
 */
class AdvanceExpenseService
{
    public static function createFor(HrAdvanceApplication $application): ?AcExpense
    {
        if (! class_exists(AcExpense::class)) {
            return null;
        }

        $marker = "Salary Advance Application #{$application->id}";

        // One expense per application, even if it is approved again.
        if ($existing = AcExpense::where('employee_id', $application->employee_id)->where('description', 'like', $marker.'%')->first()) {
            return $existing;
        }

        $particular = AcParticular::where('is_salary_advance', true)->orderBy('id')->first();
        $account = AcAccount::active()->orderBy('id')->first();
        $paymentMethod = AcPaymentMethod::active()->orderBy('id')->first();

        if (! $particular || ! $account || ! $paymentMethod) {
            Log::warning("Advance application #{$application->id}: no Salary Advance expense created - missing a Salary Advance particular, active account or payment method.");

            return null;
        }

        $application->loadMissing('employee');
        $amount = (float) $application->amount;

        $expense = DB::transaction(function () use ($application, $particular, $account, $paymentMethod, $amount, $marker) {
            $expense = AcExpense::create([
                'expense_date' => now()->toDateString(),
                'payment_method_id' => $paymentMethod->id,
                'branch_id' => $account->branch_id,
                'account_id' => $account->id,
                'employee_id' => $application->employee_id,
                'receiver_name' => $application->employee?->name,
                'total_amount' => $amount,
                'description' => $marker.($application->reason ? " - {$application->reason}" : ''),
                'created_by' => auth()->id(),
                'status' => AcExpense::STATUS_PENDING,
            ]);

            $expense->details()->create([
                'particular_id' => $particular->id,
                'qty' => 1,
                'rate' => $amount,
                'amount' => $amount,
            ]);

            return $expense;
        });

        // Same approval request a manually added expense gets, so Accounts can approve it.
        if (class_exists(ApprovalService::class)) {
            try {
                app(ApprovalService::class)->request([
                    'module' => 'accounts.expense',
                    'approvable' => $expense,
                    'title' => "Expense Approval - {$expense->expense_no}",
                    'description' => "Salary advance of {$expense->total_amount} for "
                        .trim(($application->employee?->name ?? '').' ('.($application->employee?->employee_id ?? '').')')
                        .' (from an approved advance application).',
                    'route_name' => 'acc-sfl.expenses.index',
                    'requested_by' => auth()->id(),
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $expense;
    }
}
