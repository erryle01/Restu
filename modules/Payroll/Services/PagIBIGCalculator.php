<?php

class PagIBIGCalculator
{
    public function calculate(float $monthlyCompensation, array $configuration): array
    {
        $baseCeiling = max(0, (float) ($configuration['salary_base_ceiling'] ?? 0));
        $base = $baseCeiling > 0
            ? min(max(0, $monthlyCompensation), $baseCeiling)
            : max(0, $monthlyCompensation);
        $employeeRate = max(0, (float) ($configuration['employee_rate'] ?? 0));
        $employerRate = max(0, (float) ($configuration['employer_rate'] ?? 0));
        $employeeCap = max(0, (float) ($configuration['employee_cap'] ?? PHP_FLOAT_MAX));
        $employee = min($employeeCap, round($base * $employeeRate, 2));
        $employer = round($base * $employerRate, 2);

        return [
            'assessed_salary' => round($base, 2),
            'employee' => $employee,
            'employer' => $employer
        ];
    }
}
