<?php

class PayrollEngine
{
    private SalaryCalculator $salaryCalculator;
    private OvertimeCalculator $overtimeCalculator;
    private DeductionCalculator $deductionCalculator;
    private SSSCalculator $sssCalculator;
    private PhilHealthCalculator $philHealthCalculator;
    private PagIBIGCalculator $pagIBIGCalculator;
    private TaxCalculator $taxCalculator;

    public function __construct()
    {
        $this->salaryCalculator =
            new SalaryCalculator();

        $this->overtimeCalculator =
            new OvertimeCalculator();

        $this->deductionCalculator =
            new DeductionCalculator();

        $this->sssCalculator = new SSSCalculator();
        $this->philHealthCalculator = new PhilHealthCalculator();
        $this->pagIBIGCalculator = new PagIBIGCalculator();
        $this->taxCalculator = new TaxCalculator();
    }

    public function calculate(
        array $employee,
        array $attendance,
        array $rules,
        string $startDate,
        string $endDate,
        array $options = []
    ): array {

        /*
        |--------------------------------------------------------------------------
        | BASIC SALARY
        |--------------------------------------------------------------------------
        */

        $salary = $this->salaryCalculator->calculate(
            $employee,
            $attendance,
            $startDate,
            $endDate,
            $options
        );

        /*
        |--------------------------------------------------------------------------
        | OVERTIME
        |--------------------------------------------------------------------------
        */

        $overtime = $this->overtimeCalculator->calculate(
            $employee,
            $salary['overtime_minutes'],
            $options
        );

        /*
        |--------------------------------------------------------------------------
        | EARNINGS
        |--------------------------------------------------------------------------
        */

        $earnings = [];

        $earnings[] = [
            'item_type' => 'earning',
            'item_code' => 'BASIC',
            'item_name' => 'Basic Salary',
            'amount' => $salary['basic_pay'],
            'remarks' => 'Basic payroll'
        ];

        if ($overtime['overtime_pay'] > 0) {

            $earnings[] = [
                'item_type' => 'earning',
                'item_code' => 'OT',
                'item_name' => 'Overtime Pay',
                'amount' => $overtime['overtime_pay'],
                'remarks' =>
                    $overtime['overtime_hours']
                    . ' overtime hours'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | ALLOWANCES
        |--------------------------------------------------------------------------
        */

        $baseEarnings = $salary['basic_pay'] + $overtime['overtime_pay'];
        $earningRules = array_values(array_filter(
            $rules,
            static fn($rule): bool => ($rule['rule_type'] ?? '') === 'earning'
        ));
        $configuredEarnings = $this->deductionCalculator->calculate(
            $baseEarnings,
            $earningRules,
            'earning'
        );

        $allowance = 0.0;
        $incentive = 0.0;
        foreach ($configuredEarnings['items'] as $earning) {
            $earnings[] = $earning;
            if (str_contains(strtolower($earning['item_code']), 'allow')) {
                $allowance += $earning['amount'];
            } else {
                $incentive += $earning['amount'];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | GROSS
        |--------------------------------------------------------------------------
        */

        $grossPay =
            $salary['basic_pay'] +
            $overtime['overtime_pay'] +
            $allowance +
            $incentive;

        $absenceDeduction = (float) ($salary['unpaid_absence_deduction'] ?? 0);

        $statutoryDeductions = [];
        $employerContributions = [];
        $statutory = $options['statutory'] ?? [];
        $missingStatutory = [];
        $employeeId = (int) ($employee['id'] ?? 0);

        $isIncluded = static function (array $profile) use ($employeeId): bool {
            $excluded = array_map('intval', $profile['excluded_employee_ids'] ?? []);
            return ($profile['enabled'] ?? false) === true
                && !in_array($employeeId, $excluded, true);
        };

        if (isset($statutory['sss']) && $isIncluded($statutory['sss'])) {
            $contribution = $this->sssCalculator->calculate(
                $grossPay,
                $statutory['sss']['brackets'] ?? []
            );
            $statutoryDeductions[] = $this->contributionItem('SSS', 'SSS Employee Contribution', $contribution['employee']);
            $employerContributions[] = $this->employerItem('SSS_EMPLOYER', 'SSS Employer Contribution', $contribution['employer']);
            if (($contribution['ec'] ?? 0) > 0) {
                $employerContributions[] = $this->employerItem('SSS_EC', 'SSS Employment Compensation', $contribution['ec']);
            }
        } else {
            $missingStatutory[] = 'SSS';
        }

        if (isset($statutory['philhealth']) && $isIncluded($statutory['philhealth'])) {
            $contribution = $this->philHealthCalculator->calculate(
                $grossPay,
                $statutory['philhealth']
            );
            $statutoryDeductions[] = $this->contributionItem('PHILHEALTH', 'PhilHealth Employee Contribution', $contribution['employee']);
            $employerContributions[] = $this->employerItem('PHILHEALTH_EMPLOYER', 'PhilHealth Employer Contribution', $contribution['employer']);
        } else {
            $missingStatutory[] = 'PhilHealth';
        }

        if (isset($statutory['pagibig']) && $isIncluded($statutory['pagibig'])) {
            $contribution = $this->pagIBIGCalculator->calculate(
                $grossPay,
                $statutory['pagibig']
            );
            $statutoryDeductions[] = $this->contributionItem('PAGIBIG', 'Pag-IBIG Employee Contribution', $contribution['employee']);
            $employerContributions[] = $this->employerItem('PAGIBIG_EMPLOYER', 'Pag-IBIG Employer Contribution', $contribution['employer']);
        } else {
            $missingStatutory[] = 'Pag-IBIG';
        }

        $taxableCompensation = max(
            0,
            $grossPay - $absenceDeduction - array_sum(array_column($statutoryDeductions, 'amount'))
        );

        if (isset($statutory['bir_tax']) && $isIncluded($statutory['bir_tax'])) {
            $tax = $this->taxCalculator->calculate(
                $taxableCompensation,
                $statutory['bir_tax']['brackets'] ?? []
            );
            $statutoryDeductions[] = $this->contributionItem('BIR_WITHHOLDING', 'Withholding Tax', $tax);
        } else {
            $missingStatutory[] = 'BIR withholding tax';
        }

        /*
        |--------------------------------------------------------------------------
        | DEDUCTIONS
        |--------------------------------------------------------------------------
        */

        $deductionRules = array_values(array_filter(
            $rules,
            static function ($rule) {
                return ($rule['rule_type'] ?? 'deduction') === 'deduction';
            }
        ));

        $deductions =
            $this->deductionCalculator->calculate(
                $grossPay,
                $deductionRules
            );

        if ($absenceDeduction > 0) {
            array_unshift($deductions['items'], [
                'item_type' => 'deduction',
                'item_code' => 'UNPAID_ABSENCE',
                'item_name' => 'Unpaid Absence',
                'amount' => round($absenceDeduction, 2),
                'remarks' => number_format((float) $salary['unpaid_absence_days'], 2)
                    . ' unpaid day(s) prorated across '
                    . (int) $salary['scheduled_workdays']
                    . ' scheduled workdays'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NET
        |--------------------------------------------------------------------------
        */

        $deductionItems = array_merge($statutoryDeductions, $deductions['items']);
        $totalDeductions = round(
            $absenceDeduction +
            array_sum(array_column($statutoryDeductions, 'amount')) +
            $deductions['total'],
            2
        );

        $netPay = $grossPay - $totalDeductions;

        return [
            'basic_pay' =>
                round($salary['basic_pay'], 2),

            'overtime_pay' =>
                round($overtime['overtime_pay'], 2),

            'allowance' =>
                round($allowance, 2),

            'incentive' =>
                round($incentive, 2),

            'gross_pay' =>
                round($grossPay, 2),

            'total_deductions' =>
                $totalDeductions,

            'unpaid_absence_deduction' =>
                round($absenceDeduction, 2),

            'net_pay' =>
                round($netPay, 2),

            'working_days' =>
                $salary['working_days'],

            'regular_minutes' =>
                $salary['regular_minutes'],

            'regular_hours' =>
                $salary['regular_hours'],

            'overtime_minutes' =>
                $salary['overtime_minutes'],

            'overtime_hours' =>
                $salary['overtime_hours'],

            'late_minutes' =>
                $salary['late_minutes'],

            'undertime_minutes' =>
                $salary['undertime_minutes'],

            'earnings' =>
                $earnings,

            'deductions' =>
                $deductionItems,

            'employer_contributions' =>
                $employerContributions,

            'statutory_configuration_missing' =>
                $missingStatutory
        ];
    }

    private function contributionItem(string $code, string $name, float $amount): array
    {
        return [
            'item_type' => 'deduction',
            'item_code' => $code,
            'item_name' => $name,
            'amount' => round(max(0, $amount), 2),
            'remarks' => 'Effective-dated payroll statutory profile'
        ];
    }

    private function employerItem(string $code, string $name, float $amount): array
    {
        return [
            'item_type' => 'employer_contribution',
            'item_code' => $code,
            'item_name' => $name,
            'amount' => round(max(0, $amount), 2),
            'remarks' => 'Employer cost; not deducted from employee net pay'
        ];
    }
}