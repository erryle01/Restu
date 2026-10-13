<?php

declare(strict_types=1);

class PayrollCalculationTest
{
    public static function register(TestRunner $runner): void
    {
        $runner->add(
            'Payroll calculations',
            'Monthly salary only deducts recorded unpaid absence',
            function (): void {
                $calculator = new SalaryCalculator();
                $employee = ['salary_type' => 'monthly', 'basic_salary' => 30000];
                $result = $calculator->calculate(
                    $employee,
                    [
                        ['status' => 'absent'],
                        ['status' => 'half_day'],
                        ['status' => 'leave']
                    ],
                    '2026-09-01',
                    '2026-09-30',
                    ['scheduled_workdays' => 22]
                );

                TestCase::assertEquals(30000.0, $result['basic_pay']);
                TestCase::assertEquals(2045.45, $result['unpaid_absence_deduction']);
                TestCase::assertEquals(1.5, $result['unpaid_absence_days']);
            }
        );

        $runner->add(
            'Payroll calculations',
            'Monthly salary remains full when no unpaid absences',
            function (): void {
                $result = (new SalaryCalculator())->calculate(
                    ['salary_type' => 'monthly', 'basic_salary' => 30000],
                    [['status' => 'present', 'time_in' => '08:00', 'time_out' => '17:00', 'regular_minutes' => 480]],
                    '2026-09-01',
                    '2026-09-30',
                    ['scheduled_workdays' => 22]
                );

                TestCase::assertEquals(30000.0, $result['basic_pay']);
            }
        );

        $runner->add(
            'Payroll calculations',
            'Daily salary prorates incomplete attendance by scheduled minutes',
            function (): void {
                $result = (new SalaryCalculator())->calculate(
                    ['salary_type' => 'daily', 'basic_salary' => 1000],
                    [['status' => 'half_day', 'time_in' => '08:00', 'time_out' => '12:00', 'regular_minutes' => 240]],
                    '2026-09-01',
                    '2026-09-30'
                );

                TestCase::assertEquals(500.0, $result['basic_pay']);
            }
        );

        $runner->add(
            'Payroll calculations',
            'Hourly salary uses regular recorded minutes',
            function (): void {
                $result = (new SalaryCalculator())->calculate(
                    ['salary_type' => 'hourly', 'basic_salary' => 200],
                    [['status' => 'present', 'regular_minutes' => 360]],
                    '2026-09-01',
                    '2026-09-30'
                );

                TestCase::assertEquals(1200.0, $result['basic_pay']);
            }
        );

        $runner->add(
            'Payroll calculations',
            'Overtime multiplier and standard hours are configurable',
            function (): void {
                $result = (new OvertimeCalculator())->calculate(
                    ['salary_type' => 'daily', 'basic_salary' => 1000],
                    120,
                    ['overtime_multiplier' => 1.5, 'standard_work_hours' => 8]
                );

                TestCase::assertEquals(375.0, $result['overtime_pay']);
            }
        );

        $runner->add(
            'Payroll calculations',
            'Earnings and deductions rules produce itemized amounts',
            function (): void {
                $calculator = new DeductionCalculator();
                $earning = $calculator->calculate(10000, [[
                    'status' => 'active',
                    'rule_type' => 'earning',
                    'calculation_type' => 'fixed',
                    'value' => 500,
                    'rule_code' => 'ALLOWANCE',
                    'rule_name' => 'Transport Allowance'
                ]], 'earning');
                $deduction = $calculator->calculate(10000, [[
                    'status' => 'active',
                    'rule_type' => 'deduction',
                    'calculation_type' => 'percentage',
                    'value' => 5,
                    'rule_code' => 'OTHER',
                    'rule_name' => 'Configured Deduction'
                ]], 'deduction');

                TestCase::assertEquals(500.0, $earning['total']);
                TestCase::assertEquals('earning', $earning['items'][0]['item_type']);
                TestCase::assertEquals(500.0, $deduction['total']);
            }
        );

        $runner->add(
            'Payroll calculations',
            'Philippine contribution calculators use supplied effective profiles',
            function (): void {
                $sss = (new SSSCalculator())->calculate(20000, [[
                    'salary_min' => 0,
                    'salary_max' => 30000,
                    'salary_credit' => 20000,
                    'employee_share' => 900,
                    'employer_share' => 1900,
                    'employer_ec' => 30
                ]]);
                $philHealth = (new PhilHealthCalculator())->calculate(20000, [
                    'premium_rate' => 0.05,
                    'salary_floor' => 10000,
                    'salary_ceiling' => 100000,
                    'employee_share_ratio' => 0.5
                ]);
                $pagibig = (new PagIBIGCalculator())->calculate(20000, [
                    'salary_base_ceiling' => 10000,
                    'employee_rate' => 0.02,
                    'employer_rate' => 0.02,
                    'employee_cap' => 200
                ]);

                TestCase::assertEquals(900.0, $sss['employee']);
                TestCase::assertEquals(1900.0, $sss['employer']);
                TestCase::assertEquals(500.0, $philHealth['employee']);
                TestCase::assertEquals(500.0, $philHealth['employer']);
                TestCase::assertEquals(200.0, $pagibig['employee']);
                TestCase::assertEquals(200.0, $pagibig['employer']);
            }
        );

        $runner->add(
            'Payroll calculations',
            'Withholding tax uses the supplied progressive bracket',
            function (): void {
                $tax = (new TaxCalculator())->calculate(30000, [
                    ['income_min' => 0, 'income_max' => 25000, 'base_tax' => 0, 'marginal_rate' => 0],
                    ['income_min' => 25000, 'income_max' => null, 'base_tax' => 0, 'marginal_rate' => 0.1]
                ]);

                TestCase::assertEquals(500.0, $tax);
            }
        );

        $runner->add(
            'Payroll calculations',
            'Employer statutory shares do not reduce employee net pay',
            function (): void {
                $engine = new PayrollEngine();
                $result = $engine->calculate(
                    ['id' => 1, 'salary_type' => 'monthly', 'basic_salary' => 10000],
                    [],
                    [],
                    '2026-09-01',
                    '2026-09-30',
                    [
                        'statutory' => [
                            'sss' => [
                                'enabled' => true,
                                'brackets' => [[
                                    'salary_min' => 0,
                                    'salary_max' => 20000,
                                    'employee_share' => 450,
                                    'employer_share' => 950,
                                    'employer_ec' => 10
                                ]]
                            ]
                        ]
                    ]
                );

                TestCase::assertEquals(9550.0, $result['net_pay']);
                TestCase::assertEquals(960.0, array_sum(array_column($result['employer_contributions'], 'amount')));
                TestCase::assertEquals(450.0, $result['deductions'][0]['amount']);
            }
        );
    }
}
