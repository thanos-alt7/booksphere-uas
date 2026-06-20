<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$page_title = 'Edit Pengarang';
$current_page = 'pengarang';

$id = (int) ($_GET['id'] ?? 0);
$error = '';

if ($id <= 0) {
    redirect('pages/pengarang/index.php?status=tidak_ditemukan');
}

$stmt = mysqli_prepare($conn, 'SELECT id_pengarang, nama_pengarang, biografi FROM pengarang WHERE id_pengarang = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pengarang = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$pengarang) {
    redirect('pages/pengarang/index.php?status=tidak_ditemukan');
}

$nama_pengarang = $pengarang['nama_pengarang'];
$biografi = $pengarang['biografi'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pengarang = trim($_POST['nama_pengarang'] ?? '');
    $biografi = trim($_POST['biografi'] ?? '');

    if ($nama_pengarang === '') {
        $error = 'Nama pengarang wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, 'UPDATE pengarang SET nama_pengarang = ?, biografi = ? WHERE id_pengarang = ?');
        mysqli_stmt_bind_param($stmt, 'ssi', $nama_pengarang, $biografi, $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirect('pages/pengarang/index.php?status=edit');
        }

        $error = 'Pengarang gagal diperbarui.';
        mysqli_stmt_close($stmt);
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>
<section class="form-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="form-card">
                    <div class="form-heading">
                        <div>
                            <p>Master Data</p>
                            <h1>Edit Pengarang</h1>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-warning neo-alert" role="alert"><?= e($error); ?></div>
                    <?php endif; ?>

                    <form action="<?= e(base_url('pages/pengarang/edit.php?id=' . $id)); ?>" method="post" data-validate="true" data-confirm-submit="Yakin ingin menyimpan perubahan pengarang ini?" novalidate>
                        <div class="mb-3">
                            <label class="form-label" for="nama_pengarang">Nama Pengarang</label>
                            <input class="form-control" type="text" id="nama_pengarang" name="nama_pengarang" value="<?= e($nama_pengarang); ?>" data-required="true" data-message="Nama pengarang wajib diisi.">
                            <span class="inline-error" data-error-for="nama_pengarang"></span>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="biografi">Biografi</label>
                            <textarea class="form-control" id="biografi" name="biografi" rows="5"><?= e($biografi); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <a class="btn btn-cancel-neo" href="<?= e(base_url('pages/pengarang/index.php')); ?>">Batal</a>
                            <button class="btn btn-success-neo" type="submit">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>