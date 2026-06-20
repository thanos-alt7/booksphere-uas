<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$page_title = 'Kategori';
$current_page = 'kategori';

$keyword = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 5;
$offset = ($page - 1) * $per_page;
$status = $_GET['status'] ?? '';

$total_data = 0;
$kategori = [];

if ($keyword !== '') {
    $search = '%' . $keyword . '%';
    $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM kategori WHERE nama_kategori LIKE ? OR deskripsi LIKE ?');
    mysqli_stmt_bind_param($stmt, 'ss', $search, $search);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total_data = (int) mysqli_fetch_assoc($result)['total'];
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, 'SELECT id_kategori, nama_kategori, deskripsi, fn_jumlah_buku_kategori(id_kategori) AS jumlah_buku FROM kategori WHERE nama_kategori LIKE ? OR deskripsi LIKE ? ORDER BY nama_kategori ASC LIMIT ? OFFSET ?');
    mysqli_stmt_bind_param($stmt, 'ssii', $search, $search, $per_page, $offset);
} else {
    $result = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM kategori');
    if ($result) {
        $total_data = (int) mysqli_fetch_assoc($result)['total'];
    }

    $stmt = mysqli_prepare($conn, 'SELECT id_kategori, nama_kategori, deskripsi, fn_jumlah_buku_kategori(id_kategori) AS jumlah_buku FROM kategori ORDER BY nama_kategori ASC LIMIT ? OFFSET ?');
    mysqli_stmt_bind_param($stmt, 'ii', $per_page, $offset);
}

if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $kategori[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$total_pages = max(1, (int) ceil($total_data / $per_page));

$messages = [
    'tambah' => 'Kategori baru berhasil ditambahkan.',
    'edit' => 'Kategori berhasil diperbarui.',
    'hapus' => 'Kategori berhasil dihapus.',
    'gagal_dipakai' => 'Kategori tidak bisa dihapus karena masih dipakai oleh data buku.',
    'tidak_ditemukan' => 'Kategori tidak ditemukan.',
];

require_once __DIR__ . '/../../includes/header.php';
?>
<section class="data-section">
    <div class="container">
        <div class="data-heading section-line">
            <div>
                <p>Master Data</p>
                <h1>Kategori Buku</h1>
            </div>
            <a class="btn btn-yellow" href="<?= e(base_url('pages/kategori/tambah.php')); ?>">+ Tambah Kategori</a>
        </div>

        <?php if (isset($messages[$status])): ?>
            <div class="alert <?= $status === 'gagal_dipakai' || $status === 'tidak_ditemukan' ? 'alert-warning' : 'alert-success'; ?> neo-alert" role="alert">
                <?= e($messages[$status]); ?>
            </div>
        <?php endif; ?>

        <div class="data-toolbar neo-card">
            <form action="<?= e(base_url('pages/kategori/index.php')); ?>" method="get" class="data-search">
                <input class="form-control" type="search" name="q" value="<?= e($keyword); ?>" placeholder="Cari kategori atau deskripsi...">
                <button class="btn btn-yellow" type="submit">Search</button>
                <?php if ($keyword !== ''): ?>
                    <a class="btn btn-dark-neo" href="<?= e(base_url('pages/kategori/index.php')); ?>">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-responsive data-table-wrap">
            <table class="table table-neo align-middle">
                <thead>
                    <tr>
                        <th style="width: 70px;">No</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th style="width: 150px;">Jumlah Buku</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($kategori): ?>
                        <?php foreach ($kategori as $index => $item): ?>
                            <tr>
                                <td><?= e($offset + $index + 1); ?></td>
                                <td><strong><?= e($item['nama_kategori']); ?></strong></td>
                                <td><?= e($item['deskripsi'] ?: '-'); ?></td>
                                <td><span class="stock-pill"><?= e($item['jumlah_buku']); ?> buku</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <a class="btn btn-sm btn-dark-neo" href="<?= e(base_url('pages/kategori/edit.php?id=' . $item['id_kategori'])); ?>">Edit</a>
                                        <a class="btn btn-sm btn-danger-neo" href="<?= e(base_url('pages/kategori/hapus.php?id=' . $item['id_kategori'])); ?>" data-confirm-delete="Yakin ingin menghapus kategori <?= e($item['nama_kategori']); ?>?">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state compact text-center">
                                    <h3>Data kategori belum ada.</h3>
                                    <p>Mulai dengan menambahkan kategori seperti Fiction, Science, History, atau Technology.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav class="pagination-wrap" aria-label="Navigasi halaman kategori">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a class="page-chip <?= $i === $page ? 'active' : ''; ?>" href="<?= e(base_url('pages/kategori/index.php?q=' . urlencode($keyword) . '&page=' . $i)); ?>"><?= e($i); ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>