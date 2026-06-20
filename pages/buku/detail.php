<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$page_title = 'Detail Buku';
$current_page = 'buku';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('pages/buku/index.php?status=tidak_ditemukan');
}

$stmt = mysqli_prepare($conn, "SELECT
        b.*,
        k.nama_kategori,
        GROUP_CONCAT(p.nama_pengarang ORDER BY p.nama_pengarang SEPARATOR ', ') AS pengarang
    FROM buku b
    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
    LEFT JOIN buku_pengarang bp ON b.id_buku = bp.id_buku
    LEFT JOIN pengarang p ON bp.id_pengarang = p.id_pengarang
    WHERE b.id_buku = ?
    GROUP BY b.id_buku
    LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$buku = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$buku) {
    redirect('pages/buku/index.php?status=tidak_ditemukan');
}

require_once __DIR__ . '/../../includes/header.php';
?>
<section class="form-section">
    <div class="container">
        <div class="book-detail-card">
            <div class="book-detail-cover">
                <img src="<?= e(cover_buku($buku['gambar_sampul'])); ?>" alt="Sampul <?= e($buku['judul_buku']); ?>">
            </div>
            <div class="book-detail-body">
                <span class="stock-pill"><?= e($buku['nama_kategori'] ?: 'Tanpa kategori'); ?></span>
                <h1><?= e($buku['judul_buku']); ?></h1>
                <p class="detail-author"><?= e($buku['pengarang'] ?: 'Pengarang belum diisi'); ?></p>
                <p><?= e($buku['deskripsi'] ?: 'Deskripsi belum tersedia.'); ?></p>
                <div class="detail-grid">
                    <div><span>Tahun</span><strong><?= e($buku['tahun_terbit'] ?: '-'); ?></strong></div>
                    <div><span>ISBN</span><strong><?= e($buku['isbn'] ?: '-'); ?></strong></div>
                    <div><span>Stok Total</span><strong><?= e($buku['stok_total']); ?></strong></div>
                    <div><span>Tersedia</span><strong><?= e($buku['stok_tersedia']); ?></strong></div>
                </div>
                <div class="form-actions mt-4">
                    <a class="btn btn-dark-neo" href="<?= e(base_url('pages/buku/index.php')); ?>">Kembali</a>
                    <a class="btn btn-dark-neo" href="<?= e(base_url('pages/buku/edit.php?id=' . $buku['id_buku'])); ?>">Edit Buku</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>