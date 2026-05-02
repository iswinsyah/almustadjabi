<?php
// Cek apakah file config.php ada dan namanya benar
if (!file_exists(__DIR__ . '/config.php')) {
    if (!headers_sent()) header("Content-Type: application/json");
    echo json_encode(["status" => "error", "message" => "SISTEM ERROR: File config.php tidak ditemukan! Pastikan namanya huruf kecil semua (config.php)."]);
    exit;
}

// Muat konfigurasi rahasia
require_once __DIR__ . '/config.php';

// Konfigurasi Database Tersentralisasi Hostinger
$host = DB_HOST;
$user = DB_USER;
$password = DB_PASS;
$dbname = DB_NAME;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    if (!headers_sent()) {
        header("Content-Type: application/json");
    }
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal: " . $e->getMessage()]);
    exit;
}
?>