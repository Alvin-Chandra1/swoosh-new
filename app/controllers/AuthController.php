<?php
declare(strict_types=1);

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (isLoggedIn()) {
            $this->redirect('dashboard');
        }
        $this->view('auth/login', ['title' => 'Masuk']);
    }

    public function login(): void
    {
        $this->requirePost();
        verify_csrf();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = (new User())->findByEmail($email);

        if (!$user || !password_verify($password, $user['password']) || !$user['is_active']) {
            flash('error', 'Email atau kata sandi belum cocok.');
            $this->redirect('login');
        }

        unset($user['password']);
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        flash('success', 'Selamat datang kembali, ' . $user['name'] . '!');
        $this->redirect('dashboard');
    }

    public function showRegister(): void
    {
        if (isLoggedIn()) {
            $this->redirect('dashboard');
        }
        $this->view('auth/register', ['title' => 'Buat akun']);
    }

    public function register(): void
    {
        $this->requirePost();
        verify_csrf();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';
        $role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';
        $phone = trim($_POST['phone'] ?? '');

        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || $password !== $passwordConfirmation) {
            flash('error', 'Lengkapi data dengan benar. Kata sandi minimal 8 karakter dan harus sama.');
            $this->redirect('register');
        }
        if ((new User())->findByEmail($email)) {
            flash('error', 'Email tersebut sudah terdaftar.');
            $this->redirect('register');
        }
        if ($role === 'admin' && !hash_equals((string) ($_ENV['ADMIN_REGISTER_CODE'] ?? ''), (string) ($_POST['admin_code'] ?? ''))) {
            flash('error', 'Kode registrasi admin tidak valid.');
            $this->redirect('register');
        }

        $userId = (new User())->create(compact('name', 'email', 'password', 'role', 'phone'));
        $user = (new User())->find($userId);
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        flash('success', $role === 'admin' ? 'Akun admin aktif. Tambahkan lapangan pertama Anda.' : 'Akun berhasil dibuat. Cari lapangan yang cocok untuk bermain.');
        $this->redirect('dashboard');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: ' . url());
        exit;
    }
}