<?php

class DeductionCalculator
{
    public function calculate(
        float $grossPay,
        array $rules = [],
        string $itemType = 'deduction'
    ): array {
        $items = [];

        $total = 0;

        foreach ($rules as $rule) {

            if (($rule['status'] ?? 'active') !== 'active') {
                continue;
            }

            if (($rule['rule_type'] ?? 'deduction') !== $itemType) {
                continue;
            }

            $amount = 0;

            $calculationType =
                $rule['calculation_type'] ?? '';

            if ($calculationType === 'fixed') {

                $amount = (float) (
                    $rule['value'] ?? 0
                );
            }

            elseif ($calculationType === 'percentage') {

                $percentage = (float) (
                    $rule['value'] ?? 0
                );

                $amount =
                    $grossPay *
                    ($percentage / 100);
            }

            /*
            |--------------------------------------------------------------------------
            | Formula support will be added in PayrollEngine.
            |--------------------------------------------------------------------------
            */

            if ($amount <= 0) {
                continue;
            }

            $amount = round($amount, 2);

            $items[] = [
                'item_type' => $itemType,
                'item_code' => $rule['rule_code'] ?? strtoupper($itemType),
                'item_name' => $rule['rule_name'] ?? ucfirst($itemType),
                'amount' => $amount,
                'remarks' => $rule['remarks'] ?? 'Payroll rule'
            ];

            $total += $amount;
        }

        return [
            'items' => $items,
            'total' => round($total, 2)
        ];
    }
}