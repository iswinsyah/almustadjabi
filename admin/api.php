<?php
header("Content-Type: application/json");

// Deteksi apakah request ini upload file (Multipart) atau request JSON biasa
$isMultipart = strpos($_SERVER["CONTENT_TYPE"] ?? '', 'multipart/form-data') !== false;
$data = $isMultipart ? $_POST : json_decode(file_get_contents("php://input"), true);

// Cek apakah file config.php ada dan namanya benar (huruf kecil)
if (!file_exists(__DIR__ . '/../config.php')) {
    echo json_encode(["status" => "error", "message" => "SISTEM ERROR: File config.php tidak ditemukan! Pastikan Anda sudah mengunggahnya ke Hostinger dan pastikan namanya menggunakan huruf kecil semua (config.php)."]);
    exit;
}

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
        // --- FITUR SELF-HEALING DATABASE ---
        // Otomatis menambahkan kolom baru jika tabel users masih versi lama
        $kolom_baru = [
            "nama VARCHAR(100) NULL",
            "gender ENUM('L','P') NULL",
            "tanggal_lahir DATE NULL",
            "email VARCHAR(100) NULL",
            "whatsapp VARCHAR(20) NULL",
            "domisili VARCHAR(100) NULL",
            "status_akun VARCHAR(20) DEFAULT 'free'",
            "session_token VARCHAR(255) NULL"
        ];
        foreach ($kolom_baru as $kolom) {
            try { $pdo->exec("ALTER TABLE users ADD COLUMN $kolom"); } 
            catch (PDOException $e) { /* Abaikan jika kolom sudah ada */ }
        }

        $stmt = $pdo->query("SELECT id, username, nama, whatsapp, domisili, status_akun FROM users ORDER BY id DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Rapikan data null menjadi tanda strip agar enak dilihat di tabel
        foreach($users as &$u) {
            $u['nama'] = $u['nama'] ?: '-';
            $u['whatsapp'] = $u['whatsapp'] ?: '-';
            $u['status_akun'] = $u['status_akun'] ?: 'free';
        }

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
    
    $valid_roles = ['free', 'premium', 'super_admin'];
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

// FITUR 6: Manajemen Kosakata (Bank Kosakata)
if ($action === 'get_kosakata') {
    try {
        // Self-healing: Buat tabel bank_kosakata secara otomatis jika belum ada
        $pdo->exec("CREATE TABLE IF NOT EXISTS bank_kosakata (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kata_penuh VARCHAR(100) DEFAULT '',
            kata_sebagian VARCHAR(100) DEFAULT '',
            kata_gundul VARCHAR(100) DEFAULT '',
            arti VARCHAR(200) DEFAULT ''
        )");
        
        // Upgrade struktur tabel jika berasal dari versi lama
        try { $pdo->exec("ALTER TABLE bank_kosakata ADD COLUMN kata_penuh VARCHAR(100) DEFAULT ''"); } catch (PDOException $e) {}
        try { $pdo->exec("ALTER TABLE bank_kosakata ADD COLUMN kata_sebagian VARCHAR(100) DEFAULT ''"); } catch (PDOException $e) {}
        try { $pdo->exec("ALTER TABLE bank_kosakata ADD COLUMN kata_gundul VARCHAR(100) DEFAULT ''"); } catch (PDOException $e) {}
        // Migrasi data lama (jika ada) ke kata_penuh
        try { $pdo->exec("UPDATE bank_kosakata SET kata_penuh = kata_arab WHERE kata_arab IS NOT NULL AND kata_penuh = ''"); } catch (PDOException $e) {}
        // Bersihkan kolom lama agar tidak menyebabkan error saat input baru
        try { $pdo->exec("ALTER TABLE bank_kosakata DROP COLUMN kata_arab"); } catch (PDOException $e) {}
        try { $pdo->exec("ALTER TABLE bank_kosakata DROP COLUMN jenis_kata"); } catch (PDOException $e) {}
        try { $pdo->exec("ALTER TABLE bank_kosakata DROP COLUMN jilid_minimal"); } catch (PDOException $e) {}

        $stmt = $pdo->query("SELECT id, kata_penuh, kata_sebagian, kata_gundul, arti FROM bank_kosakata ORDER BY id DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "data" => $data]);
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

if ($action === 'save_kosakata') {
    $id = $data['id'] ?? null;
    $kata_penuh = $data['kata_penuh'] ?? '';
    $kata_sebagian = $data['kata_sebagian'] ?? '';
    $kata_gundul = $data['kata_gundul'] ?? '';
    $arti = $data['arti'] ?? '';

    if (empty($kata_penuh) || empty($arti)) {
        echo json_encode(["status" => "error", "message" => "Kata Penuh dan Arti wajib diisi."]);
        exit;
    }

    try {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE bank_kosakata SET kata_penuh=?, kata_sebagian=?, kata_gundul=?, arti=? WHERE id=?");
            $stmt->execute([$kata_penuh, $kata_sebagian, $kata_gundul, $arti, $id]);
            $msg = "Kosakata berhasil diperbarui!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO bank_kosakata (kata_penuh, kata_sebagian, kata_gundul, arti) VALUES (?, ?, ?, ?)");
            $stmt->execute([$kata_penuh, $kata_sebagian, $kata_gundul, $arti]);
            $msg = "Kosakata berhasil ditambahkan!";
        }
        echo json_encode(["status" => "success", "message" => $msg]);
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

if ($action === 'save_bulk_kosakata') {
    $rows = $data['rows'] ?? [];
    if (empty($rows)) { echo json_encode(["status" => "error", "message" => "Data kosong atau format salah."]); exit; }
    try {
        $stmt = $pdo->prepare("INSERT INTO bank_kosakata (kata_penuh, kata_sebagian, kata_gundul, arti) VALUES (?, ?, ?, ?)");
        $count = 0;
        foreach ($rows as $r) {
            if (!empty($r[0]) && !empty($r[3])) { $stmt->execute([$r[0], $r[1], $r[2], $r[3]]); $count++; }
        }
        echo json_encode(["status" => "success", "message" => "$count Kosakata berhasil diimpor dari Excel!"]);
    } catch(PDOException $e) { echo json_encode(["status" => "error", "message" => "Gagal impor: " . $e->getMessage()]); }
    exit;
}

if ($action === 'delete_kosakata') {
    $id = $data['id'] ?? null;
    try {
        $stmt = $pdo->prepare("DELETE FROM bank_kosakata WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(["status" => "success", "message" => "Kosakata berhasil dihapus!"]);
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
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