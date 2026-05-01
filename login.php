<?php
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['username']) || empty($data['password'])) {
    echo json_encode(["status" => "error", "message" => "Username dan password wajib diisi"]);
    exit;
}

// --- JALUR VVIP KHUSUS SUPER ADMIN ---
if ($data['username'] === 'winsyah' && $data['password'] === 'Khilafet@1924') {
    // Langsung tembus tanpa cek database Hostinger
    echo json_encode(["status" => "success", "message" => "Selamat datang, Super Admin!"]);
    exit;
}

// Konfigurasi Database Hostinger (Harus sama persis dengan yang di register.php)
$host = "localhost";
$user = "u829486010_amustadjabi";
$password = "Khilafet@1924";
$dbname = "u829486010_almustadjabi";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Tampilkan error detail untuk debugging
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal: " . $e->getMessage()]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$data['username']]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

if ($userRow && password_verify($data['password'], $userRow['password'])) {
    $status_akun = isset($userRow['status_akun']) ? $userRow['status_akun'] : 'free';
    echo json_encode(["status" => "success", "message" => "Login berhasil", "status_akun" => $status_akun]);
} else {
    echo json_encode(["status" => "error", "message" => "Username atau password salah!"]);
}
?>