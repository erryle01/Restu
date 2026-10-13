<?php

class TaxCalculator
{
    public function calculate(float $taxableCompensation, array $brackets): float
    {
        $income = max(0, $taxableCompensation);
        usort($brackets, static fn($left, $right): int =>
            (float) ($left['income_min'] ?? 0) <=> (float) ($right['income_min'] ?? 0)
        );

        foreach ($brackets as $bracket) {
            $minimum = max(0, (float) ($bracket['income_min'] ?? 0));
            $maximum = $bracket['income_max'] ?? null;
            if ($income < $minimum || ($maximum !== null && $income > (float) $maximum)) {
                continue;
            }

            $baseTax = max(0, (float) ($bracket['base_tax'] ?? 0));
            $rate = max(0, (float) ($bracket['marginal_rate'] ?? 0));
            return round($baseTax + (($income - $minimum) * $rate), 2);
        }

        if (!$brackets && $income === 0.0) {
            return 0.0;
        }

        throw new RuntimeException('No effective withholding-tax bracket covers this taxable compensation.');
    }
}
