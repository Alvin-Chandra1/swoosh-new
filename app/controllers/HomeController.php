<?php
declare(strict_types=1);

final class HomeController extends Controller
{
    public function index(): void
    {
        $fieldModel = new Field();
        $this->view('home/index', [
            'title' => 'Sewa lapangan basket lebih mudah',
            'fields' => $fieldModel->all(),
            'cities' => $fieldModel->cities(),
        ]);
    }

    public function dashboard(): void
    {
        requireLogin();
        $bookingModel = new Booking();
        $fieldModel = new Field();
        $user = currentUser();
        $isOwner = isAdmin();
        $this->view('home/dashboard', [
            'title' => 'Dashboard',
            'upcoming' => $isOwner ? $bookingModel->upcomingForAdmin((int) $user['id']) : $bookingModel->upcomingForUser((int) $user['id']),
            'bookingCount' => $isOwner ? $bookingModel->countForAdmin((int) $user['id']) : $bookingModel->countForUser((int) $user['id']),
            'fields' => $isOwner ? $fieldModel->allForAdmin() : $fieldModel->all(),
        ]);
    }
}