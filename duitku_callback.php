<?php
// --- KONFIGURASI DUITKU (HARUS SAMA DENGAN FILE REQUEST) ---
$merchantCode = "KODE_MERCHANT_DUITKU_BOS"; 
$apiKey = "API_KEY_DUITKU_BOS";

$amount = isset($_POST['amount']) ? $_POST['amount'] : null;
$merchantOrderId = isset($_POST['merchantOrderId']) ? $_POST['merchantOrderId'] : null;
$signature = isset($_POST['signature']) ? $_POST['signature'] : null;
$resultCode = isset($_POST['resultCode']) ? $_POST['resultCode'] : null;

if (!empty($amount) && !empty($merchantOrderId) && !empty($signature)) {
    $calcSignature = md5($merchantCode . $amount . $merchantOrderId . $apiKey);
    
    // Validasi Keaslian Notifikasi dari Duitku
    if ($signature == $calcSignature) {
        if ($resultCode == "00") { // Kode '00' artinya Pembayaran Berhasil
            
            // Ambil username dari Order ID (Format kita tadi: SDKH-Username-Waktu)
            $parts = explode('-', $merchantOrderId);
            if (count($parts) >= 2) {
                $username = $parts[1];
                
                // --- KONEKSI DATABASE HOSTINGER ---
                $host = "localhost";
                $user = "u829486010_amustadjabi";
                $password = "Khilafet@1924";
                $dbname = "u829486010_almustadjabi";
                
                try {
                    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $stmt = $pdo->prepare("UPDATE users SET status_akun = 'premium' WHERE username = ?");
                    $stmt->execute([$username]);
                } catch(PDOException $e) {}
            }
        }
    }
}
echo "OK"; // Wajib membalas "OK" agar sistem Duitku tahu notifikasinya sudah diterima
?>