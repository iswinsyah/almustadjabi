<?php
header("Content-Type: application/json");

// Deteksi apakah request ini upload file (Multipart) atau request JSON biasa
$isMultipart = strpos($_SERVER["CONTENT_TYPE"] ?? '', 'multipart/form-data') !== false;
$data = $isMultipart ? $_POST : json_decode(file_get_contents("php://input"), true);

// Keamanan Lapis 1: Cek Password Super Admin
if (!isset($data['password']) || $data['password'] !== 'Khilafet@1924') {
    echo json_encode(["status" => "error", "message" => "Akses Ditolak! Password Salah."]);
    exit;
}

$action = $data['action'] ?? 'get_stats';

// FITUR 1: Upload Media
if ($action === 'upload_media') {
    $targetDir = __DIR__ . "/../assets/images/";
    if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);

    if (!isset($_FILES["file"])) {
        echo json_encode(["status" => "error", "message" => "Tidak ada file yang dipilih."]);
        exit;
    }

    $fileName = str_replace(" ", "_", basename($_FILES["file"]["name"]));
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
        echo json_encode(["status" => "success", "message" => "Media berhasil diunggah!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menyimpan file di server. Pastikan folder assets/images memiliki izin tulis."]);
    }
    exit;
}

// FITUR 2: Ambil Daftar Media
if ($action === 'get_media') {
    $targetDir = __DIR__ . "/../assets/images/";
    $fileList = [];
    if (is_dir($targetDir)) {
        $files = array_diff(scandir($targetDir), array('..', '.', '.gitkeep'));
        foreach($files as $f) { $fileList[] = "assets/images/" . $f; }
    }
    echo json_encode(["status" => "success", "data" => array_values($fileList)]);
    exit;
}

// FITUR 3: Simpan Pengaturan
if ($action === 'save_settings') {
    $settingsFile = __DIR__ . "/../assets/settings.json";
    file_put_contents($settingsFile, json_encode(["judul" => $data['judul'] ?? '', "pengumuman" => $data['pengumuman'] ?? '']));
    echo json_encode(["status" => "success", "message" => "Pengaturan halaman depan berhasil disimpan!"]);
    exit;
}

// Konfigurasi Database Hostinger (Ganti dengan milik bos)
$host = "localhost";
$user = "u829486010_amustadjabi";
$password = "Khilafet@1924";
$dbname = "u829486010_almustadjabi";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. Dapatkan Total User
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $total_users = $stmt->fetch()['total'];

    // 2. Dapatkan Total User Laki-laki
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE gender = 'L'");
    $total_l = $stmt->fetch()['total'];

    // 3. Dapatkan Total User Perempuan
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE gender = 'P'");
    $total_p = $stmt->fetch()['total'];

    // 4. Dapatkan Sebaran Domisili
    $stmt = $pdo->query("SELECT domisili, COUNT(*) as jumlah FROM users GROUP BY domisili ORDER BY jumlah DESC");
    $domisili = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Data Perolehan Wakaf (Sementara menggunakan Dummy Data)
    $total_wakaf = 250000000;

    // Kirim balasan ke Dashboard
    echo json_encode([
        "status" => "success",
        "data" => [
            "total_users" => $total_users,
            "total_l" => $total_l,
            "total_p" => $total_p,
            "domisili" => $domisili,
            "total_wakaf" => $total_wakaf
        ]
    ]);

} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal."]);
}
?>