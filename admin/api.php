<?php
header("Content-Type: application/json");

// Deteksi apakah request ini upload file (Multipart) atau request JSON biasa
$isMultipart = strpos($_SERVER["CONTENT_TYPE"] ?? '', 'multipart/form-data') !== false;
$data = $isMultipart ? $_POST : json_decode(file_get_contents("php://input"), true);

// Muat konfigurasi rahasia
require_once __DIR__ . '/../config.php';

// Panggil koneksi database dari root (dipindah ke atas untuk cek password)
require_once __DIR__ . '/../db.php';

// Keamanan Lapis 1: Cek Password Super Admin langsung dari config
if (!isset($data['password']) || $data['password'] !== SUPER_ADMIN_PASS) {
    echo json_encode(["status" => "error", "message" => "Akses Ditolak! Password Salah."]);
    exit;
}

$action = $data['action'] ?? 'get_stats';

// FITUR 1: Upload Media
if ($action === 'upload_media') {
    $targetDir = __DIR__ . "/../assets/images/";
    if (!is_dir($targetDir)) @mkdir($targetDir, 0777, true);

    if (!isset($_FILES["file"]) || $_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
        echo json_encode(["status" => "error", "message" => "Tidak ada file yang dipilih atau terjadi error saat upload."]);
        exit;
    }

    // --- TAMBAHAN KEAMANAN: VALIDASI EKSTENSI & MIME TYPE ---
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    $fileNameOriginal = $_FILES["file"]["name"];
    $fileTmp = $_FILES["file"]["tmp_name"];
    $fileExtension = strtolower(pathinfo($fileNameOriginal, PATHINFO_EXTENSION));
    
    // 1. Cek Ekstensi File
    if (!in_array($fileExtension, $allowedExtensions)) {
        echo json_encode(["status" => "error", "message" => "Format file tidak diizinkan. Hanya JPG, PNG, GIF, dan WEBP."]);
        exit;
    }
    
    // 2. Cek MIME Type menggunakan finfo (Mencegah file PHP disamarkan jadi JPG)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileTmp);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedMimeTypes)) {
        echo json_encode(["status" => "error", "message" => "File terdeteksi berbahaya atau bukan gambar."]);
        exit;
    }

    // 3. Rename File agar aman dari karakter aneh dan eksekusi ilegal
    $fileName = uniqid("img_") . "." . $fileExtension;
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($fileTmp, $targetFilePath)) {
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

// FITUR 4: Ambil Daftar Pengguna
if ($action === 'get_users') {
    try {
        $stmt = $pdo->query("SELECT id, username, nama, whatsapp, domisili, status_akun FROM users ORDER BY id DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "data" => $users]);
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Gagal mengambil data pengguna: " . $e->getMessage()]);
    }
    exit;
}

// FITUR 5: Ubah Role Pengguna
if ($action === 'update_role') {
    $target_username = $data['target_username'] ?? '';
    $new_role = $data['new_role'] ?? '';
    
    $valid_roles = ['free', 'premium', 'tester', 'super_admin'];
    if (!in_array($new_role, $valid_roles) || empty($target_username)) {
        echo json_encode(["status" => "error", "message" => "Data tidak valid atau role tidak diizinkan."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET status_akun = ? WHERE username = ?");
        $stmt->execute([$new_role, $target_username]);
        echo json_encode(["status" => "success", "message" => "Role $target_username berhasil diubah menjadi $new_role."]);
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Gagal mengubah role: " . $e->getMessage()]);
    }
    exit;
}

try {
    
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
    // Tampilkan error detail untuk debugging, hapus atau ubah di versi produksi
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal: " . $e->getMessage()]);
}
?>