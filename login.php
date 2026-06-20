<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id_user'])) {
    redirect('dashboard.php');
}

$page_title = 'Login';
$current_page = 'login';
$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT id_user, username, password, nama_lengkap, role FROM users WHERE username = ? LIMIT 1');

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];

                redirect('dashboard.php');
            }
        }

        $error = 'Username atau password tidak sesuai.';
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="auth-card neo-panel">
                    <div class="auth-badge">Staff Area</div>
                    <h1>Login BookSphere</h1>
                    <p>Masuk sebagai admin atau petugas untuk mengelola buku, anggota, dan peminjaman.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-warning neo-alert" role="alert">
                            <?= e($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= e(base_url('login.php')); ?>" method="post" data-validate="true" novalidate>
                        <div class="mb-3">
                            <label class="form-label" for="username">Username</label>
                            <input class="form-control" type="text" id="username" name="username" value="<?= e($username); ?>" data-required="true" data-message="Username wajib diisi.">
                            <span class="inline-error" data-error-for="username"></span>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-control" type="password" id="password" name="password" data-required="true" data-message="Password wajib diisi.">
                            <span class="inline-error" data-error-for="password"></span>
                        </div>

                        <button class="btn btn-yellow w-100" type="submit">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>