<?php
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['username']) || empty($data['password'])) {
    echo json_encode(["status" => "error", "message" => "Username dan password wajib diisi"]);
    exit;
}

// Bersihkan username dari huruf besar otomatis di HP atau spasi nyangkut
$clean_username = strtolower(trim($data['username']));

// --- JALUR VVIP KHUSUS SUPER ADMIN ---
if ($clean_username === 'winsyah' && trim($data['password']) === 'Khilafet@1924') {
    // Langsung tembus tanpa cek database Hostinger
    echo json_encode(["status" => "success", "message" => "Selamat datang, Super Admin!", "status_akun" => "premium", "session_token" => "SUPER_TOKEN"]);
    exit;
}

// Panggil koneksi database tersentralisasi
require_once __DIR__ . '/db.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$clean_username]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

if ($userRow && password_verify($data['password'], $userRow['password'])) {
    $status_akun = isset($userRow['status_akun']) ? $userRow['status_akun'] : 'free';
    
    // Generate Token Unik untuk perangkat ini
    $session_token = bin2hex(random_bytes(16));
    
    try {
        $updateStmt = $pdo->prepare("UPDATE users SET session_token = ? WHERE id = ?");
        $updateStmt->execute([$session_token, $userRow['id']]);
    } catch(PDOException $e) {
        // Abaikan jika kolom database belum dibuat oleh bos
    }

    // Hak akses premium menyeluruh otomatis untuk akun Super Admin
        if ($clean_username === 'winsyah') {
        $status_akun = 'premium';
    }
    
    echo json_encode(["status" => "success", "message" => "Login berhasil", "status_akun" => $status_akun, "session_token" => $session_token]);
} else {
    echo json_encode(["status" => "error", "message" => "Username atau password salah!"]);
}
?>