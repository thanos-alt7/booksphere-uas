<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$page_title = 'Edit Kategori';
$current_page = 'kategori';

$id = (int) ($_GET['id'] ?? 0);
$error = '';

if ($id <= 0) {
    redirect('pages/kategori/index.php?status=tidak_ditemukan');
}

$stmt = mysqli_prepare($conn, 'SELECT id_kategori, nama_kategori, deskripsi FROM kategori WHERE id_kategori = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$kategori = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$kategori) {
    redirect('pages/kategori/index.php?status=tidak_ditemukan');
}

$nama_kategori = $kategori['nama_kategori'];
$deskripsi = $kategori['deskripsi'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kategori = trim($_POST['nama_kategori'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($nama_kategori === '') {
        $error = 'Nama kategori wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, 'UPDATE kategori SET nama_kategori = ?, deskripsi = ? WHERE id_kategori = ?');
        mysqli_stmt_bind_param($stmt, 'ssi', $nama_kategori, $deskripsi, $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirect('pages/kategori/index.php?status=edit');
        }

        $error = 'Kategori gagal diperbarui.';
        mysqli_stmt_close($stmt);
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>
<section class="form-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="form-card neo-panel">
                    <div class="form-heading">
                        <p>Master Data</p>
                        <h1>Edit Kategori</h1>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-warning neo-alert" role="alert"><?= e($error); ?></div>
                    <?php endif; ?>

                    <form action="<?= e(base_url('pages/kategori/edit.php?id=' . $id)); ?>" method="post" data-validate="true" data-confirm-submit="Yakin ingin menyimpan perubahan kategori ini?" novalidate>
                        <div class="mb-3">
                            <label class="form-label" for="nama_kategori">Nama Kategori</label>
                            <input class="form-control" type="text" id="nama_kategori" name="nama_kategori" value="<?= e($nama_kategori); ?>" data-required="true" data-message="Nama kategori wajib diisi.">
                            <span class="inline-error" data-error-for="nama_kategori"></span>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="deskripsi">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5"><?= e($deskripsi); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <a class="btn btn-cancel-neo" href="<?= e(base_url('pages/kategori/index.php')); ?>">Batal</a>
                            <button class="btn btn-success-neo" type="submit">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>