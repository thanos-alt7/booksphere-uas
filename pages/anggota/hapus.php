<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('pages/anggota/index.php?status=tidak_ditemukan');
}

$stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM peminjaman WHERE id_anggota = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_peminjaman = (int) mysqli_fetch_assoc($result)['total'];
mysqli_stmt_close($stmt);

if ($total_peminjaman > 0) {
    redirect('pages/anggota/index.php?status=gagal_dipakai');
}

$stmt = mysqli_prepare($conn, 'DELETE FROM anggota WHERE id_anggota = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$affected = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

if ($affected < 1) {
    redirect('pages/anggota/index.php?status=tidak_ditemukan');
}

redirect('pages/anggota/index.php?status=hapus');