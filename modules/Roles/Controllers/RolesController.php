<?php

require_once BASE_PATH .
    '/modules/Roles/Models/RolesModel.php';


class RolesController extends Controller
{
    private RolesModel $rolesModel;


    public function __construct()
    {
        $this->rolesModel = new RolesModel();
    }


    /**
     * Role list.
     */
    public function index(): void
    {
        Auth::requirePermission('roles.view');

        $roles = $this->rolesModel->getAll();

        $success = Session::getFlash('success');
        $error   = Session::getFlash('error');

        $this->view(
            'modules/Roles/Views/index',
            [
                'roles'   => $roles,
                'success' => $success,
                'error'   => $error
            ]
        );
    }


    /**
     * Create role page.
     */
    public function create(): void
    {
        Auth::requirePermission('roles.create');

        $permissions =
            $this->rolesModel->getAllPermissions();

        $error = Session::getFlash('error');

        $this->view(
            'modules/Roles/Views/create',
            [
                'permissions' => $permissions,
                'error'       => $error
            ]
        );
    }


    /**
     * Store new role.
     */
    public function store(): void
    {
        Auth::requirePermission('roles.create');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/roles/create');
        }


        $name = trim(
            $_POST['name'] ?? ''
        );

        $description = trim(
            $_POST['description'] ?? ''
        );

        $permissionIds =
            $_POST['permissions'] ?? [];


        if (!is_array($permissionIds)) {
            $permissionIds = [];
        }


        /*
         * Validate role name.
         */
        if ($name === '') {

            Session::flash(
                'error',
                'Role name is required.'
            );

            $this->redirect('/roles/create');
        }


        if (strlen($name) < 2) {

            Session::flash(
                'error',
                'Role name must be at least 2 characters.'
            );

            $this->redirect('/roles/create');
        }


        if (strlen($name) > 50) {

            Session::flash(
                'error',
                'Role name cannot exceed 50 characters.'
            );

            $this->redirect('/roles/create');
        }


        /*
         * Check duplicate role.
         */
        if ($this->rolesModel->findByName($name)) {

            Session::flash(
                'error',
                'A role with this name already exists.'
            );

            $this->redirect('/roles/create');
        }


        try {

            $this->rolesModel->db->beginTransaction();


            /*
             * Create role.
             */
            $roleId = $this->rolesModel->create([
                'name'        => $name,
                'description' => $description
            ]);


            /*
             * Assign permissions.
             */
            $this->rolesModel->setPermissions(
                $roleId,
                $permissionIds
            );


            /*
             * Commit.
             */
            $this->rolesModel->db->commit();


            Session::flash(
                'success',
                'Role created successfully.'
            );

            $this->redirect('/roles');

        } catch (Throwable $e) {

            if (
                $this->rolesModel->db->inTransaction()
            ) {
                $this->rolesModel->db->rollBack();
            }


            error_log(
                'RolesController::store - ' .
                $e->getMessage()
            );


            Session::flash(
                'error',
                'Unable to create role.'
            );

            $this->redirect('/roles/create');
        }
    }


    /**
     * Edit role page.
     */
    public function edit(int $id): void
    {
        Auth::requirePermission('roles.edit');


        $role = $this->rolesModel->find($id);


        if (!$role) {

            Session::flash(
                'error',
                'Role not found.'
            );

            $this->redirect('/roles');
        }


        $permissions =
            $this->rolesModel->getAllPermissions();


        $selectedPermissions =
            $this->rolesModel->getRolePermissions($id);


        $error = Session::getFlash('error');


        $this->view(
            'modules/Roles/Views/edit',
            [
                'role'                => $role,
                'permissions'         => $permissions,
                'selectedPermissions' => $selectedPermissions,
                'error'               => $error
            ]
        );
    }


    /**
     * Update role.
     */
    public function update(int $id): void
    {
        Auth::requirePermission('roles.edit');


        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            $this->redirect(
                '/roles/edit/' . $id
            );
        }


        $role = $this->rolesModel->find($id);


        if (!$role) {

            Session::flash(
                'error',
                'Role not found.'
            );

            $this->redirect('/roles');
        }


        $name = trim(
            $_POST['name'] ?? ''
        );

        $description = trim(
            $_POST['description'] ?? ''
        );

        $permissionIds =
            $_POST['permissions'] ?? [];


        if (!is_array($permissionIds)) {
            $permissionIds = [];
        }


        /*
         * Validate role name.
         */
        if ($name === '') {

            Session::flash(
                'error',
                'Role name is required.'
            );

            $this->redirect(
                '/roles/edit/' . $id
            );
        }


        if (strlen($name) < 2) {

            Session::flash(
                'error',
                'Role name must be at least 2 characters.'
            );

            $this->redirect(
                '/roles/edit/' . $id
            );
        }


        if (strlen($name) > 50) {

            Session::flash(
                'error',
                'Role name cannot exceed 50 characters.'
            );

            $this->redirect(
                '/roles/edit/' . $id
            );
        }


        /*
         * Check duplicate name.
         */
        if (
            $this->rolesModel->findByName(
                $name,
                $id
            )
        ) {

            Session::flash(
                'error',
                'Another role already uses this name.'
            );

            $this->redirect(
                '/roles/edit/' . $id
            );
        }


        /*
         * Protect Super Admin.
         *
         * Super Admin can have its description
         * and permissions edited, but cannot be renamed.
         */
        if (
            strcasecmp(
                $role['name'],
                'Super Admin'
            ) === 0
            &&
            strcasecmp(
                $name,
                'Super Admin'
            ) !== 0
        ) {

            Session::flash(
                'error',
                'The Super Admin role cannot be renamed.'
            );

            $this->redirect(
                '/roles/edit/' . $id
            );
        }


        try {

            $this->rolesModel->db->beginTransaction();


            /*
             * Update role information.
             */
            $this->rolesModel->update(
                $id,
                [
                    'name'        => $name,
                    'description' => $description
                ]
            );


            /*
             * Replace permissions.
             */
            $this->rolesModel->setPermissions(
                $id,
                $permissionIds
            );


            /*
             * Commit changes.
             */
            $this->rolesModel->db->commit();


            Session::flash(
                'success',
                'Role updated successfully.'
            );


            $this->redirect('/roles');

        } catch (Throwable $e) {

            if (
                $this->rolesModel->db->inTransaction()
            ) {
                $this->rolesModel->db->rollBack();
            }


            error_log(
                'RolesController::update - ' .
                $e->getMessage()
            );


            Session::flash(
                'error',
                'Unable to update role.'
            );


            $this->redirect(
                '/roles/edit/' . $id
            );
        }
    }


    /**
     * Delete role.
     */
    public function delete(int $id): void
    {
        Auth::requirePermission('roles.delete');


        $role = $this->rolesModel->find($id);


        if (!$role) {

            Session::flash(
                'error',
                'Role not found.'
            );

            $this->redirect('/roles');
        }


        /*
         * Never delete Super Admin.
         */
        if (
            strcasecmp(
                $role['name'],
                'Super Admin'
            ) === 0
        ) {

            Session::flash(
                'error',
                'The Super Admin role cannot be deleted.'
            );

            $this->redirect('/roles');
        }


        /*
         * Check assigned users.
         */
        $userCount =
            $this->rolesModel->countUsers($id);


        if ($userCount > 0) {

            Session::flash(
                'error',
                'This role cannot be deleted because it is assigned to '
                . $userCount
                . ' user(s).'
            );

            $this->redirect('/roles');
        }


        try {

            $this->rolesModel->db->beginTransaction();


            /*
             * Delete role permissions.
             */
            $this->rolesModel->removeAllPermissions(
                $id
            );


            /*
             * Delete role.
             */
            $deleted =
                $this->rolesModel->delete($id);


            if (!$deleted) {
                throw new RuntimeException(
                    'Role could not be deleted.'
                );
            }


            $this->rolesModel->db->commit();


            Session::flash(
                'success',
                'Role deleted successfully.'
            );

        } catch (Throwable $e) {

            if (
                $this->rolesModel->db->inTransaction()
            ) {
                $this->rolesModel->db->rollBack();
            }


            error_log(
                'RolesController::delete - ' .
                $e->getMessage()
            );


            Session::flash(
                'error',
                'Unable to delete role.'
            );
        }


        $this->redirect('/roles');
    }
}