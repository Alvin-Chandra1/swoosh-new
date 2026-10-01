<?php
declare(strict_types=1);

final class AdminController extends Controller
{
    public function fields(): void
    {
        requireAdmin();
        $this->view('admin/fields', ['title' => 'Kelola lapangan', 'fields' => (new Field())->allForAdmin()]);
    }

    public function bookings(): void
    {
        requireAdmin();
        $this->view('admin/bookings', ['title' => 'Kelola booking', 'bookings' => (new Booking())->forAdmin((int) currentUser()['id'])]);
    }

    public function updateBooking(): void
    {
        requireAdmin();
        $this->requirePost();
        verify_csrf();
        $status = $_POST['status'] ?? 'pending';
        if (!in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'], true)) {
            $status = 'pending';
        }
        (new Booking())->updateStatus((int) $_POST['id'], $status, (int) currentUser()['id']);
        flash('success', 'Status booking diperbarui.');
        $this->redirect('admin/bookings');
    }
}