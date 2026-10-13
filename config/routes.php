<?php

$router->get(
    '/',
    'AuthController@login'
);

$router->get(
    '/login',
    'AuthController@login'
);

$router->post(
    '/login',
    'AuthController@authenticate'
);

$router->get(
    '/logout',
    'AuthController@logout'
);

$router->get(
    '/dashboard',
    'DashboardController@index'
);

/*
|--------------------------------------------------------------------------
| POS, Products, and Inventory
|--------------------------------------------------------------------------
*/

$router->get('/pos', 'POSController@index');
$router->post('/pos/checkout', 'POSController@checkout');
$router->get('/reports/sales', 'ReportsController@sales');

$router->get('/products', 'ProductsController@index');
$router->get('/products/create', 'ProductsController@create');
$router->post('/products/create', 'ProductsController@store');
$router->get('/products/edit/{id}', 'ProductsController@edit');
$router->post('/products/edit/{id}', 'ProductsController@update');

$router->get('/inventory', 'InventoryController@index');
$router->post('/inventory/adjust/{id}', 'InventoryController@adjust');

$router->get('/tables', 'TablesController@index');
$router->get('/tables/create', 'TablesController@create');
$router->post('/tables/create', 'TablesController@store');
$router->get('/tables/edit/{id}', 'TablesController@edit');
$router->post('/tables/edit/{id}', 'TablesController@update');
$router->post('/tables/delete/{id}', 'TablesController@delete');
$router->post('/tables/release/{id}', 'TablesController@release');


/*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
*/

$router->get(
    '/users',
    'UsersController@index'
);

$router->get(
    '/users/create',
    'UsersController@create'
);

$router->post(
    '/users/create',
    'UsersController@store'
);

$router->get(
    '/users/edit/{id}',
    'UsersController@edit'
);

$router->post(
    '/users/edit/{id}',
    'UsersController@update'
);

$router->get(
    '/users/delete/{id}',
    'UsersController@delete'
);

$router->get(
    '/users/toggle/{id}',
    'UsersController@toggleStatus'
);

/*
|--------------------------------------------------------------------------
| Roles
|--------------------------------------------------------------------------
*/

$router->get('/roles', 'RolesController@index');

$router->get('/roles/create', 'RolesController@create');
$router->post('/roles/create', 'RolesController@store');

$router->get('/roles/edit/{id}', 'RolesController@edit');
$router->post('/roles/edit/{id}', 'RolesController@update');

$router->get('/roles/delete/{id}', 'RolesController@delete');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

$router->get(
    '/dashboard',
    'DashboardController@index'
);

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/

$router->get(
    '/settings',
    'SettingsController@index'
);

$router->post(
    '/settings/save',
    'SettingsController@save'
);

/*
|--------------------------------------------------------------------------
| Departments
|--------------------------------------------------------------------------
*/

$router->get(
    '/departments',
    'DepartmentsController@index'
);

$router->get(
    '/departments/create',
    'DepartmentsController@create'
);

$router->post(
    '/departments/create',
    'DepartmentsController@store'
);

$router->get(
    '/departments/edit/{id}',
    'DepartmentsController@edit'
);

$router->post(
    '/departments/edit/{id}',
    'DepartmentsController@update'
);

$router->get(
    '/departments/toggle/{id}',
    'DepartmentsController@toggleStatus'
);

$router->get(
    '/departments/delete/{id}',
    'DepartmentsController@delete'
);

/*
|--------------------------------------------------------------------------
| Departments
|--------------------------------------------------------------------------
*/


$router->get(
    '/positions',
    'PositionsController@index'
);

$router->get(
    '/positions/create',
    'PositionsController@create'
);

$router->post(
    '/positions/create',
    'PositionsController@store'
);

$router->get(
    '/positions/edit/{id}',
    'PositionsController@edit'
);

$router->post(
    '/positions/edit/{id}',
    'PositionsController@update'
);

$router->get(
    '/positions/toggle/{id}',
    'PositionsController@toggleStatus'
);

$router->get(
    '/positions/delete/{id}',
    'PositionsController@delete'
);

/*
|--------------------------------------------------------------------------
| Employees
|--------------------------------------------------------------------------
*/


$router->get(
    '/employees',
    'EmployeesController@index'
);

$router->get(
    '/employees/create',
    'EmployeesController@create'
);

$router->post(
    '/employees/create',
    'EmployeesController@store'
);

$router->get(
    '/employees/edit/{id}',
    'EmployeesController@edit'
);

$router->post(
    '/employees/edit/{id}',
    'EmployeesController@update'
);

$router->get(
    '/employees/delete/{id}',
    'EmployeesController@delete'
);



/*
|--------------------------------------------------------------------------
| Employees Schedules
|--------------------------------------------------------------------------
*/


$router->get('/schedules', 'EmployeeSchedulesController@index');
$router->get('/schedules/create', 'EmployeeSchedulesController@create');
$router->post('/schedules/create', 'EmployeeSchedulesController@store');
$router->get('/schedules/edit/{id}', 'EmployeeSchedulesController@edit');
$router->post('/schedules/edit/{id}', 'EmployeeSchedulesController@update');
$router->get('/schedules/delete/{id}', 'EmployeeSchedulesController@delete');
$router->get(
    '/schedules/view/{id}',
    'EmployeeSchedulesController@show'
);

/*
|--------------------------------------------------------------------------
| Attendance
|--------------------------------------------------------------------------
*/


$router->get(
    '/attendance',
    'AttendanceController@index'
);

$router->get(
    '/attendance/create',
    'AttendanceController@create'
);

$router->post(
    '/attendance/create',
    'AttendanceController@store'
);

$router->post(
    '/attendance/clock-in',
    'AttendanceController@clockIn'
);

$router->post(
    '/attendance/clock-out',
    'AttendanceController@clockOut'
);

$router->get(
    '/attendance/view/{id}',
    'AttendanceController@employee'
);

$router->get(
    '/attendance/edit/{id}',
    'AttendanceController@edit'
);

$router->post(
    '/attendance/edit/{id}',
    'AttendanceController@update'
);

$router->get(
    '/attendance/delete/{id}',
    'AttendanceController@delete'
);

/*
|--------------------------------------------------------------------------
| Payroll
|--------------------------------------------------------------------------
*/

$router->get('/payroll', 'PayrollController@index');

$router->get('/payroll/create', 'PayrollController@create');
$router->post('/payroll/create', 'PayrollController@store');

$router->get('/payroll/view/{id}', 'PayrollController@show');

$router->get('/payroll/edit/{id}', 'PayrollController@edit');
$router->post('/payroll/edit/{id}', 'PayrollController@update');

$router->post('/payroll/calculate/{id}', 'PayrollController@calculate');

$router->post('/payroll/approve/{id}', 'PayrollController@approve');

$router->post('/payroll/pay/{id}', 'PayrollController@pay');

$router->get('/payroll/payslip/{id}', 'PayrollController@payslip');