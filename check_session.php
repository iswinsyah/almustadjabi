<?php
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['username']) || empty($data['session_token'])) {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
    exit;
}

// Bypass cek session khusus role Super Admin (karena memegang SUPER_TOKEN)
if ($data['session_token'] === 'SUPER_TOKEN') {
    echo json_encode(["status" => "valid"]);
    exit;
}

// Panggil koneksi database tersentralisasi
require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->prepare("SELECT session_token FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['session_token'] === $data['session_token']) {
        echo json_encode(["status" => "valid"]);
    } else {
        echo json_encode(["status" => "invalid", "message" => "Sesi berakhir! Akun ini sedang digunakan di perangkat lain."]);
    }
} catch(PDOException $e) {
    // Perbaikan Silent Failure: Laporkan error jika server database tumbang
    echo json_encode(["status" => "error", "message" => "Koneksi database terputus. " . $e->getMessage()]);
}
?>