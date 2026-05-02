<?php
header("Content-Type: application/json");

// Panggil koneksi database tersentralisasi
require_once __DIR__ . '/db.php';

// Ambil data yang dikirim oleh Javascript
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty(trim($data['username'] ?? '')) || empty(trim($data['password'] ?? ''))) {
    echo json_encode(["status" => "error", "message" => "Semua kolom wajib diisi dengan benar!"]);
    exit;
}

// Enkripsi password demi keamanan
$hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (nama, gender, tanggal_lahir, email, whatsapp, domisili, username, password, status_akun) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['nama'],
        $data['gender'],
        $data['tanggal_lahir'],
        $data['email'],
        $data['whatsapp'],
        $data['domisili'],
        $data['username'],
        $hashed_password,
        'free'
    ]);
    
    echo json_encode(["status" => "success", "message" => "Pendaftaran berhasil"]);
} catch(PDOException $e) {
    // Error kode 23000 berarti ada data unik (Username/Email) yang duplikat (sudah dipakai)
    if ($e->getCode() == 23000) {
        echo json_encode(["status" => "error", "message" => "Username atau Email sudah terdaftar!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menyimpan data"]);
    }
}
?>