<?php
// Konfigurasi Database Tersentralisasi Hostinger
$host = "localhost";
$user = "u829486010_amustadjabi";
$password = "Khilafet@1924";
$dbname = "u829486010_almustadjabi";

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