<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'perpustakaan_db';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');
