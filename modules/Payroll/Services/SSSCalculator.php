<?php

class SSSCalculator
{
    public function calculate(float $monthlyCompensation, array $brackets): array
    {
        $salary = max(0, $monthlyCompensation);
        foreach ($brackets as $bracket) {
            $minimum = (float) ($bracket['salary_min'] ?? 0);
            $maximum = $bracket['salary_max'] ?? null;
            if ($salary < $minimum || ($maximum !== null && $salary > (float) $maximum)) {
                continue;
            }

            return [
                'salary_credit' => (float) ($bracket['salary_credit'] ?? $salary),
                'employee' => round((float) ($bracket['employee_share'] ?? 0), 2),
                'employer' => round((float) ($bracket['employer_share'] ?? 0), 2),
                'ec' => round((float) ($bracket['employer_ec'] ?? 0), 2)
            ];
        }

        throw new RuntimeException('No effective SSS contribution bracket covers this compensation.');
    }
}
