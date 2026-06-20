<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$page_title = 'Buku';
$current_page = 'buku';

$keyword = trim($_GET['q'] ?? '');
$kategori_filter = (int) ($_GET['kategori'] ?? 0);
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 8;
$offset = ($page - 1) * $per_page;
$status = $_GET['status'] ?? '';

$where = [];
$params = [];
$types = '';

if ($keyword !== '') {
    $where[] = '(b.judul_buku LIKE ? OR b.isbn LIKE ? OR k.nama_kategori LIKE ? OR p.nama_pengarang LIKE ?)';
    $search = '%' . $keyword . '%';
    array_push($params, $search, $search, $search, $search);
    $types .= 'ssss';
}

if ($kategori_filter > 0) {
    $where[] = 'b.id_kategori = ?';
    $params[] = $kategori_filter;
    $types .= 'i';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total_data = 0;
$count_sql = "SELECT COUNT(DISTINCT b.id_buku) AS total
    FROM buku b
    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
    LEFT JOIN buku_pengarang bp ON b.id_buku = bp.id_buku
    LEFT JOIN pengarang p ON bp.id_pengarang = p.id_pengarang
    $where_sql";
$stmt = mysqli_prepare($conn, $count_sql);
if ($stmt) {
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total_data = (int) mysqli_fetch_assoc($result)['total'];
    mysqli_stmt_close($stmt);
}

$buku = [];
$sql = "SELECT
        b.id_buku,
        b.judul_buku,
        b.tahun_terbit,
        b.isbn,
        b.stok_total,
        b.stok_tersedia,
        b.gambar_sampul,
        k.nama_kategori,
        GROUP_CONCAT(p.nama_pengarang ORDER BY p.nama_pengarang SEPARATOR ', ') AS pengarang
    FROM buku b
    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
    LEFT JOIN buku_pengarang bp ON b.id_buku = bp.id_buku
    LEFT JOIN pengarang p ON bp.id_pengarang = p.id_pengarang
    $where_sql
    GROUP BY b.id_buku
    ORDER BY b.created_at DESC
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
        $buku[] = $row;
    }
    mysqli_stmt_close($stmt);
}

$kategori = [];
$result = mysqli_query($conn, 'SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $kategori[] = $row;
    }
}

$total_pages = max(1, (int) ceil($total_data / $per_page));

$messages = [
    'tambah' => 'Buku baru berhasil ditambahkan.',
    'edit' => 'Data buku berhasil diperbarui.',
    'hapus' => 'Buku berhasil dihapus.',
    'gagal_dipakai' => 'Buku tidak bisa dihapus karena masih memiliki riwayat peminjaman.',
    'tidak_ditemukan' => 'Buku tidak ditemukan.',
];

require_once __DIR__ . '/../../includes/header.php';
?>
<section class="data-section">
    <div class="container">
        <div class="data-heading">
            <div>
                <p>Koleksi</p>
                <h1>Temukan Buku Kesukaanmu</h1>
            </div>
            <a class="btn btn-yellow" href="<?= e(base_url('pages/buku/tambah.php')); ?>">+ Tambah Buku</a>
        </div>

        <?php if (isset($messages[$status])): ?>
            <div class="alert <?= $status === 'gagal_dipakai' || $status === 'tidak_ditemukan' ? 'alert-warning' : 'alert-success'; ?> neo-alert" role="alert">
                <?= e($messages[$status]); ?>
            </div>
        <?php endif; ?>

        <div class="data-toolbar">
            <form action="<?= e(base_url('pages/buku/index.php')); ?>" method="get" class="data-search">
                <input class="form-control" type="search" name="q" value="<?= e($keyword); ?>" placeholder="Cari judul, ISBN, kategori, atau pengarang...">
                <select class="form-select" name="kategori">
                    <option value="0">Semua kategori</option>
                    <?php foreach ($kategori as $item): ?>
                        <option value="<?= e($item['id_kategori']); ?>" <?= $kategori_filter === (int) $item['id_kategori'] ? 'selected' : ''; ?>><?= e($item['nama_kategori']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-yellow" type="submit">Search</button>
                <?php if ($keyword !== '' || $kategori_filter > 0): ?>
                    <a class="btn btn-dark-neo" href="<?= e(base_url('pages/buku/index.php')); ?>">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($buku): ?>
            <div class="book-grid-admin">
                <?php foreach ($buku as $item): ?>
                    <article class="book-list-card">
                        <a class="book-list-cover" href="<?= e(base_url('pages/buku/detail.php?id=' . $item['id_buku'])); ?>">
                            <img src="<?= e(cover_buku($item['gambar_sampul'])); ?>" alt="Sampul <?= e($item['judul_buku']); ?>">
                        </a>
                        <div class="book-list-body">
                            <span><?= e($item['nama_kategori'] ?: 'Tanpa kategori'); ?></span>
                            <h2><?= e($item['judul_buku']); ?></h2>
                            <p><?= e($item['pengarang'] ?: 'Pengarang belum diisi'); ?></p>
                            <div class="member-meta">
                                <span><?= e($item['tahun_terbit'] ?: '-'); ?></span>
                                <span>Stok <?= e($item['stok_tersedia']); ?>/<?= e($item['stok_total']); ?></span>
                            </div>
                            <div class="action-buttons mt-3">
                                <a class="btn btn-sm btn-outline-soft" href="<?= e(base_url('pages/buku/detail.php?id=' . $item['id_buku'])); ?>">Detail</a>
                                <a class="btn btn-sm btn-dark-neo" href="<?= e(base_url('pages/buku/edit.php?id=' . $item['id_buku'])); ?>">Edit</a>
                                <a class="btn btn-sm btn-danger-neo" href="<?= e(base_url('pages/buku/hapus.php?id=' . $item['id_buku'])); ?>" data-confirm-delete="Yakin ingin menghapus buku <?= e($item['judul_buku']); ?>?">Hapus</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state text-center">
                <h3>Data buku belum ada.</h3>
                <p>Tambahkan buku pertama untuk mulai menampilkan koleksi perpustakaan.</p>
            </div>
        <?php endif; ?>

        <?php if ($total_pages > 1): ?>
            <nav class="pagination-wrap mt-4" aria-label="Navigasi halaman buku">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a class="page-chip <?= $i === $page ? 'active' : ''; ?>" href="<?= e(base_url('pages/buku/index.php?q=' . urlencode($keyword) . '&kategori=' . $kategori_filter . '&page=' . $i)); ?>"><?= e($i); ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>