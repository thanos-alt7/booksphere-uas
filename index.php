<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Beranda';
$current_page = 'home';
$is_public_layout = true;

$keyword = trim($_GET['q'] ?? '');
$stats = ['buku' => 0, 'anggota' => 0, 'pinjam' => 0];
$kategori = [];
$buku = [];

$result = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM buku');
if ($result) { $stats['buku'] = (int) mysqli_fetch_assoc($result)['total']; }
$result = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM anggota');
if ($result) { $stats['anggota'] = (int) mysqli_fetch_assoc($result)['total']; }
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM peminjaman WHERE status = 'dipinjam'");
if ($result) { $stats['pinjam'] = (int) mysqli_fetch_assoc($result)['total']; }

$result = mysqli_query($conn, 'SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC LIMIT 6');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) { $kategori[] = $row; }
}

$sql = "SELECT b.id_buku, b.judul_buku, b.gambar_sampul, b.stok_tersedia, k.nama_kategori,
        GROUP_CONCAT(p.nama_pengarang ORDER BY p.nama_pengarang SEPARATOR ', ') AS pengarang
    FROM buku b
    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
    LEFT JOIN buku_pengarang bp ON b.id_buku = bp.id_buku
    LEFT JOIN pengarang p ON bp.id_pengarang = p.id_pengarang";
$params = [];
$types = '';
if ($keyword !== '') {
    $sql .= ' WHERE b.judul_buku LIKE ? OR k.nama_kategori LIKE ? OR p.nama_pengarang LIKE ?';
    $search = '%' . $keyword . '%';
    $params = [$search, $search, $search];
    $types = 'sss';
}
$sql .= ' GROUP BY b.id_buku ORDER BY b.created_at DESC LIMIT 8';
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    if ($params) { mysqli_stmt_bind_param($stmt, $types, ...$params); }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) { $buku[] = $row; }
    mysqli_stmt_close($stmt);
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="public-hero">
    <div class="public-hero-copy">
        <span class="eyebrow">Sistem Manajemen Perpustakaan</span>
        <h1>Kelola dan temukan koleksi buku perpustakaan dengan lebih rapi.</h1>
        <p>Mencatat buku, anggota, peminjaman, pengembalian, stok, dan denda dalam satu sistem sederhana.</p>
        <form class="public-search" action="<?= e(base_url('index.php')); ?>" method="get">
            <input type="search" name="q" value="<?= e($keyword); ?>" placeholder="Cari judul, kategori, atau pengarang...">
            <button type="submit">Search</button>
        </form>
        <div class="public-actions">
            <a class="btn btn-yellow" href="<?= e(base_url('login.php')); ?>">Masuk Dashboard</a>
            <a class="btn btn-outline-soft" href="#collection">Lihat Koleksi</a>
        </div>
    </div>
    <div class="public-hero-panel">
        <div class="hero-card-top">Buku Baru di Rak</div>
        <div class="hero-cover-row">
            <?php foreach (array_slice($buku, 0, 3) as $item): ?>
                <div class="hero-cover"><img src="<?= e(cover_buku($item['gambar_sampul'])); ?>" alt="Sampul <?= e($item['judul_buku']); ?>"></div>
            <?php endforeach; ?>
            <?php if (!$buku): ?>
                <div class="hero-cover"><img src="<?= e(base_url('assets/img/default-cover.jpg')); ?>" alt="Default cover"></div>
                <div class="hero-cover placeholder"></div>
                <div class="hero-cover placeholder"></div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="public-stats" aria-label="Statistik perpustakaan">
    <div><strong><?= e($stats['buku']); ?>+</strong><span>Buku</span></div>
    <div><strong><?= e($stats['anggota']); ?>+</strong><span>Anggota</span></div>
    <div><strong><?= e($stats['pinjam']); ?></strong><span>Peminjaman Aktif</span></div>
</section>

<section class="public-section" id="collection">
    <div class="section-heading">
        <div>
            <p>Koleksi</p>
            <h2><?= $keyword !== '' ? 'Hasil Pencarian' : 'Koleksi Terbaru'; ?></h2>
        </div>
    </div>
    <?php if ($buku): ?>
        <div class="public-book-grid">
            <?php foreach ($buku as $item): ?>
                <article class="home-book-card">
                    <div class="book-cover-frame"><img src="<?= e(cover_buku($item['gambar_sampul'])); ?>" alt="Sampul <?= e($item['judul_buku']); ?>"></div>
                    <h3><?= e($item['judul_buku']); ?></h3>
                    <p><?= e($item['pengarang'] ?: 'Pengarang belum diisi'); ?></p>
                    <span class="stock-pill"><?= e($item['nama_kategori'] ?: 'Tanpa kategori'); ?></span>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state"><h3>Belum ada buku yang ditampilkan.</h3><p>Data buku akan muncul otomatis setelah dimasukkan dari dashboard.</p></div>
    <?php endif; ?>
</section>

<section class="public-section" id="features">
    <div class="public-feature-grid">
        <div><strong>Fitur</strong><span>Buku, kategori, pengarang, anggota.</span></div>
        <div><strong>Transaksi</strong><span>Peminjaman, pengembalian, denda.</span></div>
        <div><strong>Database</strong><span>Database terintegrasi.</span></div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>