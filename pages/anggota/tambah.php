<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$page_title = 'Tambah Anggota';
$current_page = 'anggota';

$nama_anggota = '';
$no_identitas = '';
$no_telepon = '';
$alamat = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_anggota = trim($_POST['nama_anggota'] ?? '');
    $no_identitas = trim($_POST['no_identitas'] ?? '');
    $no_telepon = trim($_POST['no_telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if ($nama_anggota === '' || $no_identitas === '') {
        $error = 'Nama anggota dan nomor identitas wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, 'INSERT INTO anggota (nama_anggota, no_identitas, no_telepon, alamat) VALUES (?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'ssss', $nama_anggota, $no_identitas, $no_telepon, $alamat);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirect('pages/anggota/index.php?status=tambah');
        }

        $error = 'Anggota gagal ditambahkan. Nomor identitas mungkin sudah digunakan.';
        mysqli_stmt_close($stmt);
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>
<section class="form-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <div class="form-heading">
                        <div>
                            <p>Master Data</p>
                            <h1>Tambah Anggota</h1>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-warning neo-alert" role="alert"><?= e($error); ?></div>
                    <?php endif; ?>

                    <form action="<?= e(base_url('pages/anggota/tambah.php')); ?>" method="post" data-validate="true" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="nama_anggota">Nama Anggota</label>
                                <input class="form-control" type="text" id="nama_anggota" name="nama_anggota" value="<?= e($nama_anggota); ?>" data-required="true" data-message="Nama anggota wajib diisi.">
                                <span class="inline-error" data-error-for="nama_anggota"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="no_identitas">Nomor Identitas</label>
                                <input class="form-control" type="text" id="no_identitas" name="no_identitas" value="<?= e($no_identitas); ?>" data-required="true" data-message="Nomor identitas wajib diisi.">
                                <span class="inline-error" data-error-for="no_identitas"></span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="no_telepon">Nomor Telepon</label>
                                <input class="form-control" type="text" id="no_telepon" name="no_telepon" value="<?= e($no_telepon); ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="alamat">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="4"><?= e($alamat); ?></textarea>
                            </div>
                        </div>

                        <div class="form-actions mt-4">
                            <a class="btn btn-cancel-neo" href="<?= e(base_url('pages/anggota/index.php')); ?>">Batal</a>
                            <button class="btn btn-success-neo" type="submit">Simpan Anggota</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>