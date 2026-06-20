<?php
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function base_url($path = '')
{
    $root = '/booksphere2';
    return $root . ($path ? '/' . ltrim($path, '/') : '');
}

function format_tanggal($tanggal)
{
    if (!$tanggal) {
        return '-';
    }

    return date('d M Y', strtotime($tanggal));
}

function rupiah($angka)
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

function cover_buku($filename)
{
    if (!$filename) {
        return base_url('assets/img/default-cover.jpg');
    }

    $cover_path = dirname(__DIR__) . '/assets/img/cover/' . $filename;
    if (is_file($cover_path)) {
        return base_url('assets/img/cover/' . $filename);
    }

    $legacy_path = dirname(__DIR__) . '/assets/img/' . $filename;
    if (is_file($legacy_path)) {
        return base_url('assets/img/' . $filename);
    }

    return base_url('assets/img/default-cover.jpg');
}

function upload_cover($file)
{
    if (!isset($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'filename' => null, 'error' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'filename' => null, 'error' => 'Upload gambar gagal.'];
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        return ['success' => false, 'filename' => null, 'error' => 'Ukuran gambar maksimal 2 MB.'];
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return ['success' => false, 'filename' => null, 'error' => 'Format gambar harus JPG, PNG, atau WEBP.'];
    }

    $upload_dir = dirname(__DIR__) . '/assets/img/cover';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $filename = 'cover_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $target = $upload_dir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['success' => false, 'filename' => null, 'error' => 'Gambar gagal disimpan.'];
    }

    return ['success' => true, 'filename' => $filename, 'error' => null];
}

function redirect($path)
{
    header('Location: ' . base_url($path));
    exit;
}