<?php

class OvertimeCalculator
{
    public function calculate(
        array $employee,
        int $overtimeMinutes,
        array $options = []
    ): array {
        $salaryType = $employee['salary_type'] ?? 'monthly';

        $basicSalary = (float) (
            $employee['basic_salary'] ?? 0
        );

        /*
        |--------------------------------------------------------------------------
        | Default Philippine ordinary-day OT multiplier
        |--------------------------------------------------------------------------
        |
        | This is intentionally isolated here.
        | Later we will make this database/rule driven.
        |
        */

        $overtimeMultiplier = max(1, (float) ($options['overtime_multiplier'] ?? 1.25));
        $workHoursPerDay = max(1, (float) ($options['standard_work_hours'] ?? 8));
        $monthlyWorkdays = max(1, (float) ($options['monthly_workdays'] ?? 26));

        if ($salaryType === 'monthly') {

            /*
            | 26-day divisor is configurable later.
            */

            $dailyRate = $basicSalary / $monthlyWorkdays;

            $hourlyRate = $dailyRate / $workHoursPerDay;
        }

        elseif ($salaryType === 'daily') {

            $hourlyRate = $basicSalary / $workHoursPerDay;
        }

        elseif ($salaryType === 'hourly') {

            $hourlyRate = $basicSalary;
        }

        else {
            $hourlyRate = 0;
        }

        $overtimeHours = $overtimeMinutes / 60;

        $overtimePay =
            $hourlyRate *
            $overtimeHours *
            $overtimeMultiplier;

        return [
            'overtime_minutes' => $overtimeMinutes,

            'overtime_hours' => round(
                $overtimeHours,
                2
            ),

            'hourly_rate' => round(
                $hourlyRate,
                4
            ),

            'multiplier' => $overtimeMultiplier,

            'overtime_pay' => round(
                $overtimePay,
                2
            )
        ];
    }
}