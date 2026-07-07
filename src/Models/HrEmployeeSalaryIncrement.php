<?php

namespace ME\Hr\Models;

class HrEmployeeSalaryIncrement extends BaseHrModel
{
    protected $table = 'hr_employee_salary_increments';

    /**
     * Apply the latest increment override to a salary array from hr_employee_salary().
     *
     * When an increment exists its new_salary replaces the gross from salary_info,
     * and all salary components are scaled proportionally so that:
     *   - basic / house / medical / transport / food stay proportional to gross
     *   - ot_rate is recomputed from the new basic
     *
     * Call this right after hr_employee_salary() everywhere the package reads salary.
     */
    public static function applyIncrementOverride(array $sal, int $employeeId): array
    {
        $latest = static::where('employee_id', $employeeId)
            ->latest('increment_date')
            ->latest('id')
            ->first();

        if (!$latest) {
            return $sal;
        }

        $factoryNo = (int) (function_exists('hr_factory') ? (hr_factory('factory_no') ?? 0) : 0);
        $newGross  = match (true) {
            $factoryNo === 1 => (float) ($latest->new_salary_comp_1 ?: $latest->new_salary),
            $factoryNo === 2 => (float) ($latest->new_salary_comp_2 ?: $latest->new_salary),
            default          => (float) $latest->new_salary,
        };

        $oldGross = (float) ($sal['gross'] ?? 0);

        // Nothing to do if the salary_info already matches (or nothing stored).
        if ($oldGross <= 0 || $newGross <= 0 || abs($newGross - $oldGross) < 0.01) {
            return $sal;
        }

        $scale = $newGross / $oldGross;

        $sal['gross']     = $newGross;
        $sal['basic']     = round(($sal['basic']     ?? 0) * $scale, 2);
        $sal['house']     = round(($sal['house']     ?? 0) * $scale, 2);
        $sal['medical']   = round(($sal['medical']   ?? 0) * $scale, 2);
        $sal['transport'] = round(($sal['transport'] ?? 0) * $scale, 2);
        $sal['food']      = round(($sal['food']      ?? 0) * $scale, 2);

        // Recompute OT rate from new basic (basic / 208 working hours × 2)
        if (($sal['basic'] ?? 0) > 0) {
            $sal['ot_rate'] = round(($sal['basic'] / 208) * 2, 2);
        }

        return $sal;
    }
}
