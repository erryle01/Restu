<?php
require_once BASE_PATH .
    '/modules/Auth/Models/AuthModel.php';
class AuthController extends Controller
{
    private AuthModel $authModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
    }

    public function login(): void
    {
        if (Auth::check()) {

            $this->redirect('/dashboard');
        }

        $error = Session::getFlash('error');

        $this->view(
            'modules/Auth/Views/login',
            [
                'error' => $error
            ],
            false
        );
    }

    public function authenticate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            $this->redirect('/login');
        }

        $username = trim(
            $_POST['username'] ?? ''
        );

        $password = $_POST['password'] ?? '';

        if (
            $username === '' ||
            $password === ''
        ) {

            Session::flash(
                'error',
                'Username and password are required.'
            );

            $this->redirect('/login');
        }

        $user =
            $this->authModel->findUser(
                $username
            );

        if (!$user) {

            $this->authModel->logAttempt(
                $username,
                false
            );

            Session::flash(
                'error',
                'Invalid username or password.'
            );

            $this->redirect('/login');
        }

        if ($user['status'] !== 'active') {

            $this->authModel->logAttempt(
                $username,
                false
            );

            Session::flash(
                'error',
                'This account is not active.'
            );

            $this->redirect('/login');
        }

        if (!password_verify(
            $password,
            $user['password']
        )) {

            $this->authModel->logAttempt(
                $username,
                false
            );

            Session::flash(
                'error',
                'Invalid username or password.'
            );

            $this->redirect('/login');
        }

        session_regenerate_id(true);

        $roles =
            $this->authModel->getRoles(
                (int)$user['id']
            );

        $permissions =
            $this->authModel->getPermissions(
                (int)$user['id']
            );

        unset($user['password']);

        Session::set(
            'logged_in',
            true
        );

        Session::set(
            'auth_user_id',
            (int)$user['id']
        );

        Session::set(
            'auth_user',
            $user
        );

        Session::set(
            'auth_roles',
            $roles
        );

        Session::set(
            'auth_permissions',
            $permissions
        );

        $this->authModel->updateLastLogin(
            (int)$user['id']
        );

        $this->authModel->logAttempt(
            $username,
            true
        );

        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();

        header(
            'Location: ' .
            BASE_URL .
            '/login'
        );

        exit;
    }
}