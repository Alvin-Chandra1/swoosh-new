<?php
declare(strict_types=1);

final class FieldController extends Controller
{
    public function index(): void
    {
        $model = new Field();
        $this->view('fields/index', [
            'title' => 'Temukan lapangan',
            'fields' => $model->all($_GET),
            'cities' => $model->cities(),
        ]);
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $field = (new Field())->find($id);
        if (!$field) {
            http_response_code(404);
            exit('Lapangan tidak ditemukan.');
        }
        $this->view('fields/show', ['title' => $field['name'], 'field' => $field]);
    }

    public function create(): void
    {
        requireAdmin();
        $this->view('admin/field-form', ['title' => 'Tambah lapangan', 'field' => null]);
    }

    public function store(): void
    {
        requireAdmin();
        $this->requirePost();
        verify_csrf();
        if (!$this->validFieldInput()) {
            $this->redirect('admin/fields/create');
        }
        (new Field())->create($_POST, (int) currentUser()['id']);
        flash('success', 'Lapangan berhasil ditambahkan dan siap disewa.');
        $this->redirect('admin/fields');
    }

    public function edit(): void
    {
        requireAdmin();
        $field = (new Field())->find((int) ($_GET['id'] ?? 0));
        if (!$field || (int) $field['owner_id'] !== (int) currentUser()['id']) {
            flash('error', 'Lapangan tidak ditemukan.');
            $this->redirect('admin/fields');
        }
        $this->view('admin/field-form', ['title' => 'Edit lapangan', 'field' => $field]);
    }

    public function update(): void
    {
        requireAdmin();
        $this->requirePost();
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $field = (new Field())->find($id);
        if (!$field || (int) $field['owner_id'] !== (int) currentUser()['id'] || !$this->validFieldInput(false)) {
            $this->redirect('admin/fields');
        }
        (new Field())->update($id, $_POST);
        flash('success', 'Perubahan lapangan disimpan.');
        $this->redirect('admin/fields');
    }

    public function delete(): void
    {
        requireAdmin();
        $this->requirePost();
        verify_csrf();
        $field = (new Field())->find((int) ($_POST['id'] ?? 0));
        if ($field && (int) $field['owner_id'] === (int) currentUser()['id']) {
            (new Field())->delete((int) $field['id']);
            flash('success', 'Lapangan disembunyikan dari daftar publik.');
        }
        $this->redirect('admin/fields');
    }

    private function validFieldInput(bool $redirect = true): bool
    {
        $valid = trim($_POST['name'] ?? '') !== '' &&
            trim($_POST['location'] ?? '') !== '' &&
            trim($_POST['city'] ?? '') !== '' &&
            (int) ($_POST['price_per_hour'] ?? 0) > 0;
        if (!$valid && $redirect) {
            flash('error', 'Nama, lokasi, kota, dan harga lapangan wajib diisi.');
        }
        return $valid;
    }
}