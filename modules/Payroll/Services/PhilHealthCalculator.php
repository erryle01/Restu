<?php

class PhilHealthCalculator
{
    public function calculate(float $monthlyCompensation, array $configuration): array
    {
        $floor = max(0, (float) ($configuration['salary_floor'] ?? 0));
        $ceiling = max($floor, (float) ($configuration['salary_ceiling'] ?? PHP_FLOAT_MAX));
        $rate = max(0, (float) ($configuration['premium_rate'] ?? 0));
        $employeeShare = min(1, max(0, (float) ($configuration['employee_share_ratio'] ?? 0.5)));
        $base = min($ceiling, max($floor, $monthlyCompensation));
        $premium = round($base * $rate, 2);
        $employee = round($premium * $employeeShare, 2);

        return [
            'assessed_salary' => round($base, 2),
            'total_premium' => $premium,
            'employee' => $employee,
            'employer' => round($premium - $employee, 2)
        ];
    }
}
