<?php

/**
 * Package-owned helpers that used to be duplicated (and drift out of sync / break)
 * across every host app's app/helpers.php. Both only touch models this package
 * already owns (HrFactory, HrSalaryKey, HrDesignation, HrEmployee), so there's no
 * reason for them to live outside it. Guarded with function_exists() so a host app
 * that still defines its own copy doesn't cause a fatal redeclaration error during
 * the transition — remove the host app's copies once this is deployed everywhere.
 */

if (!function_exists('hr_factory')) {
    function hr_factory($field = null, $fallback = null, bool $fresh = false)
    {
        static $factory = null;

        // Backward compatibility: hr_factory(true) works as refresh flag.
        if (is_bool($field) && $fallback === null) {
            $fresh = $field;
            $field = null;
        }

        if ($fresh) {
            $factory = null;
        }

        if ($factory === null) {
            if (app()->runningInConsole() || !class_exists(\ME\Hr\Models\HrFactory::class)) {
                return $field !== null ? $fallback : null;
            }

            try {
                $factory = \ME\Hr\Models\HrFactory::query()
                    ->where('status', 'active')
                    ->latest('id')
                    ->first();

                if (!$factory) {
                    $factory = \ME\Hr\Models\HrFactory::query()->latest('id')->first();
                }
            } catch (\Throwable $e) {
                $factory = null;
            }
        }

        if ($field === null) {
            return $factory;
        }

        $field = trim((string) $field);
        if ($field === 'banga_name') {
            $field = 'bn_name';
        }

        return data_get($factory, $field, $fallback);
    }
}

if (!function_exists('hr_factory_name')) {
    function hr_factory_name(?string $fallback = null): string
    {
        return (string) hr_factory('name', $fallback ?? '');
    }
}

if (!function_exists('hr_employee_salary')) {
    /**
     * Resolve effective salary breakdown for an employee based on the factory compliance tier.
     *
     * Factory No = 0 (default) -> Actual gross  (employee.gross_salary)                 - deduct FROM gross
     * Factory No = 1           -> Comp-1 gross  (salary_info.gross_salary_comp_1)       - deduct FROM basic
     * Factory No = 2           -> Comp-2 gross  (salary_info.gross_salary_comp_2)       - deduct FROM basic
     *
     * Breakdown formula:  mtf = medical + transport + food
     *                     basic = (gross - mtf) / 1.5
     *                     house = basic / 2
     */
    function hr_employee_salary($employee, $factory = null, $salaryKey = null): array
    {
        // Factory
        if ($factory === null) {
            $factory = hr_factory();
        }
        $factoryNo = (int) ($factory->factory_no ?? 0);

        // Salary Key (fixed MTF allowances)
        if ($salaryKey === null) {
            try {
                $salaryKey = \ME\Hr\Models\HrSalaryKey::where('status', 'active')->latest('id')->first();
            } catch (\Throwable $e) {
                $salaryKey = null;
            }
        }
        $medical   = (float) ($salaryKey->medical   ?? 0);
        $transport = (float) ($salaryKey->transport ?? 0);
        $food      = (float) ($salaryKey->lunch     ?? 0);
        $mtf       = $medical + $transport + $food;

        // Effective gross by factory_no. $employee->salary_info is not a real attribute
        // (HrEmployee has no such column) — otherInfo()['salary_info'] is the actual
        // employee-level override source, same as used a few lines below for gross.
        $salaryInfo = $employee->otherInfo()['salary_info'] ?? [];

        $designation = null;
        if (!empty($employee->designation_id) && class_exists(\ME\Hr\Models\HrDesignation::class)) {
            try {
                $designation = \ME\Hr\Models\HrDesignation::query()->find($employee->designation_id);
            } catch (\Throwable $e) {
                $designation = null;
            }
        }

        if ($factoryNo === 1) {
            $gross      = (float) ($employee->otherInfo()['salary_info']['gross_salary_comp_1'] ?? $employee->gross_salary ?? 0);
            $deductFrom = 'basic';
        } elseif ($factoryNo === 2) {
            $gross      = (float) ($employee->otherInfo()['salary_info']['gross_salary_comp_2'] ?? $employee->gross_salary ?? 0);
            $deductFrom = 'basic';
        } else {
            $gross      = (float) ($employee->gross_salary ?? 0);
            $deductFrom = 'gross';
        }

        // Fallback: sum individual allowance columns
        if ($gross <= 0) {
            $gross = (float) (
                ($employee->basic_salary        ?? 0)
                + ($employee->house_rent          ?? 0)
                + ($employee->medical_allowance   ?? 0)
                + ($employee->transport_allowance ?? 0)
                + ($employee->food_allowance      ?? 0)
            );
        }

        // Breakdown
        $basic  = ($gross > 0 && $mtf > 0) ? ($gross - $mtf) / 1.5 : ((float) ($employee->basic_salary ?? 0) ?: null);
        $house  = $basic ? $basic / 2 : null;
        $otRate = $basic > 0 ? round(($basic / 208) * 2, 2) : 0;
        $fromEmployeeOrDesignation = static function ($employeeValue, $designationValue): float {
            if (is_numeric($employeeValue) && (float) $employeeValue > 0) {
                return (float) $employeeValue;
            }
            if (is_numeric($designationValue) && (float) $designationValue > 0) {
                return (float) $designationValue;
            }

            return (float) ($employeeValue ?? $designationValue ?? 0);
        };

        $attendanceBonus = $fromEmployeeOrDesignation(
            data_get($salaryInfo, 'attendance_bonus'),
            data_get($designation, 'attendance_bonus', 0)
        );
        $attendanceBonusCom = $fromEmployeeOrDesignation(
            data_get($salaryInfo, 'attendance_bonus_com'),
            data_get($designation, 'attendance_bonus_com', 0)
        );
        $carFuel = $fromEmployeeOrDesignation(
            data_get($salaryInfo, 'car_fuel'),
            data_get($designation, 'car_fuel', 0)
        );
        $phoneInternet = $fromEmployeeOrDesignation(
            data_get($salaryInfo, 'phone_internet'),
            data_get($designation, 'phone_internet', 0)
        );
        $extraFacility = $fromEmployeeOrDesignation(
            data_get($salaryInfo, 'extra_facility'),
            data_get($designation, 'extra_facility', 0)
        );

        return [
            'factory_no'  => $factoryNo,
            'gross'       => $gross,
            'basic'       => $basic,
            'house'       => $house,
            'medical'     => $medical,
            'transport'   => $transport,
            'food'        => $food,
            'attendance_bonus' => $attendanceBonus,
            'attendance_bonus_com' => $attendanceBonusCom,
            'car_fuel' => $carFuel,
            'phone_internet' => $phoneInternet,
            'extra_facility' => $extraFacility,
            'mtf'         => $mtf,
            'ot_rate'     => $otRate,
            'deduct_from' => $deductFrom,
        ];
    }
}
