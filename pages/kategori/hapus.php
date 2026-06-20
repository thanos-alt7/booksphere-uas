<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/config.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    redirect('pages/kategori/index.php?status=tidak_ditemukan');
}

$stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS total FROM buku WHERE id_kategori = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$jumlah_buku = (int) mysqli_fetch_assoc($result)['total'];
mysqli_stmt_close($stmt);

if ($jumlah_buku > 0) {
    redirect('pages/kategori/index.php?status=gagal_dipakai');
}

$stmt = mysqli_prepare($conn, 'DELETE FROM kategori WHERE id_kategori = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$affected = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

if ($affected < 1) {
    redirect('pages/kategori/index.php?status=tidak_ditemukan');
}

redirect('pages/kategori/index.php?status=hapus');