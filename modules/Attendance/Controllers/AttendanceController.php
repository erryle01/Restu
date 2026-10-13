<?php

require_once BASE_PATH .
    '/modules/Attendance/Models/AttendanceModel.php';

require_once BASE_PATH .
    '/modules/Payroll/Models/PayrollModel.php';

require_once BASE_PATH .
    '/modules/Payroll/Services/PayrollEngine.php';

require_once BASE_PATH .
    '/modules/Payroll/Services/SalaryCalculator.php';

require_once BASE_PATH .
    '/modules/Payroll/Services/OvertimeCalculator.php';

require_once BASE_PATH .
    '/modules/Payroll/Services/DeductionCalculator.php';

class AttendanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Attendance List
    |--------------------------------------------------------------------------
    */

    public function index(): void
    {
        Auth::requirePermission('attendance.view');

        $model = new AttendanceModel();

        $dateFrom = trim(
            $_GET['date_from'] ?? ''
        );

        $dateTo = trim(
            $_GET['date_to'] ?? ''
        );

        $employeeId = isset($_GET['employee_id'])
            ? (int) $_GET['employee_id']
            : 0;

        $status = trim(
            $_GET['status'] ?? ''
        );

        /*
        |--------------------------------------------------------------------------
        | Default Dates
        |--------------------------------------------------------------------------
        */

        if ($dateFrom === '') {
            $dateFrom = date('Y-m-01');
        }

        if ($dateTo === '') {
            $dateTo = date('Y-m-d');
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Date Filters
        |--------------------------------------------------------------------------
        */

        if (!$this->validDate($dateFrom)) {
            $dateFrom = date('Y-m-01');
        }

        if (!$this->validDate($dateTo)) {
            $dateTo = date('Y-m-d');
        }

        /*
        |--------------------------------------------------------------------------
        | Correct Reversed Date Range
        |--------------------------------------------------------------------------
        */

        if ($dateFrom > $dateTo) {
            $temp = $dateFrom;
            $dateFrom = $dateTo;
            $dateTo = $temp;
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Status Filter
        |--------------------------------------------------------------------------
        */

        $allowedStatuses = [
            'present',
            'late',
            'absent',
            'half_day',
            'leave',
            'rest_day',
            'holiday'
        ];

        if (
            $status !== '' &&
            !in_array($status, $allowedStatuses, true)
        ) {
            $status = '';
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $page = isset($_GET['page'])
            ? max(1, (int) $_GET['page'])
            : 1;

        $perPage = 20;

        $filterEmployeeId = $employeeId > 0
            ? $employeeId
            : null;

        $filterStatus = $status !== ''
            ? $status
            : null;

        /*
        |--------------------------------------------------------------------------
        | Count
        |--------------------------------------------------------------------------
        */

        $total = $model->countAll(
            $dateFrom,
            $dateTo,
            $filterEmployeeId,
            $filterStatus
        );

        $totalPages = max(
            1,
            (int) ceil($total / $perPage)
        );

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        /*
        |--------------------------------------------------------------------------
        | Get Attendance
        |--------------------------------------------------------------------------
        */

        $attendance = $model->getAll(
            $dateFrom,
            $dateTo,
            $filterEmployeeId,
            $filterStatus,
            $page,
            $perPage
        );

        /*
        |--------------------------------------------------------------------------
        | Employees
        |--------------------------------------------------------------------------
        */

        $employees = $model->getEmployees();

        /*
        |--------------------------------------------------------------------------
        | Flash Messages
        |--------------------------------------------------------------------------
        */

        $success = Session::getFlash(
            'success'
        );

        $error = Session::getFlash(
            'error'
        );

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        $this->view(
            'modules/Attendance/Views/index',
            [
                'attendance' => $attendance,
                'employees' => $employees,

                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,

                'employeeId' => $employeeId,
                'status' => $status,

                'page' => $page,
                'perPage' => $perPage,

                'total' => $total,
                'totalPages' => $totalPages,

                'success' => $success,
                'error' => $error
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Attendance
    |--------------------------------------------------------------------------
    */

    public function create(): void
    {
        Auth::requirePermission(
            'attendance.create'
        );

        $model = new AttendanceModel();

        $employees =
            $model->getEmployees();

        $error =
            Session::getFlash('error');

        $this->view(
            'modules/Attendance/Views/create',
            [
                'employees' => $employees,
                'error' => $error
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Attendance
    |--------------------------------------------------------------------------
    */

    public function store(): void
    {
        Auth::requirePermission(
            'attendance.create'
        );

        $model = new AttendanceModel();

        /*
        |--------------------------------------------------------------------------
        | POST Data
        |--------------------------------------------------------------------------
        */

        $employeeId = (int) (
            $_POST['employee_id'] ?? 0
        );

        $attendanceDate = trim(
            $_POST['attendance_date'] ?? ''
        );

        $timeIn = trim(
            $_POST['time_in'] ?? ''
        );

        $timeOut = trim(
            $_POST['time_out'] ?? ''
        );

        $status = trim(
            $_POST['status'] ?? 'present'
        );

        $notes = trim(
            $_POST['notes'] ?? ''
        );

        /*
        |--------------------------------------------------------------------------
        | Employee Validation
        |--------------------------------------------------------------------------
        */

        if ($employeeId <= 0) {

            Session::flash(
                'error',
                'Please select an employee.'
            );

            $this->redirect(
                '/attendance/create'
            );
        }

        $employee =
            $model->getEmployee(
                $employeeId
            );

        if (!$employee) {

            Session::flash(
                'error',
                'Selected employee does not exist.'
            );

            $this->redirect(
                '/attendance/create'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date Validation
        |--------------------------------------------------------------------------
        */

        if (
            !$this->validDate(
                $attendanceDate
            )
        ) {

            Session::flash(
                'error',
                'Please provide a valid attendance date.'
            );

            $this->redirect(
                '/attendance/create'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status Validation
        |--------------------------------------------------------------------------
        */

        $allowedStatuses = [
            'present',
            'late',
            'absent',
            'half_day',
            'leave',
            'rest_day',
            'holiday'
        ];

        if (
            !in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {

            Session::flash(
                'error',
                'Invalid attendance status.'
            );

            $this->redirect(
                '/attendance/create'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Time Validation
        |--------------------------------------------------------------------------
        */

        if (
            $timeIn !== '' &&
            !$this->validTime($timeIn)
        ) {

            Session::flash(
                'error',
                'Invalid Time In value.'
            );

            $this->redirect(
                '/attendance/create'
            );
        }

        if (
            $timeOut !== '' &&
            !$this->validTime($timeOut)
        ) {

            Session::flash(
                'error',
                'Invalid Time Out value.'
            );

            $this->redirect(
                '/attendance/create'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Time Order Validation
        |--------------------------------------------------------------------------
        |
        | Same-day attendance only.
        |
        | Example:
        | 08:00 -> 17:00 = valid
        | 23:08 -> 07:08 = rejected
        |
        */

        if (
            $timeIn !== '' &&
            $timeOut !== '' &&
            $timeOut <= $timeIn
        ) {

            Session::flash(
                'error',
                'Time Out must be later than Time In. Example: 08:00 AM to 05:00 PM.'
            );

            $this->redirect(
                '/attendance/create'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Duplicate Attendance
        |--------------------------------------------------------------------------
        */

        if (
            $model->findByEmployeeDate(
                $employeeId,
                $attendanceDate
            )
        ) {

            Session::flash(
                'error',
                'Attendance for this employee and date already exists.'
            );

            $this->redirect(
                '/attendance/create'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Get Employee Schedule
        |--------------------------------------------------------------------------
        */

        $schedule =
            $model->getScheduleForEmployeeDate(
                $employeeId,
                $attendanceDate
            );

        $scheduledTimeIn = null;
        $scheduledTimeOut = null;
        $breakMinutes = 0;

        if ($schedule) {

            $scheduledTimeIn =
                $schedule['time_in'];

            $scheduledTimeOut =
                $schedule['time_out'];

            $breakMinutes =
                (int) $schedule['break_minutes'];

            /*
            |--------------------------------------------------------------------------
            | Rest Day
            |--------------------------------------------------------------------------
            */

            if (
                (int) $schedule['is_rest_day'] === 1
            ) {

                $status = 'rest_day';

                $timeIn = '';
                $timeOut = '';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate Attendance
        |--------------------------------------------------------------------------
        */

        try {

            $calculation =
                $this->calculateAttendance(
                    $attendanceDate,
                    $timeIn,
                    $timeOut,
                    $scheduledTimeIn,
                    $scheduledTimeOut,
                    $breakMinutes,
                    $status
                );

        } catch (RuntimeException $exception) {

            Session::flash(
                'error',
                $exception->getMessage()
            );

            $this->redirect(
                '/attendance/create'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Create Record
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | The attendance table stores:
        |
        | total_minutes
        | overtime_minutes
        | late_minutes
        | undertime_minutes
        | remarks
        |
        | It does NOT store:
        |
        | regular_hours
        | overtime_hours
        | notes
        |
        */

        $model->create(
            [
                'employee_id' =>
                    $employeeId,

                'attendance_date' =>
                    $attendanceDate,

                'scheduled_time_in' =>
                    $scheduledTimeIn,

                'scheduled_time_out' =>
                    $scheduledTimeOut,

                'time_in' =>
                    $calculation['time_in'],

                'time_out' =>
                    $calculation['time_out'],

                'break_minutes' =>
                    $breakMinutes,

                'late_minutes' =>
                    $calculation['late_minutes'],

                'undertime_minutes' =>
                    $calculation['undertime_minutes'],

                'overtime_minutes' =>
                    $calculation['overtime_minutes'],

                'total_minutes' =>
                    $calculation['total_minutes'],

                'status' =>
                    $calculation['status'],

                'remarks' =>
                    $notes !== ''
                        ? $notes
                        : null
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        Session::flash(
            'success',
            'Attendance record created successfully.'
        );

        $this->redirect(
            '/attendance'
        );
    }

    public function clockIn(): void
    {
        Auth::requireLogin();

        $model = new AttendanceModel();
        $employee = $model->getEmployeeForUser((int) Auth::id());

        if (!$employee) {
            $this->clockError('Your account is not linked to an employee record.');
        }

        $date = date('Y-m-d');
        $existing = $model->findByEmployeeDate((int) $employee['id'], $date);

        if ($existing) {
            $this->clockError('You already have an attendance record for today.');
        }

        $schedule = $model->getScheduleForEmployeeDate((int) $employee['id'], $date);

        if ($schedule && (int) $schedule['is_rest_day'] === 1) {
            $this->clockError('Today is marked as a rest day.');
        }

        $scheduledIn = $schedule['time_in'] ?? null;
        $scheduledOut = $schedule['time_out'] ?? null;
        $breakMinutes = (int) ($schedule['break_minutes'] ?? 0);
        $calculation = $this->calculateAttendance(
            $date,
            date('H:i'),
            '',
            $scheduledIn,
            $scheduledOut,
            $breakMinutes,
            'present'
        );

        $model->create([
            'employee_id' => (int) $employee['id'],
            'attendance_date' => $date,
            'scheduled_time_in' => $scheduledIn,
            'scheduled_time_out' => $scheduledOut,
            'time_in' => $calculation['time_in'],
            'time_out' => null,
            'break_minutes' => $breakMinutes,
            'late_minutes' => $calculation['late_minutes'],
            'undertime_minutes' => 0,
            'overtime_minutes' => 0,
            'total_minutes' => 0,
            'status' => 'present',
            'remarks' => 'Self clock-in'
        ]);

        $this->clockSuccess('Clocked in successfully.');
    }

    public function clockOut(): void
    {
        Auth::requireLogin();

        $model = new AttendanceModel();
        $employee = $model->getEmployeeForUser((int) Auth::id());

        if (!$employee) {
            $this->clockError('Your account is not linked to an employee record.');
        }

        $date = date('Y-m-d');
        $existing = $model->findByEmployeeDate((int) $employee['id'], $date);

        if (!$existing || empty($existing['time_in'])) {
            $this->clockError('You must clock in before clocking out.');
        }

        if (!empty($existing['time_out'])) {
            $this->clockError('You have already clocked out today.');
        }

        $timeIn = date('H:i', strtotime($existing['time_in']));
        $calculation = $this->calculateAttendance(
            $date,
            $timeIn,
            date('H:i'),
            $existing['scheduled_time_in'],
            $existing['scheduled_time_out'],
            (int) $existing['break_minutes'],
            'present'
        );

        $model->update($existing['id'], [
            'employee_id' => (int) $employee['id'],
            'attendance_date' => $date,
            'scheduled_time_in' => $existing['scheduled_time_in'],
            'scheduled_time_out' => $existing['scheduled_time_out'],
            'time_in' => $calculation['time_in'],
            'time_out' => $calculation['time_out'],
            'break_minutes' => (int) $existing['break_minutes'],
            'late_minutes' => $calculation['late_minutes'],
            'undertime_minutes' => $calculation['undertime_minutes'],
            'overtime_minutes' => $calculation['overtime_minutes'],
            'total_minutes' => $calculation['total_minutes'],
            'status' => $calculation['status'],
            'remarks' => 'Self clock-in/out'
        ]);

        $this->clockSuccess('Clocked out successfully.');
    }

    private function clockSuccess(string $message): never
    {
        Session::flash('success', $message);

        if (ApiResponse::isRequest()) {
            $this->apiSuccess([
                'message' => $message,
                'redirect' => BASE_URL . '/dashboard'
            ]);
        }

        $this->redirect('/dashboard');
    }

    private function clockError(string $message): never
    {
        if (ApiResponse::isRequest()) {
            $this->apiError(422, 'CLOCK_ACTION_FAILED', $message);
        }

        Session::flash('error', $message);
        $this->redirect('/dashboard');
    }


    /*
    |--------------------------------------------------------------------------
    | Edit Attendance
    |--------------------------------------------------------------------------
    */

    public function edit(int $id): void
    {
        $this->requireAttendanceEditPermission();

        $model = new AttendanceModel();

        $attendance =
            $model->find($id);

        if (!$attendance) {

            Session::flash(
                'error',
                'Attendance record not found.'
            );

            $this->redirect(
                '/attendance'
            );
        }

        $employees =
            $model->getEmployees();

        $error =
            Session::getFlash('error');

        $this->view(
            'modules/Attendance/Views/edit',
            [
                'attendance' =>
                    $attendance,

                'employees' =>
                    $employees,

                'error' =>
                    $error
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Attendance
    |--------------------------------------------------------------------------
    */

    public function update(int $id): void
    {
        $this->requireAttendanceEditPermission();

        $model = new AttendanceModel();

        /*
        |--------------------------------------------------------------------------
        | Existing Record
        |--------------------------------------------------------------------------
        */

        $existing =
            $model->find($id);

        if (!$existing) {

            Session::flash(
                'error',
                'Attendance record not found.'
            );

            $this->redirect(
                '/attendance'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | POST Data
        |--------------------------------------------------------------------------
        */

        $employeeId = (int) (
            $_POST['employee_id'] ?? 0
        );

        $attendanceDate = trim(
            $_POST['attendance_date'] ?? ''
        );

        $timeIn = trim(
            $_POST['time_in'] ?? ''
        );

        $timeOut = trim(
            $_POST['time_out'] ?? ''
        );

        $status = trim(
            $_POST['status'] ?? 'present'
        );

        $notes = trim(
            $_POST['notes'] ?? ''
        );

        /*
        |--------------------------------------------------------------------------
        | Employee Validation
        |--------------------------------------------------------------------------
        */

        if ($employeeId <= 0) {

            Session::flash(
                'error',
                'Please select an employee.'
            );

            $this->redirect(
                '/attendance/edit/' . $id
            );
        }

        $employee =
            $model->getEmployee(
                $employeeId
            );

        if (!$employee) {

            Session::flash(
                'error',
                'Selected employee does not exist.'
            );

            $this->redirect(
                '/attendance/edit/' . $id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date Validation
        |--------------------------------------------------------------------------
        */

        if (
            !$this->validDate(
                $attendanceDate
            )
        ) {

            Session::flash(
                'error',
                'Please provide a valid attendance date.'
            );

            $this->redirect(
                '/attendance/edit/' . $id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status Validation
        |--------------------------------------------------------------------------
        */

        $allowedStatuses = [
            'present',
            'late',
            'absent',
            'half_day',
            'leave',
            'rest_day',
            'holiday'
        ];

        if (
            !in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {

            Session::flash(
                'error',
                'Invalid attendance status.'
            );

            $this->redirect(
                '/attendance/edit/' . $id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Time Validation
        |--------------------------------------------------------------------------
        */

        if (
            $timeIn !== '' &&
            !$this->validTime($timeIn)
        ) {

            Session::flash(
                'error',
                'Invalid Time In value.'
            );

            $this->redirect(
                '/attendance/edit/' . $id
            );
        }

        if (
            $timeOut !== '' &&
            !$this->validTime($timeOut)
        ) {

            Session::flash(
                'error',
                'Invalid Time Out value.'
            );

            $this->redirect(
                '/attendance/edit/' . $id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Time Order Validation
        |--------------------------------------------------------------------------
        */

        if (
            $timeIn !== '' &&
            $timeOut !== '' &&
            $timeOut <= $timeIn
        ) {

            Session::flash(
                'error',
                'Time Out must be later than Time In. Example: 08:00 AM to 05:00 PM.'
            );

            $this->redirect(
                '/attendance/edit/' . $id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Duplicate Check
        |--------------------------------------------------------------------------
        */

        if (
            $model->findByEmployeeDate(
                $employeeId,
                $attendanceDate,
                $id
            )
        ) {

            Session::flash(
                'error',
                'Attendance for this employee and date already exists.'
            );

            $this->redirect(
                '/attendance/edit/' . $id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Get Employee Schedule
        |--------------------------------------------------------------------------
        */

        $schedule =
            $model->getScheduleForEmployeeDate(
                $employeeId,
                $attendanceDate
            );

        $scheduledTimeIn = null;
        $scheduledTimeOut = null;
        $breakMinutes = 0;

        if ($schedule) {

            $scheduledTimeIn =
                $schedule['time_in'];

            $scheduledTimeOut =
                $schedule['time_out'];

            $breakMinutes =
                (int) $schedule['break_minutes'];

            /*
            |--------------------------------------------------------------------------
            | Rest Day
            |--------------------------------------------------------------------------
            */

            if (
                (int) $schedule['is_rest_day'] === 1
            ) {

                $status = 'rest_day';

                $timeIn = '';
                $timeOut = '';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate Attendance
        |--------------------------------------------------------------------------
        */

        try {

            $calculation =
                $this->calculateAttendance(
                    $attendanceDate,
                    $timeIn,
                    $timeOut,
                    $scheduledTimeIn,
                    $scheduledTimeOut,
                    $breakMinutes,
                    $status
                );

        } catch (RuntimeException $exception) {

            Session::flash(
                'error',
                $exception->getMessage()
            );

            $this->redirect(
                '/attendance/edit/' . $id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $model->update(
            $id,
            [
                'employee_id' =>
                    $employeeId,

                'attendance_date' =>
                    $attendanceDate,

                'scheduled_time_in' =>
                    $scheduledTimeIn,

                'scheduled_time_out' =>
                    $scheduledTimeOut,

                'time_in' =>
                    $calculation['time_in'],

                'time_out' =>
                    $calculation['time_out'],

                'break_minutes' =>
                    $breakMinutes,

                /*
                |--------------------------------------------------------------------------
                | ACTUAL DATABASE FIELDS
                |--------------------------------------------------------------------------
                */

                'late_minutes' =>
                    $calculation['late_minutes'],

                'undertime_minutes' =>
                    $calculation['undertime_minutes'],

                'overtime_minutes' =>
                    $calculation['overtime_minutes'],

                'total_minutes' =>
                    $calculation['total_minutes'],

                'status' =>
                    $calculation['status'],

                'remarks' =>
                    $notes !== ''
                        ? $notes
                        : null
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        Session::flash(
            'success',
            'Attendance updated successfully.'
        );

        $this->redirect(
            '/attendance'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Attendance
    |--------------------------------------------------------------------------
    */

    public function delete(int $id): void
    {
        Auth::requirePermission(
            'attendance.delete'
        );

        $model = new AttendanceModel();

        if (!$model->find($id)) {

            Session::flash(
                'error',
                'Attendance record not found.'
            );

            $this->redirect(
                '/attendance'
            );
        }

        $model->delete($id);

        Session::flash(
            'success',
            'Attendance record deleted successfully.'
        );

        $this->redirect(
            '/attendance'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Employee Attendance History
    |--------------------------------------------------------------------------
    */

    public function employee(
        int $employeeId
    ): void {

        Auth::requirePermission(
            'attendance.view'
        );

        $model = new AttendanceModel();

        $employee =
            $model->getEmployee(
                $employeeId
            );

        if (!$employee) {

            Session::flash(
                'error',
                'Employee not found.'
            );

            $this->redirect(
                '/attendance'
            );
        }

        $dateFrom = trim(
            $_GET['date_from']
                ?? date('Y-m-01')
        );

        $dateTo = trim(
            $_GET['date_to']
                ?? date('Y-m-d')
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Dates
        |--------------------------------------------------------------------------
        */

        if (
            !$this->validDate(
                $dateFrom
            )
        ) {

            $dateFrom =
                date('Y-m-01');
        }

        if (
            !$this->validDate(
                $dateTo
            )
        ) {

            $dateTo =
                date('Y-m-d');
        }

        if ($dateFrom > $dateTo) {

            $temp = $dateFrom;

            $dateFrom = $dateTo;

            $dateTo = $temp;
        }

        /*
        |--------------------------------------------------------------------------
        | Records
        |--------------------------------------------------------------------------
        */

        $records =
            $model->getByEmployee(
                $employeeId,
                $dateFrom,
                $dateTo
            );

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary =
            $model->getEmployeeSummary(
                $employeeId,
                $dateFrom,
                $dateTo
            );

        $payrollModel = new PayrollModel();
        $attendanceForPayroll = $payrollModel->getAttendance(
            $employeeId,
            $dateFrom,
            $dateTo
        );

        $payrollEngine = new PayrollEngine();
        $salary = $payrollEngine->calculate(
            $employee,
            $attendanceForPayroll,
            $this->getActivePayrollRules($payrollModel, $dateTo),
            $dateFrom,
            $dateTo
        );

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        $this->view(
            'modules/Attendance/Views/employee',
            [
                'employee' =>
                    $employee,

                'attendance' =>
                    $records,

                'records' =>
                    $records,

                'summary' =>
                    $summary,

                'salary' =>
                    $salary,

                'dateFrom' =>
                    $dateFrom,

                'dateTo' =>
                    $dateTo
            ]
        );
    }

    private function getActivePayrollRules(
        PayrollModel $model,
        string $date
    ): array {
        $stmt = $model->db->prepare("
            SELECT *
            FROM payroll_rules
            WHERE status = 'active'
              AND (effective_from IS NULL OR effective_from <= ?)
              AND (effective_to IS NULL OR effective_to >= ?)
            ORDER BY id ASC
        ");

        $stmt->execute([
            $date,
            $date
        ]);

        return $stmt->fetchAll();
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Attendance
    |--------------------------------------------------------------------------
    |
    | DATABASE SOURCE OF TRUTH
    |
    | total_minutes
    | overtime_minutes
    | late_minutes
    | undertime_minutes
    |
    | REGULAR HOURS ARE DERIVED:
    |
    | regular_minutes =
    |     total_minutes - overtime_minutes
    |
    | regular_hours =
    |     regular_minutes / 60
    |
    | overtime_hours =
    |     overtime_minutes / 60
    |
    */

    private function calculateAttendance(
        string $attendanceDate,
        string $timeIn,
        string $timeOut,
        ?string $scheduledTimeIn,
        ?string $scheduledTimeOut,
        int $breakMinutes,
        string $status
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Result
        |--------------------------------------------------------------------------
        */

        $result = [
            'time_in' =>
                null,

            'time_out' =>
                null,

            'total_minutes' =>
                0,

            'overtime_minutes' =>
                0,

            'late_minutes' =>
                0,

            'undertime_minutes' =>
                0,

            'status' =>
                $status
        ];

        /*
        |--------------------------------------------------------------------------
        | Normalize Break
        |--------------------------------------------------------------------------
        */

        $breakMinutes = max(
            0,
            min(
                $breakMinutes,
                1440
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Special Statuses
        |--------------------------------------------------------------------------
        |
        | These statuses have no worked time.
        |
        | Holiday is intentionally NOT here.
        |
        | A holiday may still have actual Time In / Time Out
        | and those minutes will be recorded for payroll.
        |
        */

        $noWorkStatuses = [
            'absent',
            'leave',
            'rest_day'
        ];

        if (
            in_array(
                $status,
                $noWorkStatuses,
                true
            )
        ) {

            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | Build DATETIME
        |--------------------------------------------------------------------------
        */

        if ($timeIn !== '') {

            $result['time_in'] =
                $attendanceDate .
                ' ' .
                $timeIn .
                ':00';
        }

        if ($timeOut !== '') {

            $result['time_out'] =
                $attendanceDate .
                ' ' .
                $timeOut .
                ':00';
        }

        /*
        |--------------------------------------------------------------------------
        | Missing Time
        |--------------------------------------------------------------------------
        */

        if (
            $result['time_in'] === null ||
            $result['time_out'] === null
        ) {

            /*
             * Manual half-day can be stored
             * without complete time data.
             */

            if ($status === 'half_day') {

                $result['status'] =
                    'half_day';
            }

            /*
             * Holiday with no actual time:
             * preserve holiday status.
             */

            if ($status === 'holiday') {

                $result['status'] =
                    'holiday';
            }

            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | Convert Times
        |--------------------------------------------------------------------------
        */

        $actualIn =
            strtotime(
                $result['time_in']
            );

        $actualOut =
            strtotime(
                $result['time_out']
            );

        if ($actualIn === false) {

            throw new RuntimeException(
                'Invalid Time In value.'
            );
        }

        if ($actualOut === false) {

            throw new RuntimeException(
                'Invalid Time Out value.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Time Order
        |--------------------------------------------------------------------------
        */

        if ($actualOut <= $actualIn) {

            throw new RuntimeException(
                'Time Out must be later than Time In.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Worked Minutes
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | 05:25 -> 20:00
        |
        | Total elapsed:
        | 875 minutes
        |
        | Break:
        | 60 minutes
        |
        | Worked:
        | 815 minutes
        |
        */

        $workedMinutes =
            (int) floor(
                (
                    $actualOut -
                    $actualIn
                ) / 60
            );

        /*
        |--------------------------------------------------------------------------
        | Deduct Break
        |--------------------------------------------------------------------------
        */

        $workedMinutes =
            max(
                0,
                $workedMinutes -
                $breakMinutes
            );

        /*
        |--------------------------------------------------------------------------
        | Late Calculation
        |--------------------------------------------------------------------------
        */

        if (
            $scheduledTimeIn !== null
        ) {

            $scheduledIn =
                strtotime(
                    $attendanceDate .
                    ' ' .
                    $scheduledTimeIn
                );

            if (
                $scheduledIn !== false &&
                $actualIn > $scheduledIn
            ) {

                $result['late_minutes'] =
                    (int) floor(
                        (
                            $actualIn -
                            $scheduledIn
                        ) / 60
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Scheduled Minutes
        |--------------------------------------------------------------------------
        */

        $scheduledMinutes = 0;

        if (
            $scheduledTimeIn !== null &&
            $scheduledTimeOut !== null
        ) {

            $scheduledIn =
                strtotime(
                    $attendanceDate .
                    ' ' .
                    $scheduledTimeIn
                );

            $scheduledOut =
                strtotime(
                    $attendanceDate .
                    ' ' .
                    $scheduledTimeOut
                );

            if (
                $scheduledIn !== false &&
                $scheduledOut !== false &&
                $scheduledOut > $scheduledIn
            ) {

                $scheduledMinutes =
                    (int) floor(
                        (
                            $scheduledOut -
                            $scheduledIn
                        ) / 60
                    );

                /*
                |--------------------------------------------------------------------------
                | Schedule Paid Minutes
                |--------------------------------------------------------------------------
                */

                $scheduledMinutes =
                    max(
                        0,
                        $scheduledMinutes -
                        $breakMinutes
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Total Minutes
        |--------------------------------------------------------------------------
        */

        $result['total_minutes'] =
            $workedMinutes;

        /*
        |--------------------------------------------------------------------------
        | Calculate Regular / OT / Undertime
        |--------------------------------------------------------------------------
        */

        if (
            $scheduledMinutes > 0
        ) {

            /*
            |--------------------------------------------------------------------------
            | Regular Minutes
            |--------------------------------------------------------------------------
            |
            | Regular cannot exceed scheduled paid time.
            |
            | Example:
            |
            | Scheduled = 480 minutes
            | Worked    = 815 minutes
            |
            | Regular = 480
            | OT      = 335
            |
            */

            $regularMinutes =
                min(
                    $workedMinutes,
                    $scheduledMinutes
                );

            /*
            |--------------------------------------------------------------------------
            | Overtime Minutes
            |--------------------------------------------------------------------------
            */

            $result['overtime_minutes'] =
                max(
                    0,
                    $workedMinutes -
                    $scheduledMinutes
                );

            /*
            |--------------------------------------------------------------------------
            | Undertime Minutes
            |--------------------------------------------------------------------------
            */

            $result['undertime_minutes'] =
                max(
                    0,
                    $scheduledMinutes -
                    $workedMinutes
                );

        } else {

            /*
            |--------------------------------------------------------------------------
            | No Schedule
            |--------------------------------------------------------------------------
            |
            | If an employee has no schedule,
            | record actual worked minutes as total.
            |
            | Do not automatically classify it as OT.
            |
            */

            $regularMinutes =
                $workedMinutes;

            $result['overtime_minutes'] =
                0;

            $result['undertime_minutes'] =
                0;
        }

        /*
        |--------------------------------------------------------------------------
        | Manual Statuses
        |--------------------------------------------------------------------------
        */

        if (
            $status === 'half_day'
        ) {

            $result['status'] =
                'half_day';

            return $result;
        }

        if (
            $status === 'holiday'
        ) {

            /*
             * Preserve holiday status.
             *
             * Actual worked minutes,
             * regular minutes and OT minutes
             * are still calculated above.
             */

            $result['status'] =
                'holiday';

            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | Automatic Status
        |--------------------------------------------------------------------------
        */

        if (
            $result['late_minutes'] > 0
        ) {

            $result['status'] =
                'late';

        } elseif (
            $result['total_minutes'] <= 0
        ) {

            $result['status'] =
                'absent';

        } else {

            $result['status'] =
                'present';
        }

        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Date
    |--------------------------------------------------------------------------
    */

    private function validDate(
        string $date
    ): bool {

        $dateObject =
            DateTime::createFromFormat(
                'Y-m-d',
                $date
            );

        return $dateObject !== false
            && $dateObject->format(
                'Y-m-d'
            ) === $date;
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Time
    |--------------------------------------------------------------------------
    */

    private function validTime(
        string $time
    ): bool {

        $timeObject =
            DateTime::createFromFormat(
                'H:i',
                $time
            );

        return $timeObject !== false
            && $timeObject->format(
                'H:i'
            ) === $time;
    }

    private function requireAttendanceEditPermission(): void
    {
        Auth::requireLogin();

        if (
            !Auth::can('attendance.manage') &&
            !Auth::can('attendance.edit')
        ) {
            if (ApiResponse::isRequest()) {
                ApiResponse::error(
                    403,
                    'FORBIDDEN',
                    'Only attendance managers can edit attendance date and time.'
                );
            }

            http_response_code(403);
            require BASE_PATH . '/modules/Auth/Views/unauthorized.php';
            exit;
        }
    }
}