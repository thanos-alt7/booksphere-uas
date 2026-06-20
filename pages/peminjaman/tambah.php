<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$page_title = 'Tambah Peminjaman';
$current_page = 'peminjaman';

$anggota = [];
$buku = [];
$result = mysqli_query($conn, 'SELECT id_anggota, nama_anggota, no_identitas FROM anggota ORDER BY nama_anggota ASC');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $anggota[] = $row;
    }
}
$result = mysqli_query($conn, "SELECT id_buku, judul_buku, stok_tersedia FROM buku WHERE stok_tersedia > 0 ORDER BY judul_buku ASC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $buku[] = $row;
    }
}

$id_anggota = 0;
$tanggal_pinjam = date('Y-m-d');
$tanggal_jatuh_tempo = date('Y-m-d', strtotime('+7 days'));
$buku_dipilih = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_anggota = (int) ($_POST['id_anggota'] ?? 0);
    $tanggal_pinjam = $_POST['tanggal_pinjam'] ?? date('Y-m-d');
    $tanggal_jatuh_tempo = $_POST['tanggal_jatuh_tempo'] ?? date('Y-m-d', strtotime('+7 days'));
    $buku_dipilih = array_values(array_unique(array_map('intval', $_POST['buku'] ?? [])));

    if ($id_anggota <= 0 || !$tanggal_pinjam || !$tanggal_jatuh_tempo || !$buku_dipilih) {
        $error = 'Anggota, tanggal, dan minimal satu buku wajib dipilih.';
    } elseif ($tanggal_jatuh_tempo < $tanggal_pinjam) {
        $error = 'Tanggal jatuh tempo tidak boleh sebelum tanggal pinjam.';
    } else {
        $placeholders = implode(',', array_fill(0, count($buku_dipilih), '?'));
        $types = str_repeat('i', count($buku_dipilih));
        $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM buku WHERE id_buku IN ($placeholders) AND stok_tersedia > 0");
        mysqli_stmt_bind_param($stmt, $types, ...$buku_dipilih);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $valid_books = (int) mysqli_fetch_assoc($result)['total'];
        mysqli_stmt_close($stmt);

        if ($valid_books !== count($buku_dipilih)) {
            $error = 'Ada buku yang stoknya tidak tersedia. Silakan pilih ulang.';
        } else {
            mysqli_begin_transaction($conn);
            try {
                $stmt = mysqli_prepare($conn, 'INSERT INTO peminjaman (id_anggota, id_user, tanggal_pinjam, tanggal_jatuh_tempo, status) VALUES (?, ?, ?, ?, "dipinjam")');
                $id_user = (int) $_SESSION['id_user'];
                mysqli_stmt_bind_param($stmt, 'iiss', $id_anggota, $id_user, $tanggal_pinjam, $tanggal_jatuh_tempo);
                mysqli_stmt_execute($stmt);
                $id_peminjaman = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                $stmt = mysqli_prepare($conn, 'INSERT INTO detail_peminjaman (id_peminjaman, id_buku) VALUES (?, ?)');
                foreach ($buku_dipilih as $id_buku) {
                    mysqli_stmt_bind_param($stmt, 'ii', $id_peminjaman, $id_buku);
                    mysqli_stmt_execute($stmt);
                }
                mysqli_stmt_close($stmt);

                mysqli_commit($conn);
                redirect('pages/peminjaman/index.php?status_msg=tambah');
            } catch (Throwable $e) {
                mysqli_rollback($conn);
                $error = 'Transaksi peminjaman gagal dibuat.';
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>
<section class="form-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="form-card">
                    <div class="form-heading">
                        <div>
                            <p>Sirkulasi</p>
                            <h1>Tambah Peminjaman</h1>
                        </div>
                    </div>

                    <?php if (!$anggota || !$buku): ?>
                        <div class="alert alert-warning neo-alert" role="alert">Pastikan data anggota tersedia dan ada buku dengan stok tersedia.</div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-warning neo-alert" role="alert"><?= e($error); ?></div>
                    <?php endif; ?>

                    <form action="<?= e(base_url('pages/peminjaman/tambah.php')); ?>" method="post" data-validate="true" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="id_anggota">Anggota</label>
                                <select class="form-select" id="id_anggota" name="id_anggota" data-required="true" data-message="Anggota wajib dipilih.">
                                    <option value="">Pilih anggota</option>
                                    <?php foreach ($anggota as $item): ?>
                                        <option value="<?= e($item['id_anggota']); ?>" <?= $id_anggota === (int) $item['id_anggota'] ? 'selected' : ''; ?>><?= e($item['nama_anggota']); ?> - <?= e($item['no_identitas']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="inline-error" data-error-for="id_anggota"></span>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="tanggal_pinjam">Tanggal Pinjam</label>
                                <input class="form-control" type="date" id="tanggal_pinjam" name="tanggal_pinjam" value="<?= e($tanggal_pinjam); ?>" data-required="true" data-message="Tanggal pinjam wajib diisi.">
                                <span class="inline-error" data-error-for="tanggal_pinjam"></span>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="tanggal_jatuh_tempo">Jatuh Tempo</label>
                                <input class="form-control" type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" value="<?= e($tanggal_jatuh_tempo); ?>" data-required="true" data-message="Tanggal jatuh tempo wajib diisi.">
                                <span class="inline-error" data-error-for="tanggal_jatuh_tempo"></span>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Pilih Buku Tersedia</label>
                                <div class="checkbox-grid loan-books">
                                    <?php foreach ($buku as $item): ?>
                                        <label>
                                            <input type="checkbox" name="buku[]" value="<?= e($item['id_buku']); ?>" <?= in_array((int) $item['id_buku'], $buku_dipilih, true) ? 'checked' : ''; ?>>
                                            <span><?= e($item['judul_buku']); ?> <small>Stok <?= e($item['stok_tersedia']); ?></small></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions mt-4">
                            <a class="btn btn-cancel-neo" href="<?= e(base_url('pages/peminjaman/index.php')); ?>">Batal</a>
                            <button class="btn btn-success-neo" type="submit">Simpan Peminjaman</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>