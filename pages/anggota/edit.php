<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$page_title = 'Edit Anggota';
$current_page = 'anggota';

$id = (int) ($_GET['id'] ?? 0);
$error = '';

if ($id <= 0) {
    redirect('pages/anggota/index.php?status=tidak_ditemukan');
}

$stmt = mysqli_prepare($conn, 'SELECT id_anggota, nama_anggota, no_identitas, no_telepon, alamat FROM anggota WHERE id_anggota = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$anggota = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$anggota) {
    redirect('pages/anggota/index.php?status=tidak_ditemukan');
}

$nama_anggota = $anggota['nama_anggota'];
$no_identitas = $anggota['no_identitas'];
$no_telepon = $anggota['no_telepon'];
$alamat = $anggota['alamat'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_anggota = trim($_POST['nama_anggota'] ?? '');
    $no_identitas = trim($_POST['no_identitas'] ?? '');
    $no_telepon = trim($_POST['no_telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if ($nama_anggota === '' || $no_identitas === '') {
        $error = 'Nama anggota dan nomor identitas wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, 'UPDATE anggota SET nama_anggota = ?, no_identitas = ?, no_telepon = ?, alamat = ? WHERE id_anggota = ?');
        mysqli_stmt_bind_param($stmt, 'ssssi', $nama_anggota, $no_identitas, $no_telepon, $alamat, $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirect('pages/anggota/index.php?status=edit');
        }

        $error = 'Data anggota gagal diperbarui. Nomor identitas mungkin sudah digunakan.';
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
                            <h1>Edit Anggota</h1>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-warning neo-alert" role="alert"><?= e($error); ?></div>
                    <?php endif; ?>

                    <form action="<?= e(base_url('pages/anggota/edit.php?id=' . $id)); ?>" method="post" data-validate="true" data-confirm-submit="Yakin ingin menyimpan perubahan anggota ini?" novalidate>
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
                            <button class="btn btn-success-neo" type="submit">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>