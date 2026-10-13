<?php

require_once BASE_PATH .
    '/modules/Settings/Models/SettingsModel.php';


class SettingsController extends Controller
{
    private SettingsModel $settingsModel;


    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
    }


    /**
     * Settings page.
     */
    public function index(): void
    {
        Auth::requirePermission(
            'settings.view'
        );


        $settings =
            $this->settingsModel->getGrouped();


        $success =
            Session::getFlash('success');

        $error =
            Session::getFlash('error');


        $this->view(
            'modules/Settings/Views/index',
            [
                'settings' => $settings,
                'success'  => $success,
                'error'    => $error
            ]
        );
    }


    /**
     * Save settings.
     */
    public function save(): void
    {
        Auth::requirePermission(
            'settings.edit'
        );


        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
        ) {
            $this->redirect('/settings');
        }


        /*
         * Collect submitted values.
         */
        $posted = $_POST['settings'] ?? [];


        if (!is_array($posted)) {
            $posted = [];
        }


        /*
         * Define which settings the system
         * allows the user to modify.
         */
        $allowedSettings = [

            /*
             * Company
             */
            'company_name' => [
                'type' => 'text',
                'group' => 'company',
                'description' =>
                    'Company or business name'
            ],

            'company_address' => [
                'type' => 'text',
                'group' => 'company',
                'description' =>
                    'Company address'
            ],

            'company_phone' => [
                'type' => 'text',
                'group' => 'company',
                'description' =>
                    'Company phone number'
            ],

            'company_email' => [
                'type' => 'text',
                'group' => 'company',
                'description' =>
                    'Company email address'
            ],


            /*
             * POS
             */
            'currency' => [
                'type' => 'text',
                'group' => 'pos',
                'description' =>
                    'Currency code'
            ],

            'currency_symbol' => [
                'type' => 'text',
                'group' => 'pos',
                'description' =>
                    'Currency symbol'
            ],

            'tax_enabled' => [
                'type' => 'boolean',
                'group' => 'pos',
                'description' =>
                    'Enable tax calculation'
            ],

            'tax_rate' => [
                'type' => 'number',
                'group' => 'pos',
                'description' =>
                    'Default tax percentage'
            ],


            /*
             * Receipt
             */
            'receipt_header' => [
                'type' => 'text',
                'group' => 'receipt',
                'description' =>
                    'Receipt header'
            ],

            'receipt_footer' => [
                'type' => 'text',
                'group' => 'receipt',
                'description' =>
                    'Receipt footer'
            ],


            /*
             * Payroll
             */
            'payroll_frequency' => [
                'type' => 'text',
                'group' => 'payroll',
                'description' =>
                    'Payroll frequency'
            ],

            'standard_work_hours' => [
                'type' => 'number',
                'group' => 'payroll',
                'description' =>
                    'Standard working hours per day'
            ],

            'overtime_rate' => [
                'type' => 'number',
                'group' => 'payroll',
                'description' =>
                    'Default overtime multiplier'
            ],

            'monthly_workdays_per_period' => [
                'type' => 'number',
                'group' => 'payroll',
                'description' =>
                    'Monthly-paid salary divisor for overtime hourly rate'
            ],


            /*
             * Attendance
             */
            'late_grace_minutes' => [
                'type' => 'number',
                'group' => 'attendance',
                'description' =>
                    'Allowed late minutes'
            ],

            'work_start_time' => [
                'type' => 'text',
                'group' => 'attendance',
                'description' =>
                    'Default work start time'
            ],

            'work_end_time' => [
                'type' => 'text',
                'group' => 'attendance',
                'description' =>
                    'Default work end time'
            ],


            /*
             * Inventory
             */
            'low_stock_threshold' => [
                'type' => 'number',
                'group' => 'inventory',
                'description' =>
                    'Default low stock threshold'
            ],


            /*
             * Security
             */
            'session_timeout' => [
                'type' => 'number',
                'group' => 'security',
                'description' =>
                    'Session timeout in minutes'
            ],

            'max_login_attempts' => [
                'type' => 'number',
                'group' => 'security',
                'description' =>
                    'Maximum failed login attempts'
            ],

            'password_min_length' => [
                'type' => 'number',
                'group' => 'security',
                'description' =>
                    'Minimum password length'
            ],

            'nav_dashboard' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show Dashboard navigation link'
            ],
            'nav_pos' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show POS navigation link'
            ],
            'nav_products' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show Products navigation link'
            ],
            'nav_inventory' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show Inventory navigation link'
            ],
            'nav_sales_report' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show Sales Report navigation link'
            ],
            'nav_tables' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show Tables navigation link'
            ],
            'nav_employees' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show Employees navigation link'
            ],
            'nav_departments' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show Departments navigation link'
            ],
            'nav_positions' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show Positions navigation link'
            ],
            'nav_schedules' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show Schedules navigation link'
            ],
            'nav_attendance' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show Attendance navigation link'
            ],
            'nav_payroll' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show Payroll navigation link'
            ],
            'nav_users' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show Users navigation link'
            ],
            'nav_roles' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show Roles navigation link'
            ],
            'nav_settings' => [
                'type' => 'boolean', 'group' => 'navigation',
                'description' => 'Show Settings navigation link'
            ]
        ];


        try {

            $this->settingsModel->db->beginTransaction();


            foreach (
                $allowedSettings as $key => $definition
            ) {

                /*
                 * Checkbox values.
                 */
                if (
                    $definition['type'] === 'boolean'
                ) {

                    $value =
                        isset($posted[$key])
                        ? '1'
                        : '0';

                } else {

                    $value =
                        $posted[$key] ?? '';

                    if (
                        is_array($value)
                    ) {
                        $value = '';
                    }

                    $value = trim(
                        (string) $value
                    );
                }


                /*
                 * Basic validation.
                 */
                if (
                    $definition['type'] === 'number'
                    && $value !== ''
                    && !is_numeric($value)
                ) {

                    throw new InvalidArgumentException(
                        $key .
                        ' must be a valid number.'
                    );
                }


                /*
                 * Tax rate.
                 */
                if (
                    $key === 'tax_rate'
                    && $value !== ''
                    && (
                        (float) $value < 0
                        ||
                        (float) $value > 100
                    )
                ) {

                    throw new InvalidArgumentException(
                        'Tax rate must be between 0 and 100.'
                    );
                }


                /*
                 * Work hours.
                 */
                if (
                    $key === 'standard_work_hours'
                    && $value !== ''
                    && (
                        (float) $value <= 0
                        ||
                        (float) $value > 24
                    )
                ) {

                    throw new InvalidArgumentException(
                        'Standard work hours must be between 0 and 24.'
                    );
                }

                if (
                    $key === 'monthly_workdays_per_period'
                    && $value !== ''
                    && ((float) $value <= 0 || (float) $value > 31)
                ) {
                    throw new InvalidArgumentException(
                        'Monthly workday divisor must be greater than 0 and no more than 31.'
                    );
                }


                /*
                 * Save setting.
                 */
                $this->settingsModel->set(
                    $key,
                    $value,
                    $definition['type'],
                    $definition['group'],
                    $definition['description']
                );
            }


            $this->settingsModel->db->commit();


            Session::flash(
                'success',
                'Settings saved successfully.'
            );


        } catch (Throwable $e) {

            if (
                $this->settingsModel->db->inTransaction()
            ) {
                $this->settingsModel->db->rollBack();
            }


            error_log(
                'SettingsController::save - ' .
                $e->getMessage()
            );


            Session::flash(
                'error',
                $e instanceof InvalidArgumentException
                    ? $e->getMessage()
                    : 'Unable to save settings.'
            );
        }


        $this->redirect('/settings');
    }
}