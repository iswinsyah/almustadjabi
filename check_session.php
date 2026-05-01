<?php
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['username']) || empty($data['session_token'])) {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
    exit;
}

// Bypass khusus bos (Super Admin)
if ($data['username'] === 'winsyah' && $data['session_token'] === 'SUPER_TOKEN') {
    echo json_encode(["status" => "valid"]);
    exit;
}

$host = "localhost";
$user = "u829486010_amustadjabi";
$password = "Khilafet@1924";
$dbname = "u829486010_almustadjabi";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT session_token FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['session_token'] === $data['session_token']) {
        echo json_encode(["status" => "valid"]);
    } else {
        echo json_encode(["status" => "invalid", "message" => "Sesi berakhir! Akun ini sedang digunakan di perangkat lain."]);
    }
} catch(PDOException $e) {
    // Jika ada error database, biarkan valid agar user tidak terlogout masal
    echo json_encode(["status" => "valid"]);
}
?>