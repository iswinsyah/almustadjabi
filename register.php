<?php
header("Content-Type: application/json");

// Konfigurasi Database Hostinger (Ganti dengan kredensial asli milik bos)
$host = "localhost";
$user = "u123456789_user";      // GANTI INI
$password = "PasswordDbAnda!";  // GANTI INI
$dbname = "u123456789_db";      // GANTI INI

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal"]);
    exit;
}

// Ambil data yang dikirim oleh Javascript
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Data tidak valid"]);
    exit;
}

// Enkripsi password demi keamanan
$hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (nama, gender, tanggal_lahir, email, whatsapp, domisili, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['nama'],
        $data['gender'],
        $data['tanggal_lahir'],
        $data['email'],
        $data['whatsapp'],
        $data['domisili'],
        $data['username'],
        $hashed_password
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