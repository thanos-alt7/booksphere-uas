<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

$page_title = 'Tambah Buku';
$current_page = 'buku';

$kategori = [];
$pengarang = [];
$result = mysqli_query($conn, 'SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $kategori[] = $row;
    }
}
$result = mysqli_query($conn, 'SELECT id_pengarang, nama_pengarang FROM pengarang ORDER BY nama_pengarang ASC');
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pengarang[] = $row;
    }
}

$judul_buku = '';
$tahun_terbit = '';
$isbn = '';
$stok_total = 0;
$stok_tersedia = 0;
$deskripsi = '';
$id_kategori = 0;
$pengarang_dipilih = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul_buku = trim($_POST['judul_buku'] ?? '');
    $tahun_terbit = trim($_POST['tahun_terbit'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $stok_total = max(0, (int) ($_POST['stok_total'] ?? 0));
    $stok_tersedia = max(0, (int) ($_POST['stok_tersedia'] ?? $stok_total));
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $id_kategori = (int) ($_POST['id_kategori'] ?? 0);
    $pengarang_dipilih = array_map('intval', $_POST['pengarang'] ?? []);

    if ($judul_buku === '' || $id_kategori <= 0 || $stok_total < 1) {
        $error = 'Judul, kategori, dan stok total wajib diisi dengan benar.';
    } elseif ($stok_tersedia > $stok_total) {
        $error = 'Stok tersedia tidak boleh lebih besar dari stok total.';
    } else {
        $upload = upload_cover($_FILES['gambar_sampul'] ?? null);
        if (!$upload['success']) {
            $error = $upload['error'];
        } else {
            mysqli_begin_transaction($conn);
            try {
                $stmt = mysqli_prepare($conn, 'INSERT INTO buku (judul_buku, tahun_terbit, isbn, stok_total, stok_tersedia, deskripsi, gambar_sampul, id_kategori) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $tahun_value = $tahun_terbit !== '' ? $tahun_terbit : null;
                mysqli_stmt_bind_param($stmt, 'sssiissi', $judul_buku, $tahun_value, $isbn, $stok_total, $stok_tersedia, $deskripsi, $upload['filename'], $id_kategori);
                mysqli_stmt_execute($stmt);
                $id_buku = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                if ($pengarang_dipilih) {
                    $stmt = mysqli_prepare($conn, 'INSERT INTO buku_pengarang (id_buku, id_pengarang) VALUES (?, ?)');
                    foreach (array_unique($pengarang_dipilih) as $id_pengarang) {
                        mysqli_stmt_bind_param($stmt, 'ii', $id_buku, $id_pengarang);
                        mysqli_stmt_execute($stmt);
                    }
                    mysqli_stmt_close($stmt);
                }

                mysqli_commit($conn);
                redirect('pages/buku/index.php?status=tambah');
            } catch (Throwable $e) {
                mysqli_rollback($conn);
                $error = 'Buku gagal ditambahkan.';
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
                            <p>Koleksi</p>
                            <h1>Tambah Buku</h1>
                        </div>
                    </div>

                    <?php if (!$kategori || !$pengarang): ?>
                        <div class="alert alert-warning neo-alert" role="alert">Isi data kategori dan pengarang terlebih dahulu sebelum menambahkan buku.</div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-warning neo-alert" role="alert"><?= e($error); ?></div>
                    <?php endif; ?>

                    <form action="<?= e(base_url('pages/buku/tambah.php')); ?>" method="post" enctype="multipart/form-data" data-validate="true" novalidate>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label" for="judul_buku">Judul Buku</label>
                                <input class="form-control" type="text" id="judul_buku" name="judul_buku" value="<?= e($judul_buku); ?>" data-required="true" data-message="Judul buku wajib diisi.">
                                <span class="inline-error" data-error-for="judul_buku"></span>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="tahun_terbit">Tahun Terbit</label>
                                <input class="form-control" type="number" min="1000" max="<?= e(date('Y')); ?>" id="tahun_terbit" name="tahun_terbit" value="<?= e($tahun_terbit); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="isbn">ISBN</label>
                                <input class="form-control" type="text" id="isbn" name="isbn" value="<?= e($isbn); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="id_kategori">Kategori</label>
                                <select class="form-select" id="id_kategori" name="id_kategori" data-required="true" data-message="Kategori wajib dipilih.">
                                    <option value="">Pilih kategori</option>
                                    <?php foreach ($kategori as $item): ?>
                                        <option value="<?= e($item['id_kategori']); ?>" <?= $id_kategori === (int) $item['id_kategori'] ? 'selected' : ''; ?>><?= e($item['nama_kategori']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="inline-error" data-error-for="id_kategori"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="stok_total">Stok Total</label>
                                <input class="form-control" type="number" min="1" id="stok_total" name="stok_total" value="<?= e($stok_total); ?>" data-required="true" data-message="Stok total wajib diisi.">
                                <span class="inline-error" data-error-for="stok_total"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="stok_tersedia">Stok Tersedia</label>
                                <input class="form-control" type="number" min="0" id="stok_tersedia" name="stok_tersedia" value="<?= e($stok_tersedia); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Pengarang</label>
                                <div class="checkbox-grid">
                                    <?php foreach ($pengarang as $item): ?>
                                        <label><input type="checkbox" name="pengarang[]" value="<?= e($item['id_pengarang']); ?>" <?= in_array((int) $item['id_pengarang'], $pengarang_dipilih, true) ? 'checked' : ''; ?>> <?= e($item['nama_pengarang']); ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="gambar_sampul">Upload Cover</label>
                                <input class="form-control" type="file" id="gambar_sampul" name="gambar_sampul" accept="image/jpeg,image/png,image/webp">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="deskripsi">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5"><?= e($deskripsi); ?></textarea>
                            </div>
                        </div>

                        <div class="form-actions mt-4">
                            <a class="btn btn-cancel-neo" href="<?= e(base_url('pages/buku/index.php')); ?>">Batal</a>
                            <button class="btn btn-success-neo" type="submit">Simpan Buku</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>