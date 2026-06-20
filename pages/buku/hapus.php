<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('pages/buku/index.php?status=tidak_ditemukan');
}

$stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM detail_peminjaman WHERE id_buku = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_peminjaman = (int) mysqli_fetch_assoc($result)['total'];
mysqli_stmt_close($stmt);

if ($total_peminjaman > 0) {
    redirect('pages/buku/index.php?status=gagal_dipakai');
}

mysqli_begin_transaction($conn);
try {
    $stmt = mysqli_prepare($conn, 'DELETE FROM buku_pengarang WHERE id_buku = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, 'DELETE FROM buku WHERE id_buku = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    mysqli_commit($conn);

    if ($affected < 1) {
        redirect('pages/buku/index.php?status=tidak_ditemukan');
    }

    redirect('pages/buku/index.php?status=hapus');
} catch (Throwable $e) {
    mysqli_rollback($conn);
    redirect('pages/buku/index.php?status=gagal_dipakai');
}