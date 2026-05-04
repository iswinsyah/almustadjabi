<?php
header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['username']) || empty($data['session_token']) || empty($data['jilid'])) {
    echo json_encode(["status" => "error", "message" => "Akses Ditolak. Data tidak lengkap."]);
    exit;
}

$jilid = (int)$data['jilid'];

// --- MAPPING URL RAHASIA GOOGLE SCRIPT ---
// Sekarang url tidak akan bocor ke publik karena disembunyikan di dalam PHP server
$kurikulum_gas = [
    1 => "https://script.google.com/macros/s/AKfycbxSUFw79hAluE84LK6kToEyBkDuW0BLSS8gWcofSfagi-cQYH9j2_jeWk7LUFSz4OF8/exec",
    2 => "https://script.google.com/macros/s/AKfycbwIVgae6B5evRTQovqt5FZXayjbpTDSwWTXRZpSf3TWGpdfq_SUdnGCUcfOweoieahH/exec",
    3 => "https://script.google.com/macros/s/AKfycbwJMzHqLjnaKKEo2Z-oZwRQa6iqfnokRN7QttjpW87v8rvHoMdmxS5c0iq8O-TL0WY/exec",
    4 => "https://script.google.com/macros/s/AKfycbylkuitkS3rD_Z_C2aX_c-ZGoDVJu1Nube_1-8T0znc2veVkcujqKJYAlVq7gPKdco/exec",
    5 => "https://script.google.com/macros/s/AKfycbxGU7L45qfp-xNXV-M6uqI8t1S0P81qT5fymbCX6tv1zj_Cvry-3XY1sUURBN9MB_U/exec",
    6 => "https://script.google.com/macros/s/AKfycbzkaPd1wxezWDzT7SLW4vdudTCA4uxnMFkmZam7HOWGZYC_c0hq_f0xQ9-xdvN5jEU7/exec"
];

if (!isset($kurikulum_gas[$jilid])) {
    echo json_encode(["status" => "error", "message" => "Materi jilid tidak ditemukan."]);
    exit;
}

require_once __DIR__ . '/db.php';
$status_akun = 'free';

// --- VERIFIKASI SESI DAN ROLE DATABASE ---
if ($data['session_token'] === 'SUPER_TOKEN') {
    $status_akun = 'super_admin';
} else {
    try {
        $stmt = $pdo->prepare("SELECT session_token, status_akun FROM users WHERE username = ?");
        $stmt->execute([$data['username']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || $row['session_token'] !== $data['session_token']) {
            echo json_encode(["status" => "invalid", "message" => "Sesi Anda tidak valid atau telah berakhir di perangkat lain."]);
            exit;
        }
        $status_akun = strtolower(trim($row['status_akun']));
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Koneksi database terganggu."]);
        exit;
    }
}

// Normalisasi Typo Role
if (in_array($status_akun, ['admin', 'super admin', 'administrator'])) {
    $status_akun = 'super_admin';
}

// --- BLOKIR BACKEND UNTUK USER FREE (Jilid 2-6) ---
if ($jilid > 1 && $status_akun !== 'premium' && $status_akun !== 'super_admin') {
    echo json_encode(["status" => "locked", "message" => "Akses ditolak permanen. Jilid ini khusus pengguna Premium."]);
    exit;
}

// --- AMBIL DATA DARI GOOGLE SCRIPT ---
$ch = curl_init($kurikulum_gas[$jilid]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
curl_close($ch);

// Serahkan materi langsung ke Frontend
echo $response;
?>