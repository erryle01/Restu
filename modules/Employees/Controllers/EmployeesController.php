<?php

require_once BASE_PATH .
    '/modules/Employees/Models/EmployeesModel.php';

class EmployeesController extends Controller
{
    private EmployeesModel $employeesModel;

    public function __construct()
    {
        $this->employeesModel = new EmployeesModel();
    }

    public function index(): void
    {
        Auth::requirePermission('employees.view');

        $employees =
            $this->employeesModel->getAll();

        $success =
            Session::getFlash('success');

        $error =
            Session::getFlash('error');

        $this->view(
            'modules/Employees/Views/index',
            [
                'employees' => $employees,
                'success' => $success,
                'error' => $error
            ]
        );
    }

    public function create(): void
    {
        Auth::requirePermission('employees.create');

        $departments =
            $this->employeesModel->getDepartments();

        $positions =
            $this->employeesModel->getPositions();

        $error =
            Session::getFlash('error');

        $this->view(
            'modules/Employees/Views/create',
            [
                'departments' => $departments,
                'positions' => $positions,
                'error' => $error
            ]
        );
    }

    public function store(): void
    {
        Auth::requirePermission('employees.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/employees/create');
        }

        $employeeCode =
            trim($_POST['employee_code'] ?? '');

        $firstName =
            trim($_POST['first_name'] ?? '');

        $middleName =
            trim($_POST['middle_name'] ?? '');

        $lastName =
            trim($_POST['last_name'] ?? '');

        $email =
            trim($_POST['email'] ?? '');

        $phone =
            trim($_POST['phone'] ?? '');

        $address =
            trim($_POST['address'] ?? '');

        $departmentId =
            (int) ($_POST['department_id'] ?? 0);

        $positionId =
            (int) ($_POST['position_id'] ?? 0);

        $hireDate =
            trim($_POST['hire_date'] ?? '');

        $employmentStatus =
            $_POST['employment_status'] ?? 'active';

        $salaryType =
            $_POST['salary_type'] ?? 'monthly';

        $basicSalary =
            $_POST['basic_salary'] ?? '0';

        $salaryValue =
            is_numeric($basicSalary)
                ? (float) $basicSalary
                : -1;

        if ($employeeCode === '') {
            $this->error(
                'Employee code is required.',
                '/employees/create'
            );
        }

        if (strlen($employeeCode) > 50) {
            $this->error(
                'Employee code cannot exceed 50 characters.',
                '/employees/create'
            );
        }

        if ($firstName === '') {
            $this->error(
                'First name is required.',
                '/employees/create'
            );
        }

        if ($lastName === '') {
            $this->error(
                'Last name is required.',
                '/employees/create'
            );
        }

        if ($departmentId <= 0) {
            $this->error(
                'Please select a department.',
                '/employees/create'
            );
        }

        if ($positionId <= 0) {
            $this->error(
                'Please select a position.',
                '/employees/create'
            );
        }

        if (!$this->employeesModel
            ->departmentExists($departmentId)
        ) {
            $this->error(
                'Selected department is invalid.',
                '/employees/create'
            );
        }

        if (!$this->employeesModel
            ->positionExists(
                $positionId,
                $departmentId
            )
        ) {
            $this->error(
                'Selected position does not belong to the selected department.',
                '/employees/create'
            );
        }

        if ($hireDate === '') {
            $this->error(
                'Hire date is required.',
                '/employees/create'
            );
        }

        if (
            !in_array(
                $employmentStatus,
                [
                    'active',
                    'inactive',
                    'on_leave',
                    'terminated'
                ],
                true
            )
        ) {
            $this->error(
                'Invalid employment status.',
                '/employees/create'
            );
        }

        if (
            !in_array(
                $salaryType,
                [
                    'monthly',
                    'daily',
                    'hourly'
                ],
                true
            )
        ) {
            $this->error(
                'Invalid salary type.',
                '/employees/create'
            );
        }

        if ($salaryValue < 0) {
            $this->error(
                'Basic salary must be a valid amount.',
                '/employees/create'
            );
        }

        if ($this->employeesModel
            ->findByEmployeeCode($employeeCode)
        ) {
            $this->error(
                'Employee code already exists.',
                '/employees/create'
            );
        }

        try {
            $this->employeesModel->db->beginTransaction();

            $this->employeesModel->create([
                'employee_code' =>
                    $employeeCode,

                'first_name' =>
                    $firstName,

                'middle_name' =>
                    $middleName,

                'last_name' =>
                    $lastName,

                'email' =>
                    $email,

                'phone' =>
                    $phone,

                'address' =>
                    $address,

                'department_id' =>
                    $departmentId,

                'position_id' =>
                    $positionId,

                'hire_date' =>
                    $hireDate,

                'employment_status' =>
                    $employmentStatus,

                'salary_type' =>
                    $salaryType,

                'basic_salary' =>
                    number_format(
                        $salaryValue,
                        2,
                        '.',
                        ''
                    )
            ]);

            $this->employeesModel->db->commit();

            Session::flash(
                'success',
                'Employee created successfully.'
            );

            $this->redirect('/employees');

        } catch (Throwable $e) {

            if (
                $this->employeesModel
                    ->db
                    ->inTransaction()
            ) {
                $this->employeesModel
                    ->db
                    ->rollBack();
            }

            error_log(
                'EmployeesController::store - ' .
                $e->getMessage()
            );

            Session::flash(
                'error',
                'Unable to create employee.'
            );

            $this->redirect('/employees/create');
        }
    }

    public function edit(int $id): void
    {
        Auth::requirePermission('employees.edit');

        $employee =
            $this->employeesModel->find($id);

        if (!$employee) {
            Session::flash(
                'error',
                'Employee not found.'
            );

            $this->redirect('/employees');
        }

        $departments =
            $this->employeesModel->getDepartments();

        $positions =
            $this->employeesModel->getPositions(
                (int) $employee['department_id']
            );

        $error =
            Session::getFlash('error');

        $this->view(
            'modules/Employees/Views/edit',
            [
                'employee' => $employee,
                'departments' => $departments,
                'positions' => $positions,
                'error' => $error
            ]
        );
    }

    public function update(int $id): void
    {
        Auth::requirePermission('employees.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(
                '/employees/edit/' . $id
            );
        }

        $employee =
            $this->employeesModel->find($id);

        if (!$employee) {
            Session::flash(
                'error',
                'Employee not found.'
            );

            $this->redirect('/employees');
        }

        $employeeCode =
            trim($_POST['employee_code'] ?? '');

        $firstName =
            trim($_POST['first_name'] ?? '');

        $middleName =
            trim($_POST['middle_name'] ?? '');

        $lastName =
            trim($_POST['last_name'] ?? '');

        $email =
            trim($_POST['email'] ?? '');

        $phone =
            trim($_POST['phone'] ?? '');

        $address =
            trim($_POST['address'] ?? '');

        $departmentId =
            (int) ($_POST['department_id'] ?? 0);

        $positionId =
            (int) ($_POST['position_id'] ?? 0);

        $hireDate =
            trim($_POST['hire_date'] ?? '');

        $employmentStatus =
            $_POST['employment_status'] ?? 'active';

        $salaryType =
            $_POST['salary_type'] ?? 'monthly';

        $basicSalary =
            $_POST['basic_salary'] ?? '0';

        $salaryValue =
            is_numeric($basicSalary)
                ? (float) $basicSalary
                : -1;

        if ($employeeCode === '') {
            $this->error(
                'Employee code is required.',
                '/employees/edit/' . $id
            );
        }

        if ($firstName === '') {
            $this->error(
                'First name is required.',
                '/employees/edit/' . $id
            );
        }

        if ($lastName === '') {
            $this->error(
                'Last name is required.',
                '/employees/edit/' . $id
            );
        }

        if ($departmentId <= 0) {
            $this->error(
                'Please select a department.',
                '/employees/edit/' . $id
            );
        }

        if ($positionId <= 0) {
            $this->error(
                'Please select a position.',
                '/employees/edit/' . $id
            );
        }

        if (!$this->employeesModel
            ->departmentExists($departmentId)
        ) {
            $this->error(
                'Selected department is invalid.',
                '/employees/edit/' . $id
            );
        }

        if (!$this->employeesModel
            ->positionExists(
                $positionId,
                $departmentId
            )
        ) {
            $this->error(
                'Selected position does not belong to the selected department.',
                '/employees/edit/' . $id
            );
        }

        if ($hireDate === '') {
            $this->error(
                'Hire date is required.',
                '/employees/edit/' . $id
            );
        }

        if (
            !in_array(
                $employmentStatus,
                [
                    'active',
                    'inactive',
                    'on_leave',
                    'terminated'
                ],
                true
            )
        ) {
            $this->error(
                'Invalid employment status.',
                '/employees/edit/' . $id
            );
        }

        if (
            !in_array(
                $salaryType,
                [
                    'monthly',
                    'daily',
                    'hourly'
                ],
                true
            )
        ) {
            $this->error(
                'Invalid salary type.',
                '/employees/edit/' . $id
            );
        }

        if ($salaryValue < 0) {
            $this->error(
                'Basic salary must be a valid amount.',
                '/employees/edit/' . $id
            );
        }

        if (
            $this->employeesModel
                ->findByEmployeeCode(
                    $employeeCode,
                    $id
                )
        ) {
            $this->error(
                'Employee code already exists.',
                '/employees/edit/' . $id
            );
        }

        try {
            $this->employeesModel->db->beginTransaction();

            $this->employeesModel->update(
                $id,
                [
                    'employee_code' =>
                        $employeeCode,

                    'first_name' =>
                        $firstName,

                    'middle_name' =>
                        $middleName,

                    'last_name' =>
                        $lastName,

                    'email' =>
                        $email,

                    'phone' =>
                        $phone,

                    'address' =>
                        $address,

                    'department_id' =>
                        $departmentId,

                    'position_id' =>
                        $positionId,

                    'hire_date' =>
                        $hireDate,

                    'employment_status' =>
                        $employmentStatus,

                    'salary_type' =>
                        $salaryType,

                    'basic_salary' =>
                        number_format(
                            $salaryValue,
                            2,
                            '.',
                            ''
                        )
                ]
            );

            $this->employeesModel
                ->db
                ->commit();

            Session::flash(
                'success',
                'Employee updated successfully.'
            );

            $this->redirect('/employees');

        } catch (Throwable $e) {

            if (
                $this->employeesModel
                    ->db
                    ->inTransaction()
            ) {
                $this->employeesModel
                    ->db
                    ->rollBack();
            }

            error_log(
                'EmployeesController::update - ' .
                $e->getMessage()
            );

            Session::flash(
                'error',
                'Unable to update employee.'
            );

            $this->redirect(
                '/employees/edit/' . $id
            );
        }
    }

    public function delete(int $id): void
    {
        Auth::requirePermission('employees.delete');

        $employee =
            $this->employeesModel->find($id);

        if (!$employee) {
            Session::flash(
                'error',
                'Employee not found.'
            );

            $this->redirect('/employees');
        }

        try {
            $this->employeesModel->delete($id);

            Session::flash(
                'success',
                'Employee deleted successfully.'
            );

        } catch (Throwable $e) {

            error_log(
                'EmployeesController::delete - ' .
                $e->getMessage()
            );

            Session::flash(
                'error',
                'Unable to delete employee. The employee may already be referenced by attendance, payroll, or another record.'
            );
        }

        $this->redirect('/employees');
    }

    private function error(
        string $message,
        string $redirect
    ): never {
        Session::flash(
            'error',
            $message
        );

        $this->redirect($redirect);
    }
}