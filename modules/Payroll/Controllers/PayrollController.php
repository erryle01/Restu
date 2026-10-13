<?php
require_once BASE_PATH .
    '/modules/Payroll/Models/PayrollModel.php';

    require_once BASE_PATH .
    '/modules/Settings/Models/SettingsModel.php';
class PayrollController extends Controller
{
    private function model(): PayrollModel
    {
        return new PayrollModel();
    }

    /*
    |--------------------------------------------------------------------------
    | ENGINE
    |--------------------------------------------------------------------------
    */

    private function engine(): PayrollEngine
    {
        $this->loadServices();

    return new PayrollEngine();
}

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(): void
    {
        Auth::requirePermission('payroll.view');

        $model = $this->model();

        $periods = $model->getPeriods();

        $success = Session::getFlash('success');
        $error = Session::getFlash('error');

        $this->view(
            'modules/Payroll/Views/index',
            [
                'periods' => $periods,
                'success' => $success,
                'error' => $error
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(): void
    {
        Auth::requirePermission('payroll.create');

        $success = Session::getFlash('success');
        $error = Session::getFlash('error');

        $this->view(
            'modules/Payroll/Views/create',
            [
                'success' => $success,
                'error' => $error
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(): void
    {
        Auth::requirePermission('payroll.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/payroll/create');
        }

        $periodName = trim(
            $_POST['period_name'] ?? ''
        );

        $startDate = trim(
            $_POST['start_date'] ?? ''
        );

        $endDate = trim(
            $_POST['end_date'] ?? ''
        );

        $payDate = trim(
            $_POST['pay_date'] ?? ''
        );

        if (
            $periodName === '' ||
            $startDate === '' ||
            $endDate === ''
        ) {
            Session::flash(
                'error',
                'Please complete all required fields.'
            );

            $this->redirect('/payroll/create');
        }

        if ($startDate > $endDate) {
            Session::flash(
                'error',
                'Start date cannot be later than end date.'
            );

            $this->redirect('/payroll/create');
        }

        $model = $this->model();

        $overlap =
            $model->findOverlappingPeriod(
                $startDate,
                $endDate
            );

        if ($overlap) {
            Session::flash(
                'error',
                'The payroll period overlaps with an existing period: '
                . $overlap['period_name']
            );

            $this->redirect('/payroll/create');
        }

        try {

            $id = $model->createPeriod([
                'period_name' => $periodName,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'pay_date' => $payDate,
                'created_by' => Auth::id()
            ]);

            Session::flash(
                'success',
                'Payroll period created successfully.'
            );

            $this->redirect(
                '/payroll/view/' . $id
            );

        } catch (Throwable $e) {

            Session::flash(
                'error',
                'Unable to create payroll period: '
                . $e->getMessage()
            );

            $this->redirect('/payroll/create');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(int $id): void
    {
        Auth::requirePermission('payroll.view');

        $model = $this->model();

        $period = $model->findPeriod($id);

        if (!$period) {
            Session::flash(
                'error',
                'Payroll period not found.'
            );

            $this->redirect('/payroll');
        }

        $payroll = $model->getPayrollByPeriod($id);

        $success = Session::getFlash('success');
        $error = Session::getFlash('error');

        $this->view(
            'modules/Payroll/Views/view',
            [
                'period' => $period,
                'payroll' => $payroll,
                'success' => $success,
                'error' => $error
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(int $id): void
    {
        Auth::requirePermission('payroll.create');

        $model = $this->model();

        $period = $model->findPeriod($id);

        if (!$period) {
            Session::flash(
                'error',
                'Payroll period not found.'
            );

            $this->redirect('/payroll');
        }

        if ($period['status'] !== 'draft') {
            Session::flash(
                'error',
                'Only draft payroll periods can be edited.'
            );

            $this->redirect(
                '/payroll/view/' . $id
            );
        }

        $this->view(
            'modules/Payroll/Views/edit',
            [
                'period' => $period
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(int $id): void
    {
        Auth::requirePermission('payroll.create');

        $model = $this->model();

        $period = $model->findPeriod($id);

        if (!$period) {
            Session::flash(
                'error',
                'Payroll period not found.'
            );

            $this->redirect('/payroll');
        }

        if ($period['status'] !== 'draft') {
            Session::flash(
                'error',
                'Only draft payroll periods can be edited.'
            );

            $this->redirect(
                '/payroll/view/' . $id
            );
        }

        $periodName = trim(
            $_POST['period_name'] ?? ''
        );

        $startDate = trim(
            $_POST['start_date'] ?? ''
        );

        $endDate = trim(
            $_POST['end_date'] ?? ''
        );

        $payDate = trim(
            $_POST['pay_date'] ?? ''
        );

        if (
            $periodName === '' ||
            $startDate === '' ||
            $endDate === ''
        ) {
            Session::flash(
                'error',
                'Please complete all required fields.'
            );

            $this->redirect(
                '/payroll/edit/' . $id
            );
        }

        if ($startDate > $endDate) {
            Session::flash(
                'error',
                'Start date cannot be later than end date.'
            );

            $this->redirect(
                '/payroll/edit/' . $id
            );
        }

        $overlap =
            $model->findOverlappingPeriod(
                $startDate,
                $endDate,
                $id
            );

        if ($overlap) {
            Session::flash(
                'error',
                'This period overlaps another payroll period.'
            );

            $this->redirect(
                '/payroll/edit/' . $id
            );
        }

        try {

            $model->updatePeriod(
                $id,
                [
                    'period_name' => $periodName,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'pay_date' => $payDate
                ]
            );

            Session::flash(
                'success',
                'Payroll period updated successfully.'
            );

        } catch (Throwable $e) {

            Session::flash(
                'error',
                'Unable to update payroll period: '
                . $e->getMessage()
            );
        }

        $this->redirect(
            '/payroll/view/' . $id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATE
    |--------------------------------------------------------------------------
    */

    public function calculate(int $id): void
    {
        Auth::requirePermission('payroll.calculate');

        $model = $this->model();

        $period = $model->findPeriod($id);

        if (!$period) {
            Session::flash(
                'error',
                'Payroll period not found.'
            );

            $this->redirect('/payroll');
        }

        if (in_array(
            $period['status'],
            ['approved', 'paid', 'locked'],
            true
        )) {
            Session::flash(
                'error',
                'This payroll period can no longer be recalculated.'
            );

            $this->redirect(
                '/payroll/view/' . $id
            );
        }

        try {

            $this->dbTransactionStart($model);

            $model->updatePeriodStatus(
                $id,
                'processing'
            );

            $employees =
                $model->getActiveEmployees();

            /*
            |--------------------------------------------------------------------------
            | Rules
            |--------------------------------------------------------------------------
            */

            $rules = $this->getActiveRules(
                $model,
                $period['end_date']
            );

            $engine = $this->engine();
            $settingsModel = new SettingsModel();
            $calculationOptions = [
                'overtime_multiplier' => (float) $settingsModel->get('overtime_rate', 1.25),
                'standard_work_hours' => (float) $settingsModel->get('standard_work_hours', 8),
                'monthly_workdays' => (float) $settingsModel->get('monthly_workdays_per_period', 26)
            ];

            foreach ($employees as $employee) {

                $attendance =
                    $model->getAttendance(
                        (int) $employee['id'],
                        $period['start_date'],
                        $period['end_date']
                    );

                $result =
                    $engine->calculate(
                        $employee,
                        $attendance,
                        $rules,
                        $period['start_date'],
                        $period['end_date'],
                        array_merge($calculationOptions, [
                            'scheduled_workdays' => $model->getScheduledWorkdays(
                                (int) $employee['id'],
                                $period['start_date'],
                                $period['end_date']
                            ),
                            'standard_work_minutes' => (int) round(
                                $calculationOptions['standard_work_hours'] * 60
                            ),
                            'statutory' => $this->getStatutoryProfiles($rules),
                            'require_statutory_configuration' => false
                        ])
                    );

                if ($result['absence_proration_unavailable'] ?? false) {
                    throw new RuntimeException(
                        'Cannot calculate unpaid absence for '
                        . $employee['first_name'] . ' ' . $employee['last_name']
                        . ': no scheduled workdays exist in this payroll period.'
                    );
                }

                $existing =
                    $model->findPayrollByEmployeePeriod(
                        $id,
                        (int) $employee['id']
                    );

                $payrollData = [
                    'payroll_period_id' =>
                        $id,

                    'employee_id' =>
                        (int) $employee['id'],

                    'basic_pay' =>
                        $result['basic_pay'],

                    'overtime_pay' =>
                        $result['overtime_pay'],

                    'allowance' =>
                        $result['allowance'],

                    'incentive' =>
                        $result['incentive'],

                    'gross_pay' =>
                        $result['gross_pay'],

                    'total_deductions' =>
                        $result['total_deductions'],

                    'net_pay' =>
                        $result['net_pay']
                ];

                if ($existing) {

                    $payrollId =
                        (int) $existing['id'];

                    $model->updatePayroll(
                        $payrollId,
                        $payrollData
                    );

                } else {

                    $payrollId =
                        $model->createPayroll(
                            $payrollData
                        );
                }

                /*
                |--------------------------------------------------------------------------
                | Replace payroll items
                |--------------------------------------------------------------------------
                */

                $model->deletePayrollItems(
                    $payrollId
                );

                foreach (
                    array_merge(
                        $result['earnings'],
                        $result['deductions'],
                        $result['employer_contributions'] ?? []
                    ) as $item
                ) {

                    $model->createPayrollItem([
                        'payroll_id' =>
                            $payrollId,

                        'item_type' =>
                            $item['item_type'],

                        'item_code' =>
                            $item['item_code'],

                        'item_name' =>
                            $item['item_name'],

                        'amount' =>
                            $item['amount'],

                        'remarks' =>
                            $item['remarks'] ?? null
                    ]);
                }
            }

            $model->updatePeriodStatus(
                $id,
                'review'
            );

            $this->dbCommit(
                $model
            );

            Session::flash(
                'success',
                'Payroll calculated successfully and is ready for review.'
            );

        } catch (Throwable $e) {

            $this->dbRollback(
                $model
            );

            Session::flash(
                'error',
                'Payroll calculation failed: '
                . $e->getMessage()
            );
        }

        $this->redirect(
            '/payroll/view/' . $id
        );
    }


private function loadServices(): void
{
    require_once BASE_PATH .
        '/modules/Payroll/Services/PayrollEngine.php';

    require_once BASE_PATH .
        '/modules/Payroll/Services/SalaryCalculator.php';

    require_once BASE_PATH .
        '/modules/Payroll/Services/OvertimeCalculator.php';

    require_once BASE_PATH .
        '/modules/Payroll/Services/DeductionCalculator.php';
}


    /*
    |--------------------------------------------------------------------------
    | APPROVE
    |--------------------------------------------------------------------------
    */

    public function approve(int $id): void
    {
        Auth::requirePermission('payroll.approve');

        $model = $this->model();

        $period = $model->findPeriod($id);

        if (!$period) {
            Session::flash(
                'error',
                'Payroll period not found.'
            );

            $this->redirect('/payroll');
        }

        if ($period['status'] !== 'review') {
            Session::flash(
                'error',
                'Only payroll periods in review can be approved.'
            );

            $this->redirect(
                '/payroll/view/' . $id
            );
        }

        try {

            $model->approvePeriod(
                $id,
                (int) Auth::id()
            );

            Session::flash(
                'success',
                'Payroll approved successfully.'
            );

        } catch (Throwable $e) {

            Session::flash(
                'error',
                'Unable to approve payroll: '
                . $e->getMessage()
            );
        }

        $this->redirect(
            '/payroll/view/' . $id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PAY
    |--------------------------------------------------------------------------
    */

    public function pay(int $id): void
    {
        Auth::requirePermission('payroll.pay');

        $model = $this->model();

        $payroll = $model->findPayroll($id);

        if (!$payroll) {
            Session::flash(
                'error',
                'Payroll record not found.'
            );

            $this->redirect('/payroll');
        }

        if ($payroll['status'] !== 'approved') {
            Session::flash(
                'error',
                'Only approved payroll can be paid.'
            );

            $this->redirect(
                '/payroll/view/'
                . $payroll['payroll_period_id']
            );
        }

        $paymentMethod =
            $_POST['payment_method'] ?? '';

        $referenceNumber =
            trim(
                $_POST['reference_number'] ?? ''
            );

        $allowedMethods = [
            'cash',
            'bank_transfer',
            'gcash',
            'other'
        ];

        if (!in_array(
            $paymentMethod,
            $allowedMethods,
            true
        )) {
            Session::flash(
                'error',
                'Invalid payment method.'
            );

            $this->redirect(
                '/payroll/view/'
                . $payroll['payroll_period_id']
            );
        }

        try {

            $model->createPayment([
                'payroll_id' =>
                    (int) $payroll['id'],

                'payment_method' =>
                    $paymentMethod,

                'amount' =>
                    $payroll['net_pay'],

                'reference_number' =>
                    $referenceNumber,

                'paid_by' =>
                    Auth::id()
            ]);

            $model->updatePayrollStatus(
                (int) $payroll['id'],
                'paid'
            );

            Session::flash(
                'success',
                'Payroll payment recorded successfully.'
            );

        } catch (Throwable $e) {

            Session::flash(
                'error',
                'Unable to record payment: '
                . $e->getMessage()
            );
        }

        $this->redirect(
            '/payroll/view/'
            . $payroll['payroll_period_id']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PAYSLIP
    |--------------------------------------------------------------------------
    */

    public function payslip(int $id): void
    {
        Auth::requirePermission('payroll.view');

        $model = $this->model();

        $payroll =
            $model->findPayroll($id);

        if (!$payroll) {
            Session::flash(
                'error',
                'Payroll record not found.'
            );

            $this->redirect('/payroll');
        }

        $items =
            $model->getPayrollItems($id);

        $payments =
            $model->getPayments($id);

        $settingsModel = new SettingsModel();

$settings = $settingsModel->getAll();

$settingValue = static function (
    array $settings,
    string $key,
    string $default = ''
): string {
    foreach ($settings as $setting) {
        if (($setting['setting_key'] ?? '') === $key) {
            return (string) ($setting['setting_value'] ?? $default);
        }
    }

    return $default;
};

$companyName = $settingValue(
    $settings,
    'company_name',
    'My Company'
);

$companyAddress = $settingValue(
    $settings,
    'company_address'
);

$companyPhone = $settingValue(
    $settings,
    'company_phone'
);

$receiptFooter = $settingValue(
    $settings,
    'receipt_footer',
    'This is a computer-generated payslip.'
);

$this->view(
    'modules/Payroll/Views/payslip',
    [
        'payroll'        => $payroll,
        'items'          => $items,
        'payments'       => $payments,
        'companyName'    => $companyName,
        'companyAddress' => $companyAddress,
        'companyPhone'   => $companyPhone,
        'receiptFooter'  => $receiptFooter
    ]

        );
    }

    /*
    |--------------------------------------------------------------------------
    | RULES
    |--------------------------------------------------------------------------
    */

    private function getActiveRules(
        PayrollModel $model,
        string $date
    ): array {
        $stmt = $model->db->prepare("
            SELECT *
            FROM payroll_rules
            WHERE status = 'active'
              AND (
                    effective_from IS NULL
                    OR effective_from <= ?
              )
              AND (
                    effective_to IS NULL
                    OR effective_to >= ?
              )
            ORDER BY id ASC
        ");

        $stmt->execute([
            $date,
            $date
        ]);

        return $stmt->fetchAll();
    }

    private function getStatutoryProfiles(array $rules): array
    {
        $profiles = [];

        foreach ($rules as $rule) {
            if (($rule['rule_type'] ?? '') !== 'statutory') {
                continue;
            }

            $code = strtolower(trim($rule['rule_code'] ?? ''));
            if (!in_array($code, ['sss', 'philhealth', 'pagibig', 'bir_tax'], true)) {
                continue;
            }

            $configuration = json_decode($rule['formula'] ?? '', true);
            if (!is_array($configuration)) {
                throw new RuntimeException(
                    'Invalid JSON configuration for statutory payroll rule ' . ($rule['rule_code'] ?? $code) . '.'
                );
            }

            $configuration['enabled'] = true;
            $profiles[$code] = $configuration;
        }

        return $profiles;
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION HELPERS
    |--------------------------------------------------------------------------
    */

    private function dbTransactionStart(
        PayrollModel $model
    ): void {
        if (!$model->db->inTransaction()) {
            $model->db->beginTransaction();
        }
    }

    private function dbCommit(
        PayrollModel $model
    ): void {
        if ($model->db->inTransaction()) {
            $model->db->commit();
        }
    }

    private function dbRollback(
        PayrollModel $model
    ): void {
        if ($model->db->inTransaction()) {
            $model->db->rollBack();
        }
    }
}