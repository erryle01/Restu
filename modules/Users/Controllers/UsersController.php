<?php

require_once BASE_PATH .
    '/modules/Users/Models/UsersModel.php';


class UsersController extends Controller
{
    private UsersModel $usersModel;


    public function __construct()
    {
        $this->usersModel =
            new UsersModel();
    }


    /*
    |--------------------------------------------------------------------------
    | User List
    |--------------------------------------------------------------------------
    */

    public function index(): void
    {
        Auth::requirePermission(
            'users.view'
        );

        $users =
            $this->usersModel->getAll();

        $success =
            Session::getFlash('success');

        $error =
            Session::getFlash('error');

        $this->view(
            'modules/Users/Views/index',
            [
                'users'  => $users,
                'success' => $success,
                'error'   => $error
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Page
    |--------------------------------------------------------------------------
    */

    public function create(): void
    {
        Auth::requirePermission(
            'users.create'
        );

        $roles =
            $this->usersModel->getAllRoles();

        $error =
            Session::getFlash('error');

        $this->view(
            'modules/Users/Views/create',
            [
                'roles' => $roles,
                'error' => $error
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store User
    |--------------------------------------------------------------------------
    */

    public function store(): void
{
    Auth::requirePermission('users.create');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirect('/users/create');
    }

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $status = $_POST['status'] ?? 'active';

    $roleIds = $_POST['roles'] ?? [];

    if (!is_array($roleIds)) {
        $roleIds = [];
    }

    // -----------------------------
    // VALIDATION
    // -----------------------------

    if ($username === '') {
        Session::flash('error', 'Username is required.');
        $this->redirect('/users/create');
    }

    if ($fullName === '') {
        Session::flash('error', 'Full name is required.');
        $this->redirect('/users/create');
    }

    if ($password === '') {
        Session::flash('error', 'Password is required.');
        $this->redirect('/users/create');
    }

    if (strlen($password) < 8) {
        Session::flash(
            'error',
            'Password must be at least 8 characters.'
        );

        $this->redirect('/users/create');
    }

    if ($password !== $confirmPassword) {
        Session::flash(
            'error',
            'Passwords do not match.'
        );

        $this->redirect('/users/create');
    }

    if (!in_array(
        $status,
        ['active', 'inactive', 'locked'],
        true
    )) {
        Session::flash(
            'error',
            'Invalid account status.'
        );

        $this->redirect('/users/create');
    }

    // -----------------------------
    // CHECK DUPLICATE USERNAME
    // -----------------------------

    if ($this->usersModel->findByUsername($username)) {
        Session::flash(
            'error',
            'Username already exists.'
        );

        $this->redirect('/users/create');
    }

    // -----------------------------
    // CHECK DUPLICATE EMAIL
    // -----------------------------

    if ($email !== '') {

        if ($this->usersModel->findByEmail($email)) {
            Session::flash(
                'error',
                'Email already exists.'
            );

            $this->redirect('/users/create');
        }
    }

    // -----------------------------
    // CREATE USER
    // -----------------------------

    try {

        $this->usersModel->db->beginTransaction();

        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $userId = $this->usersModel->create([
            'username'  => $username,
            'email'     => $email !== '' ? $email : null,
            'password'  => $hashedPassword,
            'full_name' => $fullName,
            'status'    => $status
        ]);

        if (!$userId) {
            throw new RuntimeException(
                'Failed to create user.'
            );
        }

        // Assign roles
        $this->usersModel->setRoles(
            $userId,
            $roleIds
        );

        $this->usersModel->db->commit();

        Session::flash(
            'success',
            'User created successfully.'
        );

        $this->redirect('/users');

    } catch (Throwable $e) {

        if (
            $this->usersModel->db->inTransaction()
        ) {
            $this->usersModel->db->rollBack();
        }

        error_log(
            'Create user error: ' .
            $e->getMessage()
        );

        Session::flash(
            'error',
            'Unable to create user: ' .
            $e->getMessage()
        );

        $this->redirect('/users/create');
    }
}

    /*
    |--------------------------------------------------------------------------
    | Edit Page
    |--------------------------------------------------------------------------
    */

    public function edit(
        int $id
    ): void {

        Auth::requirePermission(
            'users.edit'
        );

        $user =
            $this->usersModel->find($id);

        if (!$user) {

            Session::flash(
                'error',
                'User not found.'
            );

            $this->redirect(
                '/users'
            );
        }


        $roles =
            $this->usersModel->getAllRoles();

        $userRoles =
            $this->usersModel->getRoles($id);


        $selectedRoles = array_map(
            fn($role) => (int)$role['id'],
            $userRoles
        );


        $error =
            Session::getFlash('error');


        $this->view(
            'modules/Users/Views/edit',
            [
                'user' => $user,
                'roles' => $roles,
                'selectedRoles' =>
                    $selectedRoles,
                'error' => $error
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update User
    |--------------------------------------------------------------------------
    */

    public function update(
        int $id
    ): void {

        Auth::requirePermission(
            'users.edit'
        );

        $user =
            $this->usersModel->find($id);

        if (!$user) {

            Session::flash(
                'error',
                'User not found.'
            );

            $this->redirect(
                '/users'
            );
        }


        $username =
            trim($_POST['username'] ?? '');

        $email =
            trim($_POST['email'] ?? '');

        $fullName =
            trim($_POST['full_name'] ?? '');

        $status =
            $_POST['status'] ?? 'active';

        $newPassword =
            $_POST['password'] ?? '';

        $confirmPassword =
            $_POST['confirm_password'] ?? '';

        $roleIds =
            $_POST['roles'] ?? [];


        /*
        |--------------------------------------------------------------------------
        | Basic validation
        |--------------------------------------------------------------------------
        */

        if (
            $username === '' ||
            $fullName === ''
        ) {

            Session::flash(
                'error',
                'Username and full name are required.'
            );

            $this->redirect(
                '/users/edit/' . $id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Username duplicate check
        |--------------------------------------------------------------------------
        */

        $existingUser =
            $this->usersModel
                ->findByUsername($username);

        if (
            $existingUser &&
            (int)$existingUser['id'] !== $id
        ) {

            Session::flash(
                'error',
                'Username already exists.'
            );

            $this->redirect(
                '/users/edit/' . $id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Email
        |--------------------------------------------------------------------------
        */

        if ($email !== '') {

            if (
                !filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                Session::flash(
                    'error',
                    'Invalid email address.'
                );

                $this->redirect(
                    '/users/edit/' . $id
                );
            }


            $existingEmail =
                $this->usersModel
                    ->findByEmail($email);

            if (
                $existingEmail &&
                (int)$existingEmail['id'] !== $id
            ) {

                Session::flash(
                    'error',
                    'Email already exists.'
                );

                $this->redirect(
                    '/users/edit/' . $id
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Validate status
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $status,
                [
                    'active',
                    'inactive',
                    'locked'
                ],
                true
            )
        ) {

            $status = 'active';
        }


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        try {

            $this->usersModel
                ->db
                ->beginTransaction();


            $this->usersModel->update(
                $id,
                [
                    'username'  => $username,
                    'email'     => $email ?: null,
                    'full_name' => $fullName,
                    'status'    => $status
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | Password
            |--------------------------------------------------------------------------
            */

            if ($newPassword !== '') {

                if (
                    $newPassword !==
                    $confirmPassword
                ) {

                    throw new Exception(
                        'Passwords do not match.'
                    );
                }


                if (
                    strlen($newPassword) < 8
                ) {

                    throw new Exception(
                        'Password must be at least 8 characters.'
                    );
                }


                $hashedPassword =
                    password_hash(
                        $newPassword,
                        PASSWORD_DEFAULT
                    );


                $this->usersModel
                    ->updatePassword(
                        $id,
                        $hashedPassword
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Roles
            |--------------------------------------------------------------------------
            */

            $this->usersModel->setRoles(
                $id,
                $roleIds
            );


            $this->usersModel
                ->db
                ->commit();


            Session::flash(
                'success',
                'User updated successfully.'
            );

            $this->redirect(
                '/users'
            );

        } catch (Throwable $e) {

            if (
                $this->usersModel
                    ->db
                    ->inTransaction()
            ) {

                $this->usersModel
                    ->db
                    ->rollBack();
            }

            Session::flash(
                'error',
                $e->getMessage()
            );

            $this->redirect(
                '/users/edit/' . $id
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(
        int $id
    ): void {

        Auth::requirePermission(
            'users.edit'
        );

        if (
            Auth::id() === $id
        ) {

            Session::flash(
                'error',
                'You cannot change your own account status.'
            );

            $this->redirect(
                '/users'
            );
        }


        $user =
            $this->usersModel->find($id);

        if (!$user) {

            Session::flash(
                'error',
                'User not found.'
            );

            $this->redirect(
                '/users'
            );
        }


        $this->usersModel
            ->toggleStatus($id);


        Session::flash(
            'success',
            'User status updated.'
        );


        $this->redirect(
            '/users'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete User
    |--------------------------------------------------------------------------
    */

    public function delete(
        int $id
    ): void {

        Auth::requirePermission(
            'users.delete'
        );


        if (
            Auth::id() === $id
        ) {

            Session::flash(
                'error',
                'You cannot delete your own account.'
            );

            $this->redirect(
                '/users'
            );
        }


        $user =
            $this->usersModel->find($id);

        if (!$user) {

            Session::flash(
                'error',
                'User not found.'
            );

            $this->redirect(
                '/users'
            );
        }


        $this->usersModel
            ->delete($id);


        Session::flash(
            'success',
            'User deleted successfully.'
        );


        $this->redirect(
            '/users'
        );
    }
}