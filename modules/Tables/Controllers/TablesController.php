<?php

require_once BASE_PATH . '/modules/Tables/Models/TablesModel.php';

class TablesController extends Controller
{
    private TablesModel $model;

    public function __construct()
    {
        $this->model = new TablesModel();
    }

    public function index(): void
    {
        Auth::requirePermission('pos.edit');
        $this->view('modules/Tables/Views/index', [
            'tables' => $this->model->getAll(),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('pos.edit');
        $this->view('modules/Tables/Views/form', [
            'table' => [],
            'action' => BASE_URL . '/tables/create',
            'title' => 'Add Table',
            'error' => Session::getFlash('error')
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('pos.edit');
        $number = trim($_POST['table_number'] ?? '');
        $capacity = max(1, min(99, (int) ($_POST['capacity'] ?? 2)));
        if ($number === '' || $this->model->findByNumber($number)) {
            Session::flash('error', 'Enter a unique table number.');
            $this->redirect('/tables/create');
        }
        $this->model->create($number, $capacity);
        Session::flash('success', 'Table added successfully.');
        $this->redirect('/tables');
    }

    public function edit(int $id): void
    {
        Auth::requirePermission('pos.edit');
        $table = $this->model->find($id);
        if (!$table) {
            Session::flash('error', 'Table not found.');
            $this->redirect('/tables');
        }
        $this->view('modules/Tables/Views/form', [
            'table' => $table,
            'action' => BASE_URL . '/tables/edit/' . $id,
            'title' => 'Edit Table',
            'error' => Session::getFlash('error')
        ]);
    }

    public function update(int $id): void
    {
        Auth::requirePermission('pos.edit');
        $number = trim($_POST['table_number'] ?? '');
        $capacity = max(1, min(99, (int) ($_POST['capacity'] ?? 2)));
        $status = in_array($_POST['status'] ?? 'available', ['available', 'occupied', 'reserved', 'inactive'], true) ? $_POST['status'] : 'available';
        if ($number === '' || $this->model->findByNumber($number, $id)) {
            Session::flash('error', 'Enter a unique table number.');
            $this->redirect('/tables/edit/' . $id);
        }
        $this->model->update($id, $number, $capacity, $status);
        Session::flash('success', 'Table updated successfully.');
        $this->redirect('/tables');
    }

    public function delete(int $id): void
    {
        Auth::requirePermission('pos.edit');
        $this->model->delete($id);
        Session::flash('success', 'Table disabled successfully.');
        $this->redirect('/tables');
    }

    public function release(int $id): void
    {
        Auth::requirePermission('pos.edit');

        if (!$this->model->release($id)) {
            Session::flash('error', 'This table is no longer occupied. Refresh and try again.');
            $this->redirect('/tables');
        }

        Session::flash('success', 'Table released and now available.');
        $this->redirect('/tables');
    }
}
