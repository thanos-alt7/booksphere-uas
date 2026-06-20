<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$page_title = 'Peminjaman';
$current_page = 'peminjaman';

$keyword = trim($_GET['q'] ?? '');
$status_filter = $_GET['status'] ?? '';
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 8;
$offset = ($page - 1) * $per_page;
$status = $_GET['status_msg'] ?? '';
if ($status === '' && $status_filter === 'tambah') {
    $status = 'tambah';
}

$where = [];
$params = [];
$types = '';

if ($keyword !== '') {
    $where[] = '(a.nama_anggota LIKE ? OR u.nama_lengkap LIKE ? OR b.judul_buku LIKE ?)';
    $search = '%' . $keyword . '%';
    array_push($params, $search, $search, $search);
    $types .= 'sss';
}

if ($status_filter === 'dipinjam' || $status_filter === 'selesai') {
    $where[] = 'pm.status = ?';
    $params[] = $status_filter;
    $types .= 's';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count_sql = "SELECT COUNT(DISTINCT pm.id_peminjaman) AS total
    FROM peminjaman pm
    JOIN anggota a ON pm.id_anggota = a.id_anggota
    JOIN users u ON pm.id_user = u.id_user
    LEFT JOIN detail_peminjaman dp ON pm.id_peminjaman = dp.id_peminjaman
    LEFT JOIN buku b ON dp.id_buku = b.id_buku
    $where_sql";
$stmt = mysqli_prepare($conn, $count_sql);
$total_data = 0;
if ($stmt) {
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total_data = (int) mysqli_fetch_assoc($result)['total'];
    mysqli_stmt_close($stmt);
}

$peminjaman = [];
$sql = "SELECT
        pm.id_peminjaman,
        pm.tanggal_pinjam,
        pm.tanggal_jatuh_tempo,
        pm.status,
        a.nama_anggota,
        u.nama_lengkap AS petugas,
        COUNT(dp.id_detail) AS jumlah_buku,
        SUM(CASE WHEN dp.tanggal_kembali IS NULL THEN 1 ELSE 0 END) AS belum_kembali,
        GROUP_CONCAT(b.judul_buku ORDER BY b.judul_buku SEPARATOR ', ') AS daftar_buku
    FROM peminjaman pm
    JOIN anggota a ON pm.id_anggota = a.id_anggota
    JOIN users u ON pm.id_user = u.id_user
    LEFT JOIN detail_peminjaman dp ON pm.id_peminjaman = dp.id_peminjaman
    LEFT JOIN buku b ON dp.id_buku = b.id_buku
    $where_sql
    GROUP BY pm.id_peminjaman
    ORDER BY pm.tanggal_pinjam DESC, pm.id_peminjaman DESC
    LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    $list_params = $params;
    $list_types = $types . 'ii';
    $list_params[] = $per_page;
    $list_params[] = $offset;
    mysqli_stmt_bind_param($stmt, $list_types, ...$list_params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $peminjaman[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$total_pages = max(1, (int) ceil($total_data / $per_page));

$messages = [
    'tambah' => 'Transaksi peminjaman berhasil dibuat.',
    'kembali' => 'Buku berhasil dikembalikan dan stok sudah diperbarui oleh trigger.',
    'selesai' => 'Transaksi sudah selesai.',
    'tidak_ditemukan' => 'Data peminjaman tidak ditemukan.',
    'gagal' => 'Aksi gagal dilakukan.',
];

require_once __DIR__ . '/../../includes/header.php';
?>
<section class="data-section">
    <div class="container">
        <div class="data-heading">
            <div>
                <p>sirkulasi</p>
                <h1>Peminjaman</h1>
            </div>
            <a class="btn btn-yellow" href="<?= e(base_url('pages/peminjaman/tambah.php')); ?>">+ Tambah Peminjaman</a>
        </div>

        <?php if (isset($messages[$status])): ?>
            <div class="alert <?= $status === 'gagal' || $status === 'tidak_ditemukan' ? 'alert-warning' : 'alert-success'; ?> neo-alert" role="alert">
                <?= e($messages[$status]); ?>
            </div>
        <?php endif; ?>

        <div class="data-toolbar">
            <form action="<?= e(base_url('pages/peminjaman/index.php')); ?>" method="get" class="data-search">
                <input class="form-control" type="search" name="q" value="<?= e($keyword); ?>" placeholder="Cari anggota, petugas, atau judul buku...">
                <select class="form-select" name="status">
                    <option value="">Semua status</option>
                    <option value="dipinjam" <?= $status_filter === 'dipinjam' ? 'selected' : ''; ?>>Dipinjam</option>
                    <option value="selesai" <?= $status_filter === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                </select>
                <button class="btn btn-dark" type="submit">Search</button>
                <?php if ($keyword !== '' || $status_filter !== ''): ?>
                    <a class="btn btn-dark-neo" href="<?= e(base_url('pages/peminjaman/index.php')); ?>">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive data-table-wrap">
            <table class="table table-neo align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Anggota</th>
                        <th>Buku</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($peminjaman): ?>
                        <?php foreach ($peminjaman as $item): ?>
                            <?php $late = $item['status'] === 'dipinjam' && $item['tanggal_jatuh_tempo'] < date('Y-m-d'); ?>
                            <tr>
                                <td>#<?= e($item['id_peminjaman']); ?></td>
                                <td>
                                    <strong><?= e($item['nama_anggota']); ?></strong>
                                    <div class="table-note">Petugas: <?= e($item['petugas']); ?></div>
                                </td>
                                <td>
                                    <span class="stock-pill"><?= e($item['jumlah_buku']); ?> buku</span>
                                    <div class="table-note"><?= e($item['daftar_buku'] ?: '-'); ?></div>
                                </td>
                                <td>
                                    <?= e(format_tanggal($item['tanggal_pinjam'])); ?>
                                    <div class="table-note">Tempo: <?= e(format_tanggal($item['tanggal_jatuh_tempo'])); ?></div>
                                </td>
                                <td><span class="status-badge <?= $late ? 'late' : e($item['status']); ?>"><?= e($late ? 'terlambat' : $item['status']); ?></span></td>
                                <td><a class="btn btn-sm btn-yellow" href="<?= e(base_url('pages/peminjaman/detail.php?id=' . $item['id_peminjaman'])); ?>">Detail</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state compact text-center">
                                    <h3>Belum ada transaksi peminjaman.</h3>
                                    <p>Buat transaksi baru untuk mulai mencatat sirkulasi buku.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav class="pagination-wrap" aria-label="Navigasi halaman peminjaman">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a class="page-chip <?= $i === $page ? 'active' : ''; ?>" href="<?= e(base_url('pages/peminjaman/index.php?q=' . urlencode($keyword) . '&status=' . urlencode($status_filter) . '&page=' . $i)); ?>"><?= e($i); ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>