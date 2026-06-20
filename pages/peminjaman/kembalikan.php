<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$id_detail = (int) ($_GET['id_detail'] ?? 0);

if ($id_detail <= 0) {
    redirect('pages/peminjaman/index.php?status_msg=tidak_ditemukan');
}

$stmt = mysqli_prepare($conn, "SELECT
        dp.id_detail,
        dp.id_peminjaman,
        dp.tanggal_kembali,
        pm.tanggal_jatuh_tempo
    FROM detail_peminjaman dp
    JOIN peminjaman pm ON dp.id_peminjaman = pm.id_peminjaman
    WHERE dp.id_detail = ?
    LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id_detail);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$detail = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$detail) {
    redirect('pages/peminjaman/index.php?status_msg=tidak_ditemukan');
}

$id_peminjaman = (int) $detail['id_peminjaman'];

if ($detail['tanggal_kembali']) {
    redirect('pages/peminjaman/detail.php?id=' . $id_peminjaman);
}

$tanggal_kembali = date('Y-m-d');

mysqli_begin_transaction($conn);
try {
    $stmt = mysqli_prepare($conn, 'SELECT fn_hitung_denda(?, ?) AS denda');
    mysqli_stmt_bind_param($stmt, 'ss', $detail['tanggal_jatuh_tempo'], $tanggal_kembali);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $denda = (float) mysqli_fetch_assoc($result)['denda'];
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, 'UPDATE detail_peminjaman SET tanggal_kembali = ?, denda = ? WHERE id_detail = ? AND tanggal_kembali IS NULL');
    mysqli_stmt_bind_param($stmt, 'sdi', $tanggal_kembali, $denda, $id_detail);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM detail_peminjaman WHERE id_peminjaman = ? AND tanggal_kembali IS NULL');
    mysqli_stmt_bind_param($stmt, 'i', $id_peminjaman);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $belum_kembali = (int) mysqli_fetch_assoc($result)['total'];
    mysqli_stmt_close($stmt);

    if ($belum_kembali === 0) {
        $stmt = mysqli_prepare($conn, 'UPDATE peminjaman SET status = "selesai" WHERE id_peminjaman = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id_peminjaman);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    mysqli_commit($conn);
    redirect('pages/peminjaman/detail.php?id=' . $id_peminjaman . '&status_msg=kembali');
} catch (Throwable $e) {
    mysqli_rollback($conn);
    redirect('pages/peminjaman/detail.php?id=' . $id_peminjaman . '&status_msg=gagal');
}