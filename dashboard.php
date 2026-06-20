<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

$page_title = 'Dashboard';
$current_page = 'dashboard';

function get_total($conn, $sql)
{
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (int) ($row['total'] ?? 0);
}

$stats = [
    'buku' => get_total($conn, 'SELECT COUNT(*) AS total FROM buku'),
    'kategori' => get_total($conn, 'SELECT COUNT(*) AS total FROM kategori'),
    'pengarang' => get_total($conn, 'SELECT COUNT(*) AS total FROM pengarang'),
    'anggota' => get_total($conn, 'SELECT COUNT(*) AS total FROM anggota'),
    'dipinjam' => get_total($conn, "SELECT COUNT(*) AS total FROM peminjaman WHERE status = 'dipinjam'"),
    'terlambat' => get_total($conn, "SELECT COUNT(*) AS total FROM peminjaman WHERE status = 'dipinjam' AND tanggal_jatuh_tempo < CURDATE()"),
];

$buku_dipinjam = [];
$result = mysqli_query($conn, 'SELECT nama_anggota, judul_buku, tanggal_pinjam, tanggal_jatuh_tempo, status_tampil FROM vw_buku_dipinjam ORDER BY tanggal_jatuh_tempo ASC LIMIT 5');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $buku_dipinjam[] = $row;
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-heading section-line">
            <div>
                <p>Admin Dashboard</p>
                <h1>Halo, <?= e($_SESSION['nama_lengkap'] ?? 'Petugas'); ?>!</h1>
            </div>
            <a class="btn btn-dark" href="<?= e(base_url('pages/buku/index.php')); ?>">Kelola Buku</a>
        </div>

        <div class="row g-3 dashboard-stats">
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-yellow">
                    <div><span>Total Buku</span><strong><?= e($stats['buku']); ?></strong></div>
                    <svg class="stat-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v17H6.5A2.5 2.5 0 0 1 4 17.5v-12Z"/><path d="M7 17h13"/><path d="M8 7h8"/><path d="M8 11h8"/></svg>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-cyan">
                    <div><span>Total Anggota</span><strong><?= e($stats['anggota']); ?></strong></div>
                    <svg class="stat-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11a4 4 0 1 0-8 0"/><path d="M4 21a8 8 0 0 1 16 0"/><path d="M18 8a3 3 0 1 0-2-5"/><path d="M22 19a6 6 0 0 0-4-5"/></svg>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-green">
                    <div><span>Sedang Dipinjam</span><strong><?= e($stats['dipinjam']); ?></strong></div>
                    <svg class="stat-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></svg>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card stat-red">
                    <div><span>Terlambat</span><strong><?= e($stats['terlambat']); ?></strong></div>
                    <svg class="stat-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 22 20H2L12 3Z"/><path d="M12 9v5"/><path d="M12 17h.01"/></svg>
                </div>
            </div>
        </div>
        <div class="row g-4 mt-2">
            <div class="col-lg-8">
                <div class="neo-panel dashboard-panel">
                    <div class="panel-title">
                        <h2>Buku Sedang Dipinjam</h2>
                    </div>

                    <?php if ($buku_dipinjam): ?>
                        <div class="table-responsive">
                            <table class="table table-neo align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Anggota</th>
                                        <th>Buku</th>
                                        <th>Pinjam</th>
                                        <th>Jatuh Tempo</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($buku_dipinjam as $item): ?>
                                        <tr>
                                            <td><?= e($item['nama_anggota']); ?></td>
                                            <td><?= e($item['judul_buku']); ?></td>
                                            <td><?= e(format_tanggal($item['tanggal_pinjam'])); ?></td>
                                            <td><?= e(format_tanggal($item['tanggal_jatuh_tempo'])); ?></td>
                                            <td><span class="stock-pill"><?= e($item['status_tampil']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state compact">
                            <h3>Belum ada peminjaman aktif.</h3>
                            <p>Data dari view <strong>vw_buku_dipinjam</strong> akan muncul di sini setelah ada transaksi peminjaman.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="neo-panel dashboard-panel quick-panel">
                    <h2>Quick Actions</h2>
                    <a class="btn btn-yellow w-100" href="<?= e(base_url('pages/kategori/index.php')); ?>">Kelola Kategori</a>
                    <a class="btn btn-yellow w-100" href="<?= e(base_url('pages/pengarang/index.php')); ?>">Kelola Pengarang</a>
                    <a class="btn btn-yellow w-100" href="<?= e(base_url('pages/anggota/index.php')); ?>">Kelola Anggota</a>
                    <a class="btn btn-yellow w-100" href="<?= e(base_url('pages/peminjaman/index.php')); ?>">Kelola Peminjaman</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>