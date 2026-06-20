<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$page_title = 'Detail Peminjaman';
$current_page = 'peminjaman';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('pages/peminjaman/index.php?status_msg=tidak_ditemukan');
}

$stmt = mysqli_prepare($conn, "SELECT
        pm.*,
        a.nama_anggota,
        a.no_identitas,
        a.no_telepon,
        u.nama_lengkap AS petugas
    FROM peminjaman pm
    JOIN anggota a ON pm.id_anggota = a.id_anggota
    JOIN users u ON pm.id_user = u.id_user
    WHERE pm.id_peminjaman = ?
    LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$peminjaman = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$peminjaman) {
    redirect('pages/peminjaman/index.php?status_msg=tidak_ditemukan');
}

$details = [];
$stmt = mysqli_prepare($conn, "SELECT
        dp.id_detail,
        dp.tanggal_kembali,
        dp.denda,
        b.id_buku,
        b.judul_buku,
        b.gambar_sampul,
        k.nama_kategori,
        GROUP_CONCAT(pg.nama_pengarang ORDER BY pg.nama_pengarang SEPARATOR ', ') AS pengarang
    FROM detail_peminjaman dp
    JOIN buku b ON dp.id_buku = b.id_buku
    LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
    LEFT JOIN buku_pengarang bp ON b.id_buku = bp.id_buku
    LEFT JOIN pengarang pg ON bp.id_pengarang = pg.id_pengarang
    WHERE dp.id_peminjaman = ?
    GROUP BY dp.id_detail
    ORDER BY dp.id_detail ASC");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $details[] = $row;
}
mysqli_stmt_close($stmt);

$late = $peminjaman['status'] === 'dipinjam' && $peminjaman['tanggal_jatuh_tempo'] < date('Y-m-d');

require_once __DIR__ . '/../../includes/header.php';
?>
<section class="form-section">
    <div class="container">
        <div class="data-heading">
            <div>
                <p>Detail Sirkulasi</p>
                <h1>Peminjaman #<?= e($peminjaman['id_peminjaman']); ?></h1>
            </div>
            <a class="btn btn-dark-neo" href="<?= e(base_url('pages/peminjaman/index.php')); ?>">Kembali</a>
        </div>

        <div class="loan-summary">
            <div>
                <span>Anggota</span>
                <strong><?= e($peminjaman['nama_anggota']); ?></strong>
                <small><?= e($peminjaman['no_identitas']); ?> · <?= e($peminjaman['no_telepon'] ?: '-'); ?></small>
            </div>
            <div>
                <span>Petugas</span>
                <strong><?= e($peminjaman['petugas']); ?></strong>
                <small>Dicatat oleh user login</small>
            </div>
            <div>
                <span>Tanggal</span>
                <strong><?= e(format_tanggal($peminjaman['tanggal_pinjam'])); ?></strong>
                <small>Tempo <?= e(format_tanggal($peminjaman['tanggal_jatuh_tempo'])); ?></small>
            </div>
            <div>
                <span>Status</span>
                <strong><span class="status-badge <?= $late ? 'late' : e($peminjaman['status']); ?>"><?= e($late ? 'terlambat' : $peminjaman['status']); ?></span></strong>
                <small><?= $late ? 'Lewat jatuh tempo' : 'Status transaksi'; ?></small>
            </div>
        </div>

        <div class="section-card mt-4">
            <div class="section-heading">
                <div>
                    <p>Loan Items</p>
                    <h2>Daftar Buku</h2>
                </div>
            </div>

            <div class="loan-item-list">
                <?php foreach ($details as $item): ?>
                    <article class="loan-item">
                        <img src="<?= e(cover_buku($item['gambar_sampul'])); ?>" alt="Sampul <?= e($item['judul_buku']); ?>">
                        <div>
                            <span><?= e($item['nama_kategori'] ?: 'Tanpa kategori'); ?></span>
                            <h3><?= e($item['judul_buku']); ?></h3>
                            <p><?= e($item['pengarang'] ?: 'Pengarang belum diisi'); ?></p>
                            <?php if ($item['tanggal_kembali']): ?>
                                <div class="member-meta">
                                    <span>Kembali <?= e(format_tanggal($item['tanggal_kembali'])); ?></span>
                                    <span>Denda <?= e(rupiah($item['denda'])); ?></span>
                                </div>
                            <?php else: ?>
                                <div class="member-meta"><span>Belum kembali</span></div>
                            <?php endif; ?>
                        </div>
                        <div class="loan-item-action">
                            <?php if (!$item['tanggal_kembali']): ?>
                                <a class="btn btn-sm btn-yellow" href="<?= e(base_url('pages/peminjaman/kembalikan.php?id_detail=' . $item['id_detail'])); ?>" data-confirm-delete="Proses pengembalian buku <?= e($item['judul_buku']); ?> sekarang?">Kembalikan</a>
                            <?php else: ?>
                                <span class="status-badge selesai">Selesai</span>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>