<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$page_title = 'Anggota';
$current_page = 'anggota';

$keyword = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 6;
$offset = ($page - 1) * $per_page;
$status = $_GET['status'] ?? '';

$total_data = 0;
$anggota = [];

if ($keyword !== '') {
    $search = '%' . $keyword . '%';
    $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM anggota WHERE nama_anggota LIKE ? OR no_identitas LIKE ? OR no_telepon LIKE ? OR alamat LIKE ?');
    mysqli_stmt_bind_param($stmt, 'ssss', $search, $search, $search, $search);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total_data = (int) mysqli_fetch_assoc($result)['total'];
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "SELECT
            a.id_anggota,
            a.nama_anggota,
            a.no_identitas,
            a.no_telepon,
            a.alamat,
            a.created_at,
            COUNT(p.id_peminjaman) AS total_peminjaman
        FROM anggota a
        LEFT JOIN peminjaman p ON a.id_anggota = p.id_anggota
        WHERE a.nama_anggota LIKE ? OR a.no_identitas LIKE ? OR a.no_telepon LIKE ? OR a.alamat LIKE ?
        GROUP BY a.id_anggota
        ORDER BY a.created_at DESC
        LIMIT ? OFFSET ?");
    mysqli_stmt_bind_param($stmt, 'ssssii', $search, $search, $search, $search, $per_page, $offset);
} else {
    $result = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM anggota');
    if ($result) {
        $total_data = (int) mysqli_fetch_assoc($result)['total'];
    }

    $stmt = mysqli_prepare($conn, "SELECT
            a.id_anggota,
            a.nama_anggota,
            a.no_identitas,
            a.no_telepon,
            a.alamat,
            a.created_at,
            COUNT(p.id_peminjaman) AS total_peminjaman
        FROM anggota a
        LEFT JOIN peminjaman p ON a.id_anggota = p.id_anggota
        GROUP BY a.id_anggota
        ORDER BY a.created_at DESC
        LIMIT ? OFFSET ?");
    mysqli_stmt_bind_param($stmt, 'ii', $per_page, $offset);
}

if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $anggota[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$total_pages = max(1, (int) ceil($total_data / $per_page));

$messages = [
    'tambah' => 'Anggota baru berhasil ditambahkan.',
    'edit' => 'Data anggota berhasil diperbarui.',
    'hapus' => 'Anggota berhasil dihapus.',
    'gagal_dipakai' => 'Anggota tidak bisa dihapus karena masih memiliki riwayat peminjaman.',
    'tidak_ditemukan' => 'Anggota tidak ditemukan.',
];

require_once __DIR__ . '/../../includes/header.php';
?>
<section class="data-section">
    <div class="container">
        <div class="data-heading">
            <div>
                <p>Master Data</p>
                <h1>Anggota</h1>
            </div>
            <a class="btn btn-yellow" href="<?= e(base_url('pages/anggota/tambah.php')); ?>">+ Tambah Anggota</a>
        </div>

        <?php if (isset($messages[$status])): ?>
            <div class="alert <?= $status === 'gagal_dipakai' || $status === 'tidak_ditemukan' ? 'alert-warning' : 'alert-success'; ?> neo-alert" role="alert">
                <?= e($messages[$status]); ?>
            </div>
        <?php endif; ?>

        <div class="data-toolbar">
            <form action="<?= e(base_url('pages/anggota/index.php')); ?>" method="get" class="data-search">
                <input class="form-control" type="search" name="q" value="<?= e($keyword); ?>" placeholder="Cari nama, identitas, telepon, atau alamat...">
                <button class="btn btn-yellow" type="submit">Search</button>
                <?php if ($keyword !== ''): ?>
                    <a class="btn btn-dark-neo" href="<?= e(base_url('pages/anggota/index.php')); ?>">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="row g-3">
            <?php if ($anggota): ?>
                <?php foreach ($anggota as $item): ?>
                    <div class="col-md-6 col-xl-4">
                        <article class="member-card">
                            <div class="member-avatar"><?= e(strtoupper(substr($item['nama_anggota'], 0, 1))); ?></div>
                            <div class="member-body">
                                <h2><?= e($item['nama_anggota']); ?></h2>
                                <p><?= e($item['no_identitas'] ?: 'No identitas belum diisi'); ?></p>
                                <div class="member-meta">
                                    <span><?= e($item['no_telepon'] ?: '-'); ?></span>
                                    <span><?= e($item['total_peminjaman']); ?> loan</span>
                                </div>
                                <p class="table-note"><?= e($item['alamat'] ?: 'Alamat belum diisi'); ?></p>
                                <div class="action-buttons mt-3">
                                    <a class="btn btn-sm btn-dark-neo" href="<?= e(base_url('pages/anggota/edit.php?id=' . $item['id_anggota'])); ?>">Edit</a>
                                    <a class="btn btn-sm btn-danger-neo" href="<?= e(base_url('pages/anggota/hapus.php?id=' . $item['id_anggota'])); ?>" data-confirm-delete="Yakin ingin menghapus anggota <?= e($item['nama_anggota']); ?>?">Hapus</a>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-state text-center">
                        <h3>Data anggota belum ada.</h3>
                        <p>Tambahkan anggota agar transaksi peminjaman bisa dibuat.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav class="pagination-wrap mt-4" aria-label="Navigasi halaman anggota">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a class="page-chip <?= $i === $page ? 'active' : ''; ?>" href="<?= e(base_url('pages/anggota/index.php?q=' . urlencode($keyword) . '&page=' . $i)); ?>"><?= e($i); ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>