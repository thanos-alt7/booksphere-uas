<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

$page_title = $page_title ?? 'BookSphere';
$current_page = $current_page ?? '';
$is_public_layout = $is_public_layout ?? false;
$is_logged_in = isset($_SESSION['id_user']);
$nama_user = $_SESSION['nama_lengkap'] ?? 'Guest';
$role_user = $_SESSION['role'] ?? 'Visitor';

function nav_active($page, $current_page)
{
    return $page === $current_page ? 'active' : '';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title); ?> | BookSphere</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')); ?>">
</head>
<body>
<?php if ($is_public_layout): ?>
<div class="public-shell">
    <header class="public-navbar">
        <a class="public-brand" href="<?= e(base_url('index.php')); ?>">
            <span style="font-size: x-large;">BookSphere</span>
        </a>
        <nav>
            <a href="<?= e(base_url('index.php#collection')); ?>">Collection</a>
            <a href="<?= e(base_url('index.php#features')); ?>">Features</a>
            <a href="<?= e(base_url('login.php')); ?>">Login</a>
        </nav>
    </header>
    <main class="public-main">
<?php else: ?>
<div class="app-shell">
    <aside class="app-sidebar" id="appSidebar">
        <a class="sidebar-brand" href="<?= e(base_url('index.php')); ?>">
            <span>BOOKSPHERE</span>
            <small>LIBRARY</small>
        </a>

        <nav class="sidebar-nav" aria-label="Navigasi utama">
            <a class="<?= nav_active('dashboard', $current_page); ?>" href="<?= e(base_url($is_logged_in ? 'dashboard.php' : 'index.php')); ?>">Dashboard</a>
            <a class="<?= nav_active('peminjaman', $current_page); ?>" href="<?= e(base_url('pages/peminjaman/index.php')); ?>">Peminjaman</a>
            <a class="<?= nav_active('buku', $current_page); ?>" href="<?= e(base_url('pages/buku/index.php')); ?>">Buku</a>
            <a class="<?= nav_active('kategori', $current_page); ?>" href="<?= e(base_url('pages/kategori/index.php')); ?>">Kategori</a>
            <a class="<?= nav_active('pengarang', $current_page); ?>" href="<?= e(base_url('pages/pengarang/index.php')); ?>">Pengarang</a>
            <a class="<?= nav_active('anggota', $current_page); ?>" href="<?= e(base_url('pages/anggota/index.php')); ?>">Anggota</a>
        </nav>

        <div class="sidebar-event">
            <span>ACARA MENDATANG</span>
            <strong>Pekan Perpustakaan</strong>
            <p>Kelola koleksi, anggota, dan transaksi buku dari satu dashboard.</p>
            <?php if ($is_logged_in): ?>
                <a href="<?= e(base_url('pages/peminjaman/tambah.php')); ?>">Pinjam Sekarang</a>
            <?php else: ?>
                <a href="<?= e(base_url('login.php')); ?>">Login</a>
            <?php endif; ?>
        </div>
    </aside>

    <div class="app-content">
        <header class="app-topbar">
            <button class="sidebar-toggle" type="button" data-toggle-target="#appSidebar" aria-label="Toggle menu"><span></span><span></span><span></span></button>
            <a class="mobile-brand" href="<?= e(base_url('dashboard.php')); ?>">BookSphere</a>
            <form class="top-search" action="<?= e(base_url('index.php')); ?>" method="get">
                <span class="search-icon" aria-hidden="true"></span>
                <input type="search" name="q" value="<?= e($_GET['q'] ?? ''); ?>" placeholder="Search for your next book...">
            </form>
            <div class="top-user">
                <span class="user-dot"></span>
                <div>
                    <strong><?= e($nama_user); ?></strong>
                    <small><?= e(ucfirst($role_user)); ?></small>
                </div>
                <?php if ($is_logged_in): ?>
                    <a class="logout-icon" href="<?= e(base_url('logout.php')); ?>" aria-label="Logout" title="Logout">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M10 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4" />
                            <path d="M15 7l5 5-5 5" />
                            <path d="M20 12H9" />
                        </svg>
                    </a>
                <?php else: ?>
                    <a href="<?= e(base_url('login.php')); ?>">Login</a>
                <?php endif; ?>
            </div>
        </header>
        <main class="app-main">
<?php endif; ?>