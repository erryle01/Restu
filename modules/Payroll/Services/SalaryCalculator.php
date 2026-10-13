<?php

class SalaryCalculator
{
    public function calculate(
        array $employee,
        array $attendance,
        string $startDate,
        string $endDate,
        array $options = []
    ): array {
        $salaryType = $employee['salary_type'] ?? 'monthly';
        $basicSalary = (float) ($employee['basic_salary'] ?? 0);
        $standardWorkMinutes = max(1, (int) ($options['standard_work_minutes'] ?? 480));
        $scheduledWorkdays = max(0, (int) ($options['scheduled_workdays'] ?? 0));

        $workingDays = 0;
        $unpaidAbsenceDays = 0.0;
        $regularMinutes = 0;
        $lateMinutes = 0;
        $undertimeMinutes = 0;
        $overtimeMinutes = 0;

        foreach ($attendance as $row) {
            if (($row['status'] ?? '') === 'rest_day') {
                continue;
            }

            $status = $row['status'] ?? '';
            if ($status === 'absent') {
                $unpaidAbsenceDays += 1;
            } elseif ($status === 'half_day') {
                $unpaidAbsenceDays += 0.5;
            }

            if (
                ($row['time_in'] ?? null) !== null &&
                ($row['time_out'] ?? null) !== null
            ) {
                $paidFraction = min(
                    1,
                    max(0, (int) ($row['regular_minutes'] ?? $row['total_minutes'] ?? 0) / $standardWorkMinutes)
                );
                $workingDays += $paidFraction;
            }

            $regularMinutes += (int) (
                $row['regular_minutes'] ?? 0
            );

            $lateMinutes += (int) (
                $row['late_minutes'] ?? 0
            );

            $undertimeMinutes += (int) (
                $row['undertime_minutes'] ?? 0
            );

            $overtimeMinutes += (int) (
                $row['overtime_minutes'] ?? 0
            );
        }

        /*
        |--------------------------------------------------------------------------
        | MONTHLY
        |--------------------------------------------------------------------------
        */

        if ($salaryType === 'monthly') {
            $absenceDeduction = 0.0;
            if ($scheduledWorkdays > 0 && $unpaidAbsenceDays > 0) {
                $dailyRate = $basicSalary / $scheduledWorkdays;
                $absenceDeduction = min($basicSalary, $dailyRate * $unpaidAbsenceDays);
            }
            $basicPay = $basicSalary;
        }

        /*
        |--------------------------------------------------------------------------
        | DAILY
        |--------------------------------------------------------------------------
        */

        elseif ($salaryType === 'daily') {
            $basicPay = $basicSalary * $workingDays;
        }

        /*
        |--------------------------------------------------------------------------
        | HOURLY
        |--------------------------------------------------------------------------
        */

        elseif ($salaryType === 'hourly') {
            $basicPay = $basicSalary *
                ($regularMinutes / 60);
        }

        else {
            $basicPay = 0;
        }

        return [
            'basic_pay' => round($basicPay, 2),

            'unpaid_absence_deduction' => round($absenceDeduction ?? 0, 2),

            'absence_proration_unavailable' => $salaryType === 'monthly'
                && $unpaidAbsenceDays > 0
                && $scheduledWorkdays === 0,

            'working_days' => $workingDays,

            'unpaid_absence_days' => round($unpaidAbsenceDays, 2),

            'scheduled_workdays' => $scheduledWorkdays,

            'regular_minutes' => $regularMinutes,

            'regular_hours' => round(
                $regularMinutes / 60,
                2
            ),

            'late_minutes' => $lateMinutes,

            'undertime_minutes' => $undertimeMinutes,

            'overtime_minutes' => $overtimeMinutes,

            'overtime_hours' => round(
                $overtimeMinutes / 60,
                2
            )
        ];
    }
}