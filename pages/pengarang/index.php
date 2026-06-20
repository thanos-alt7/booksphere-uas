<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$page_title = 'Pengarang';
$current_page = 'pengarang';

$keyword = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 5;
$offset = ($page - 1) * $per_page;
$status = $_GET['status'] ?? '';

$total_data = 0;
$pengarang = [];

if ($keyword !== '') {
    $search = '%' . $keyword . '%';
    $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM pengarang WHERE nama_pengarang LIKE ? OR biografi LIKE ?');
    mysqli_stmt_bind_param($stmt, 'ss', $search, $search);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total_data = (int) mysqli_fetch_assoc($result)['total'];
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "SELECT
            p.id_pengarang,
            p.nama_pengarang,
            p.biografi,
            COUNT(bp.id_buku) AS jumlah_buku,
            GROUP_CONCAT(b.judul_buku ORDER BY b.judul_buku SEPARATOR ', ') AS daftar_buku
        FROM pengarang p
        LEFT JOIN buku_pengarang bp ON p.id_pengarang = bp.id_pengarang
        LEFT JOIN buku b ON bp.id_buku = b.id_buku
        WHERE p.nama_pengarang LIKE ? OR p.biografi LIKE ?
        GROUP BY p.id_pengarang
        ORDER BY p.nama_pengarang ASC
        LIMIT ? OFFSET ?");
    mysqli_stmt_bind_param($stmt, 'ssii', $search, $search, $per_page, $offset);
} else {
    $result = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM pengarang');
    if ($result) {
        $total_data = (int) mysqli_fetch_assoc($result)['total'];
    }

    $stmt = mysqli_prepare($conn, "SELECT
            p.id_pengarang,
            p.nama_pengarang,
            p.biografi,
            COUNT(bp.id_buku) AS jumlah_buku,
            GROUP_CONCAT(b.judul_buku ORDER BY b.judul_buku SEPARATOR ', ') AS daftar_buku
        FROM pengarang p
        LEFT JOIN buku_pengarang bp ON p.id_pengarang = bp.id_pengarang
        LEFT JOIN buku b ON bp.id_buku = b.id_buku
        GROUP BY p.id_pengarang
        ORDER BY p.nama_pengarang ASC
        LIMIT ? OFFSET ?");
    mysqli_stmt_bind_param($stmt, 'ii', $per_page, $offset);
}

if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $pengarang[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$total_pages = max(1, (int) ceil($total_data / $per_page));

$messages = [
    'tambah' => 'Pengarang baru berhasil ditambahkan.',
    'edit' => 'Pengarang berhasil diperbarui.',
    'hapus' => 'Pengarang berhasil dihapus.',
    'gagal_dipakai' => 'Pengarang tidak bisa dihapus karena masih terhubung dengan data buku.',
    'tidak_ditemukan' => 'Pengarang tidak ditemukan.',
];

require_once __DIR__ . '/../../includes/header.php';
?>
<section class="data-section">
    <div class="container">
        <div class="data-heading">
            <div>
                <p>Master Data</p>
                <h1>Pengarang</h1>
            </div>
            <a class="btn btn-yellow" href="<?= e(base_url('pages/pengarang/tambah.php')); ?>">+ Tambah Pengarang</a>
        </div>

        <?php if (isset($messages[$status])): ?>
            <div class="alert <?= $status === 'gagal_dipakai' || $status === 'tidak_ditemukan' ? 'alert-warning' : 'alert-success'; ?> neo-alert" role="alert">
                <?= e($messages[$status]); ?>
            </div>
        <?php endif; ?>

        <div class="data-toolbar">
            <form action="<?= e(base_url('pages/pengarang/index.php')); ?>" method="get" class="data-search">
                <input class="form-control" type="search" name="q" value="<?= e($keyword); ?>" placeholder="Cari nama pengarang atau biografi...">
                <button class="btn btn-yellow" type="submit">Search</button>
                <?php if ($keyword !== ''): ?>
                    <a class="btn btn-dark-neo" href="<?= e(base_url('pages/pengarang/index.php')); ?>">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive data-table-wrap">
            <table class="table table-neo align-middle">
                <thead>
                    <tr>
                        <th style="width: 70px;">No</th>
                        <th>Nama Pengarang</th>
                        <th>Biografi</th>
                        <th>Buku Terkait</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pengarang): ?>
                        <?php foreach ($pengarang as $index => $item): ?>
                            <tr>
                                <td><?= e($offset + $index + 1); ?></td>
                                <td><strong><?= e($item['nama_pengarang']); ?></strong></td>
                                <td><?= e($item['biografi'] ?: '-'); ?></td>
                                <td>
                                    <span class="stock-pill"><?= e($item['jumlah_buku']); ?> buku</span>
                                    <?php if ($item['daftar_buku']): ?>
                                        <div class="table-note"><?= e($item['daftar_buku']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a class="btn btn-sm btn-dark-neo" href="<?= e(base_url('pages/pengarang/edit.php?id=' . $item['id_pengarang'])); ?>">Edit</a>
                                        <a class="btn btn-sm btn-danger-neo" href="<?= e(base_url('pages/pengarang/hapus.php?id=' . $item['id_pengarang'])); ?>" data-confirm-delete="Yakin ingin menghapus pengarang <?= e($item['nama_pengarang']); ?>?">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state compact text-center">
                                    <h3>Data pengarang belum ada.</h3>
                                    <p>Tambahkan pengarang terlebih dahulu sebelum membuat data buku.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav class="pagination-wrap" aria-label="Navigasi halaman pengarang">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a class="page-chip <?= $i === $page ? 'active' : ''; ?>" href="<?= e(base_url('pages/pengarang/index.php?q=' . urlencode($keyword) . '&page=' . $i)); ?>"><?= e($i); ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>