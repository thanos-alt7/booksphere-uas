<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$page_title = 'Tambah Pengarang';
$current_page = 'pengarang';

$nama_pengarang = '';
$biografi = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pengarang = trim($_POST['nama_pengarang'] ?? '');
    $biografi = trim($_POST['biografi'] ?? '');

    if ($nama_pengarang === '') {
        $error = 'Nama pengarang wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, 'INSERT INTO pengarang (nama_pengarang, biografi) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ss', $nama_pengarang, $biografi);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirect('pages/pengarang/index.php?status=tambah');
        }

        $error = 'Pengarang gagal ditambahkan.';
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
                            <h1>Tambah Pengarang</h1>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-warning neo-alert" role="alert"><?= e($error); ?></div>
                    <?php endif; ?>

                    <form action="<?= e(base_url('pages/pengarang/tambah.php')); ?>" method="post" data-validate="true" novalidate>
                        <div class="mb-3">
                            <label class="form-label" for="nama_pengarang">Nama Pengarang</label>
                            <input class="form-control" type="text" id="nama_pengarang" name="nama_pengarang" value="<?= e($nama_pengarang); ?>" data-required="true" data-message="Nama pengarang wajib diisi.">
                            <span class="inline-error" data-error-for="nama_pengarang"></span>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="biografi">Biografi</label>
                            <textarea class="form-control" id="biografi" name="biografi" rows="5" placeholder="Tuliskan biografi singkat pengarang."><?= e($biografi); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <a class="btn btn-cancel-neo" href="<?= e(base_url('pages/pengarang/index.php')); ?>">Batal</a>
                            <button class="btn btn-success-neo" type="submit">Simpan Pengarang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>