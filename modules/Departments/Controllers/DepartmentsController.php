<?php

require_once BASE_PATH .
    '/modules/Departments/Models/DepartmentsModel.php';


class DepartmentsController extends Controller
{
    private DepartmentsModel $departmentsModel;


    public function __construct()
    {
        $this->departmentsModel =
            new DepartmentsModel();
    }


    /**
     * Department list.
     */
    public function index(): void
    {
        Auth::requirePermission(
            'departments.view'
        );


        $departments =
            $this->departmentsModel->getAll();


        $success =
            Session::getFlash('success');

        $error =
            Session::getFlash('error');


        $this->view(
            'modules/Departments/Views/index',
            [
                'departments' => $departments,
                'success'     => $success,
                'error'       => $error
            ]
        );
    }


    /**
     * Create form.
     */
    public function create(): void
    {
        Auth::requirePermission(
            'departments.create'
        );


        $error =
            Session::getFlash('error');


        $this->view(
            'modules/Departments/Views/create',
            [
                'error' => $error
            ]
        );
    }


    /**
     * Store department.
     */
    public function store(): void
    {
        Auth::requirePermission(
            'departments.create'
        );


        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
        ) {
            $this->redirect(
                '/departments/create'
            );
        }


        $name =
            trim($_POST['name'] ?? '');

        $description =
            trim($_POST['description'] ?? '');

        $status =
            $_POST['status'] ?? 'active';


        /*
         * Validate name.
         */
        if ($name === '') {

            Session::flash(
                'error',
                'Department name is required.'
            );

            $this->redirect(
                '/departments/create'
            );
        }


        if (strlen($name) < 2) {

            Session::flash(
                'error',
                'Department name must be at least 2 characters.'
            );

            $this->redirect(
                '/departments/create'
            );
        }


        if (strlen($name) > 100) {

            Session::flash(
                'error',
                'Department name cannot exceed 100 characters.'
            );

            $this->redirect(
                '/departments/create'
            );
        }


        /*
         * Validate status.
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
         * Duplicate check.
         */
        if (
            $this->departmentsModel
                ->findByName($name)
        ) {

            Session::flash(
                'error',
                'A department with this name already exists.'
            );

            $this->redirect(
                '/departments/create'
            );
        }


        try {

            $this->departmentsModel->create([
                'name' =>
                    $name,

                'description' =>
                    $description,

                'status' =>
                    $status
            ]);


            Session::flash(
                'success',
                'Department created successfully.'
            );


        } catch (Throwable $e) {

            error_log(
                'DepartmentsController::store - ' .
                $e->getMessage()
            );


            Session::flash(
                'error',
                'Unable to create department.'
            );
        }


        $this->redirect(
            '/departments'
        );
    }


    /**
     * Edit form.
     */
    public function edit(int $id): void
    {
        Auth::requirePermission(
            'departments.edit'
        );


        $department =
            $this->departmentsModel->find($id);


        if (!$department) {

            Session::flash(
                'error',
                'Department not found.'
            );

            $this->redirect(
                '/departments'
            );
        }


        $error =
            Session::getFlash('error');


        $this->view(
            'modules/Departments/Views/edit',
            [
                'department' =>
                    $department,

                'error' =>
                    $error
            ]
        );
    }


    /**
     * Update department.
     */
    public function update(int $id): void
    {
        Auth::requirePermission(
            'departments.edit'
        );


        if (
            $_SERVER['REQUEST_METHOD'] !== 'POST'
        ) {
            $this->redirect(
                '/departments/edit/' . $id
            );
        }


        $department =
            $this->departmentsModel->find($id);


        if (!$department) {

            Session::flash(
                'error',
                'Department not found.'
            );

            $this->redirect(
                '/departments'
            );
        }


        $name =
            trim($_POST['name'] ?? '');

        $description =
            trim($_POST['description'] ?? '');

        $status =
            $_POST['status'] ?? 'active';


        if ($name === '') {

            Session::flash(
                'error',
                'Department name is required.'
            );

            $this->redirect(
                '/departments/edit/' . $id
            );
        }


        if (strlen($name) < 2) {

            Session::flash(
                'error',
                'Department name must be at least 2 characters.'
            );

            $this->redirect(
                '/departments/edit/' . $id
            );
        }


        if (strlen($name) > 100) {

            Session::flash(
                'error',
                'Department name cannot exceed 100 characters.'
            );

            $this->redirect(
                '/departments/edit/' . $id
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
            $this->departmentsModel
                ->findByName($name, $id)
        ) {

            Session::flash(
                'error',
                'Another department already uses this name.'
            );

            $this->redirect(
                '/departments/edit/' . $id
            );
        }


        try {

            $this->departmentsModel->update(
                $id,
                [
                    'name' =>
                        $name,

                    'description' =>
                        $description,

                    'status' =>
                        $status
                ]
            );


            Session::flash(
                'success',
                'Department updated successfully.'
            );


        } catch (Throwable $e) {

            error_log(
                'DepartmentsController::update - ' .
                $e->getMessage()
            );


            Session::flash(
                'error',
                'Unable to update department.'
            );
        }


        $this->redirect(
            '/departments'
        );
    }


    /**
     * Toggle active/inactive.
     */
    public function toggleStatus(int $id): void
    {
        Auth::requirePermission(
            'departments.edit'
        );


        $department =
            $this->departmentsModel->find($id);


        if (!$department) {

            Session::flash(
                'error',
                'Department not found.'
            );

            $this->redirect(
                '/departments'
            );
        }


        try {

            $this->departmentsModel
                ->toggleStatus($id);


            Session::flash(
                'success',
                'Department status updated.'
            );


        } catch (Throwable $e) {

            error_log(
                'DepartmentsController::toggleStatus - ' .
                $e->getMessage()
            );


            Session::flash(
                'error',
                'Unable to change department status.'
            );
        }


        $this->redirect(
            '/departments'
        );
    }


    /**
     * Delete department.
     */
    public function delete(int $id): void
    {
        Auth::requirePermission(
            'departments.delete'
        );


        $department =
            $this->departmentsModel->find($id);


        if (!$department) {

            Session::flash(
                'error',
                'Department not found.'
            );

            $this->redirect(
                '/departments'
            );
        }


        /*
         * Prevent deletion when employees
         * are assigned.
         */
        $employeeCount =
            $this->departmentsModel
                ->countEmployees($id);


        if ($employeeCount > 0) {

            Session::flash(
                'error',
                'This department cannot be deleted because '
                . $employeeCount
                . ' employee(s) are assigned to it.'
            );

            $this->redirect(
                '/departments'
            );
        }


        try {

            $deleted =
                $this->departmentsModel
                    ->delete($id);


            if (!$deleted) {

                throw new RuntimeException(
                    'Department could not be deleted.'
                );
            }


            Session::flash(
                'success',
                'Department deleted successfully.'
            );


        } catch (Throwable $e) {

            error_log(
                'DepartmentsController::delete - ' .
                $e->getMessage()
            );


            Session::flash(
                'error',
                'Unable to delete department.'
            );
        }


        $this->redirect(
            '/departments'
        );
    }
}