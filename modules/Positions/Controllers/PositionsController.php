<?php

require_once BASE_PATH .
    '/modules/Positions/Models/PositionsModel.php';


class PositionsController extends Controller
{
    private PositionsModel $positionsModel;


    public function __construct()
    {
        $this->positionsModel =
            new PositionsModel();
    }


    /**
     * Position list.
     */
    public function index(): void
    {
        Auth::requirePermission(
            'positions.view'
        );

        $positions =
            $this->positionsModel->getAll();

        $success =
            Session::getFlash('success');

        $error =
            Session::getFlash('error');

        $this->view(
            'modules/Positions/Views/index',
            [
                'positions' => $positions,
                'success'   => $success,
                'error'     => $error
            ]
        );
    }


    /**
     * Create form.
     */
    public function create(): void
    {
        Auth::requirePermission(
            'positions.create'
        );

        $departments =
            $this->positionsModel->getDepartments();

        $error =
            Session::getFlash('error');

        $this->view(
            'modules/Positions/Views/create',
            [
                'departments' => $departments,
                'error'       => $error
            ]
        );
    }


    /**
     * Store position.
     */
    public function store(): void
    {
        Auth::requirePermission(
            'positions.create'
        );

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
        ) {
            $this->redirect(
                '/positions/create'
            );
        }


        $departmentId =
            (int) ($_POST['department_id'] ?? 0);

        $name =
            trim($_POST['name'] ?? '');

        $description =
            trim($_POST['description'] ?? '');

        $salaryType =
            $_POST['salary_type'] ?? 'monthly';

        $basicSalary =
            trim($_POST['basic_salary'] ?? '0');

        $status =
            $_POST['status'] ?? 'active';


        /*
         * Department validation.
         */
        if ($departmentId <= 0) {

            Session::flash(
                'error',
                'Please select a department.'
            );

            $this->redirect(
                '/positions/create'
            );
        }


        if (
            !$this->positionsModel
                ->departmentExists($departmentId)
        ) {

            Session::flash(
                'error',
                'Selected department does not exist.'
            );

            $this->redirect(
                '/positions/create'
            );
        }


        /*
         * Name validation.
         */
        if ($name === '') {

            Session::flash(
                'error',
                'Position name is required.'
            );

            $this->redirect(
                '/positions/create'
            );
        }


        if (strlen($name) < 2) {

            Session::flash(
                'error',
                'Position name must be at least 2 characters.'
            );

            $this->redirect(
                '/positions/create'
            );
        }


        if (strlen($name) > 100) {

            Session::flash(
                'error',
                'Position name cannot exceed 100 characters.'
            );

            $this->redirect(
                '/positions/create'
            );
        }


        /*
         * Salary type.
         */
        if (
            !in_array(
                $salaryType,
                ['monthly', 'daily', 'hourly'],
                true
            )
        ) {
            $salaryType = 'monthly';
        }


        /*
         * Salary validation.
         */
        if (
            $basicSalary === '' ||
            !is_numeric($basicSalary)
        ) {

            Session::flash(
                'error',
                'Basic salary must be a valid number.'
            );

            $this->redirect(
                '/positions/create'
            );
        }


        $basicSalary =
            (float) $basicSalary;


        if ($basicSalary < 0) {

            Session::flash(
                'error',
                'Basic salary cannot be negative.'
            );

            $this->redirect(
                '/positions/create'
            );
        }


        /*
         * Status.
         */
        if (
            !in_array(
                $status,
                ['active', 'inactive'],
                true
            )
        ) {
            $status = 'active';
        }


        /*
         * Duplicate.
         */
        if (
            $this->positionsModel->findByName(
                $departmentId,
                $name
            )
        ) {

            Session::flash(
                'error',
                'This position already exists in the selected department.'
            );

            $this->redirect(
                '/positions/create'
            );
        }


        try {

            $this->positionsModel->create([
                'department_id' =>
                    $departmentId,

                'name' =>
                    $name,

                'description' =>
                    $description,

                'salary_type' =>
                    $salaryType,

                'basic_salary' =>
                    $basicSalary,

                'status' =>
                    $status
            ]);


            Session::flash(
                'success',
                'Position created successfully.'
            );

        } catch (Throwable $e) {

            error_log(
                'PositionsController::store - ' .
                $e->getMessage()
            );

            Session::flash(
                'error',
                'Unable to create position.'
            );
        }


        $this->redirect(
            '/positions'
        );
    }


    /**
     * Edit form.
     */
    public function edit(int $id): void
    {
        Auth::requirePermission(
            'positions.edit'
        );

        $position =
            $this->positionsModel->find($id);

        if (!$position) {

            Session::flash(
                'error',
                'Position not found.'
            );

            $this->redirect(
                '/positions'
            );
        }


        $departments =
            $this->positionsModel->getDepartments();

        $error =
            Session::getFlash('error');


        $this->view(
            'modules/Positions/Views/edit',
            [
                'position'    => $position,
                'departments' => $departments,
                'error'       => $error
            ]
        );
    }


    /**
     * Update position.
     */
    public function update(int $id): void
    {
        Auth::requirePermission(
            'positions.edit'
        );

        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
        ) {
            $this->redirect(
                '/positions/edit/' . $id
            );
        }


        $position =
            $this->positionsModel->find($id);


        if (!$position) {

            Session::flash(
                'error',
                'Position not found.'
            );

            $this->redirect(
                '/positions'
            );
        }


        $departmentId =
            (int) ($_POST['department_id'] ?? 0);

        $name =
            trim($_POST['name'] ?? '');

        $description =
            trim($_POST['description'] ?? '');

        $salaryType =
            $_POST['salary_type'] ?? 'monthly';

        $basicSalary =
            trim($_POST['basic_salary'] ?? '0');

        $status =
            $_POST['status'] ?? 'active';


        if ($departmentId <= 0) {

            Session::flash(
                'error',
                'Please select a department.'
            );

            $this->redirect(
                '/positions/edit/' . $id
            );
        }


        if (
            !$this->positionsModel
                ->departmentExists($departmentId)
        ) {

            Session::flash(
                'error',
                'Selected department does not exist.'
            );

            $this->redirect(
                '/positions/edit/' . $id
            );
        }


        if ($name === '') {

            Session::flash(
                'error',
                'Position name is required.'
            );

            $this->redirect(
                '/positions/edit/' . $id
            );
        }


        if (strlen($name) < 2) {

            Session::flash(
                'error',
                'Position name must be at least 2 characters.'
            );

            $this->redirect(
                '/positions/edit/' . $id
            );
        }


        if (strlen($name) > 100) {

            Session::flash(
                'error',
                'Position name cannot exceed 100 characters.'
            );

            $this->redirect(
                '/positions/edit/' . $id
            );
        }


        if (
            !in_array(
                $salaryType,
                ['monthly', 'daily', 'hourly'],
                true
            )
        ) {
            $salaryType = 'monthly';
        }


        if (
            $basicSalary === '' ||
            !is_numeric($basicSalary)
        ) {

            Session::flash(
                'error',
                'Basic salary must be a valid number.'
            );

            $this->redirect(
                '/positions/edit/' . $id
            );
        }


        $basicSalary =
            (float) $basicSalary;


        if ($basicSalary < 0) {

            Session::flash(
                'error',
                'Basic salary cannot be negative.'
            );

            $this->redirect(
                '/positions/edit/' . $id
            );
        }


        if (
            !in_array(
                $status,
                ['active', 'inactive'],
                true
            )
        ) {
            $status = 'active';
        }


        if (
            $this->positionsModel->findByName(
                $departmentId,
                $name,
                $id
            )
        ) {

            Session::flash(
                'error',
                'Another position already uses this name in the selected department.'
            );

            $this->redirect(
                '/positions/edit/' . $id
            );
        }


        try {

            $this->positionsModel->update(
                $id,
                [
                    'department_id' =>
                        $departmentId,

                    'name' =>
                        $name,

                    'description' =>
                        $description,

                    'salary_type' =>
                        $salaryType,

                    'basic_salary' =>
                        $basicSalary,

                    'status' =>
                        $status
                ]
            );


            Session::flash(
                'success',
                'Position updated successfully.'
            );

        } catch (Throwable $e) {

            error_log(
                'PositionsController::update - ' .
                $e->getMessage()
            );

            Session::flash(
                'error',
                'Unable to update position.'
            );
        }


        $this->redirect(
            '/positions'
        );
    }


    /**
     * Toggle status.
     */
    public function toggleStatus(int $id): void
    {
        Auth::requirePermission(
            'positions.edit'
        );


        $position =
            $this->positionsModel->find($id);


        if (!$position) {

            Session::flash(
                'error',
                'Position not found.'
            );

            $this->redirect(
                '/positions'
            );
        }


        try {

            $this->positionsModel
                ->toggleStatus($id);

            Session::flash(
                'success',
                'Position status updated.'
            );

        } catch (Throwable $e) {

            error_log(
                'PositionsController::toggleStatus - ' .
                $e->getMessage()
            );

            Session::flash(
                'error',
                'Unable to change position status.'
            );
        }


        $this->redirect(
            '/positions'
        );
    }


    /**
     * Delete position.
     */
    public function delete(int $id): void
    {
        Auth::requirePermission(
            'positions.delete'
        );


        $position =
            $this->positionsModel->find($id);


        if (!$position) {

            Session::flash(
                'error',
                'Position not found.'
            );

            $this->redirect(
                '/positions'
            );
        }


        $employeeCount =
            $this->positionsModel
                ->countEmployees($id);


        if ($employeeCount > 0) {

            Session::flash(
                'error',
                'This position cannot be deleted because '
                . $employeeCount
                . ' employee(s) are assigned to it.'
            );

            $this->redirect(
                '/positions'
            );
        }


        try {

            $deleted =
                $this->positionsModel->delete($id);


            if (!$deleted) {

                throw new RuntimeException(
                    'Position could not be deleted.'
                );
            }


            Session::flash(
                'success',
                'Position deleted successfully.'
            );

        } catch (Throwable $e) {

            error_log(
                'PositionsController::delete - ' .
                $e->getMessage()
            );

            Session::flash(
                'error',
                'Unable to delete position.'
            );
        }


        $this->redirect(
            '/positions'
        );
    }
}