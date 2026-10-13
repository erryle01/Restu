<?php

require_once BASE_PATH .
    '/modules/EmployeeSchedules/Models/EmployeeSchedulesModel.php';

class EmployeeSchedulesController extends Controller
{
    private EmployeeSchedulesModel $schedulesModel;


    public function __construct()
    {
        $this->schedulesModel =
            new EmployeeSchedulesModel();
    }


    /**
     * List schedules.
     */
    public function index(): void
{
    Auth::requirePermission('schedules.view');

    $model = new EmployeeSchedulesModel();

    $search = trim($_GET['search'] ?? '');

    $departmentId = isset($_GET['department_id'])
        ? (int) $_GET['department_id']
        : 0;

    $status = trim($_GET['status'] ?? '');

    $page = isset($_GET['page'])
        ? max(1, (int) $_GET['page'])
        : 1;

    $perPage = 20;

    $totalEmployees = $model->countEmployeesWithScheduleFilters(
        $search !== '' ? $search : null,
        $departmentId > 0 ? $departmentId : null,
        $status !== '' ? $status : null
    );

    $totalPages = max(
        1,
        (int) ceil($totalEmployees / $perPage)
    );

    if ($page > $totalPages) {
        $page = $totalPages;
    }

    $employees = $model->getEmployeesWithScheduleSummary(
        $search !== '' ? $search : null,
        $departmentId > 0 ? $departmentId : null,
        $status !== '' ? $status : null,
        $page,
        $perPage
    );

    $departments = $model->getDepartments();

    $success = Session::getFlash('success');
    $error = Session::getFlash('error');

    $this->view(
        'modules/EmployeeSchedules/Views/index',
        [
            'employees'   => $employees,
            'departments' => $departments,
            'search'      => $search,
            'departmentId'=> $departmentId,
            'status'      => $status,
            'page'        => $page,
            'perPage'     => $perPage,
            'totalPages'  => $totalPages,
            'totalEmployees' => $totalEmployees,
            'success'     => $success,
            'error'       => $error
        ]
    );
}

public function show(int $employeeId): void
{
    Auth::requirePermission('schedules.view');

    $model = new EmployeeSchedulesModel();

    $employee = $model->getEmployee($employeeId);

    if (!$employee) {
        Session::flash('error', 'Employee not found.');
        $this->redirect('/schedules');
    }

    $schedules = $model->getEmployeeWeeklySchedule($employeeId);

    $success = Session::getFlash('success');
    $error = Session::getFlash('error');

    $this->view(
        'modules/EmployeeSchedules/Views/view',
        [
            'employee'  => $employee,
            'schedules' => $schedules,
            'success'   => $success,
            'error'     => $error
        ]
    );
}
    /**
     * Create weekly schedule.
     */
    public function create(): void
    {
        Auth::requirePermission(
            'schedules.create'
        );

        $employees =
            $this->schedulesModel->getEmployees();

        $error =
            Session::getFlash('error');

        $this->view(
            'modules/EmployeeSchedules/Views/create',
            [
                'employees' => $employees,
                'error'     => $error
            ]
        );
    }


    /**
     * Store weekly schedule.
     */
    public function store(): void
    {
        Auth::requirePermission(
            'schedules.create'
        );

        if (
            $_SERVER['REQUEST_METHOD']
            !== 'POST'
        ) {
            $this->redirect(
                '/schedules/create'
            );
        }


        $employeeId =
            (int) ($_POST['employee_id'] ?? 0);

        $employee =
            $this->schedulesModel
                ->getEmployee($employeeId);


        if (!$employee) {

            Session::flash(
                'error',
                'Employee not found.'
            );

            $this->redirect(
                '/schedules/create'
            );
        }


        /*
         * Weekly schedule fields.
         */
        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        ];


        try {

            $this->schedulesModel->db
                ->beginTransaction();


            foreach ($days as $day => $dayName) {

                $isRestDay =
                    isset(
                        $_POST['rest_day'][$day]
                    )
                        ? 1
                        : 0;


                $timeIn =
                    trim(
                        $_POST['time_in'][$day]
                        ?? ''
                    );

                $timeOut =
                    trim(
                        $_POST['time_out'][$day]
                        ?? ''
                    );

                $breakMinutes =
                    (int) (
                        $_POST['break_minutes'][$day]
                        ?? 60
                    );


                /*
                 * Validate break minutes.
                 */
                if (
                    $breakMinutes < 0
                    ||
                    $breakMinutes > 1440
                ) {

                    throw new RuntimeException(
                        $dayName .
                        ': Invalid break duration.'
                    );
                }


                /*
                 * Rest day.
                 */
                if ($isRestDay === 1) {

                    $timeIn = '00:00:00';
                    $timeOut = '00:00:00';
                    $breakMinutes = 0;

                } else {

                    /*
                     * Working day must have
                     * time in and time out.
                     */
                    if (
                        $timeIn === ''
                        ||
                        $timeOut === ''
                    ) {

                        throw new RuntimeException(
                            $dayName .
                            ': Time in and time out are required.'
                        );
                    }


                    /*
                     * Normalize HH:MM.
                     */
                    if (
                        strlen($timeIn) === 5
                    ) {
                        $timeIn .= ':00';
                    }

                    if (
                        strlen($timeOut) === 5
                    ) {
                        $timeOut .= ':00';
                    }


                    /*
                     * Validate time format.
                     */
                    $timeInObj =
                        DateTime::createFromFormat(
                            'H:i:s',
                            $timeIn
                        );

                    $timeOutObj =
                        DateTime::createFromFormat(
                            'H:i:s',
                            $timeOut
                        );


                    if (
                        !$timeInObj
                        ||
                        !$timeOutObj
                    ) {

                        throw new RuntimeException(
                            $dayName .
                            ': Invalid time format.'
                        );
                    }

                }


                /*
                 * Check duplicate schedule.
                 */
                $existing =
                    $this->schedulesModel
                        ->findByEmployeeDay(
                            $employeeId,
                            $day
                        );


                if ($existing) {

                    throw new RuntimeException(
                        $dayName .
                        ': A schedule already exists for this employee.'
                    );
                }


                /*
                 * Create schedule.
                 */
                $this->schedulesModel->create([
                    'employee_id'   => $employeeId,
                    'day_of_week'   => $day,
                    'time_in'       => $timeIn,
                    'time_out'      => $timeOut,
                    'break_minutes' => $breakMinutes,
                    'is_rest_day'   => $isRestDay
                ]);
            }


            $this->schedulesModel->db
                ->commit();


            Session::flash(
                'success',
                'Weekly schedule created successfully.'
            );

            $this->redirect(
                '/schedules'
            );

        } catch (Throwable $e) {

            if (
                $this->schedulesModel->db
                    ->inTransaction()
            ) {

                $this->schedulesModel->db
                    ->rollBack();
            }


            error_log(
                'EmployeeSchedulesController::store - '
                . $e->getMessage()
            );


            Session::flash(
                'error',
                $e->getMessage()
            );

            $this->redirect(
                '/schedules/create'
            );
        }
    }


    /**
     * Edit schedule.
     */
    public function edit(int $id): void
    {
        Auth::requirePermission(
            'schedules.edit'
        );

        $schedule =
            $this->schedulesModel->find($id);


        if (!$schedule) {

            Session::flash(
                'error',
                'Schedule not found.'
            );

            $this->redirect(
                '/schedules'
            );
        }


        $employees =
            $this->schedulesModel->getEmployees();

        $error =
            Session::getFlash('error');


        $this->view(
            'modules/EmployeeSchedules/Views/edit',
            [
                'schedule'  => $schedule,
                'employees' => $employees,
                'error'     => $error
            ]
        );
    }


    /**
     * Update schedule.
     */
    public function update(int $id): void
    {
        Auth::requirePermission(
            'schedules.edit'
        );


        if (
            $_SERVER['REQUEST_METHOD']
            !== 'POST'
        ) {

            $this->redirect(
                '/schedules/edit/' . $id
            );
        }


        $schedule =
            $this->schedulesModel->find($id);


        if (!$schedule) {

            Session::flash(
                'error',
                'Schedule not found.'
            );

            $this->redirect(
                '/schedules'
            );
        }


        $employeeId =
            (int) (
                $_POST['employee_id']
                ?? 0
            );

        $dayOfWeek =
            (int) (
                $_POST['day_of_week']
                ?? 0
            );

        $timeIn =
            trim(
                $_POST['time_in']
                ?? ''
            );

        $timeOut =
            trim(
                $_POST['time_out']
                ?? ''
            );

        $breakMinutes =
            (int) (
                $_POST['break_minutes']
                ?? 60
            );

        $isRestDay =
            isset($_POST['is_rest_day'])
                ? 1
                : 0;


        /*
         * Validation.
         */
        $employee =
            $this->schedulesModel
                ->getEmployee($employeeId);


        if (!$employee) {

            Session::flash(
                'error',
                'Employee not found.'
            );

            $this->redirect(
                '/schedules/edit/' . $id
            );
        }


        if (
            $dayOfWeek < 1
            ||
            $dayOfWeek > 7
        ) {

            Session::flash(
                'error',
                'Invalid day of week.'
            );

            $this->redirect(
                '/schedules/edit/' . $id
            );
        }


        if (
            $breakMinutes < 0
            ||
            $breakMinutes > 1440
        ) {

            Session::flash(
                'error',
                'Break duration is invalid.'
            );

            $this->redirect(
                '/schedules/edit/' . $id
            );
        }


        if ($isRestDay) {

            $timeIn = '00:00:00';
            $timeOut = '00:00:00';
            $breakMinutes = 0;

        } else {

            if (
                $timeIn === ''
                ||
                $timeOut === ''
            ) {

                Session::flash(
                    'error',
                    'Time in and time out are required.'
                );

                $this->redirect(
                    '/schedules/edit/' . $id
                );
            }


            if (
                strlen($timeIn) === 5
            ) {
                $timeIn .= ':00';
            }

            if (
                strlen($timeOut) === 5
            ) {
                $timeOut .= ':00';
            }
        }


        /*
         * Prevent duplicate employee/day.
         */
        $existing =
            $this->schedulesModel
                ->findByEmployeeDay(
                    $employeeId,
                    $dayOfWeek,
                    $id
                );


        if ($existing) {

            Session::flash(
                'error',
                'A schedule already exists for this employee on the selected day.'
            );

            $this->redirect(
                '/schedules/edit/' . $id
            );
        }


        try {

            $updated =
                $this->schedulesModel->update(
                    $id,
                    [
                        'employee_id'   => $employeeId,
                        'day_of_week'   => $dayOfWeek,
                        'time_in'       => $timeIn,
                        'time_out'      => $timeOut,
                        'break_minutes' => $breakMinutes,
                        'is_rest_day'   => $isRestDay
                    ]
                );


            if (!$updated) {

                throw new RuntimeException(
                    'Schedule could not be updated.'
                );
            }


            Session::flash(
                'success',
                'Schedule updated successfully.'
            );

        } catch (Throwable $e) {

            error_log(
                'EmployeeSchedulesController::update - '
                . $e->getMessage()
            );


            Session::flash(
                'error',
                'Unable to update schedule.'
            );
        }


        $this->redirect(
            '/schedules'
        );
    }


    /**
     * Delete schedule.
     */
    public function delete(int $id): void
    {
        Auth::requirePermission(
            'schedules.delete'
        );


        $schedule =
            $this->schedulesModel->find($id);


        if (!$schedule) {

            Session::flash(
                'error',
                'Schedule not found.'
            );

            $this->redirect(
                '/schedules'
            );
        }


        try {

            $deleted =
                $this->schedulesModel
                    ->delete($id);


            if (!$deleted) {

                throw new RuntimeException(
                    'Schedule could not be deleted.'
                );
            }


            Session::flash(
                'success',
                'Schedule deleted successfully.'
            );

        } catch (Throwable $e) {

            error_log(
                'EmployeeSchedulesController::delete - '
                . $e->getMessage()
            );


            Session::flash(
                'error',
                'Unable to delete schedule.'
            );
        }


        $this->redirect(
            '/schedules'
        );
    }
}