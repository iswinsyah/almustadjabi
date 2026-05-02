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
            if (count($parts) >= 3) {
                array_shift($parts); // Hapus 'SDKH' di awal
                array_pop($parts);   // Hapus Timestamp di akhir
                $username = implode('-', $parts); // Gabungkan kembali jika username punya tanda strip
                
                // --- KONEKSI DATABASE ---
                require_once __DIR__ . '/db.php';
                
                try {
                    $stmt = $pdo->prepare("UPDATE users SET status_akun = 'premium' WHERE username = ?");
                    $stmt->execute([$username]);
                } catch(PDOException $e) {
                    error_log("Duitku Callback DB Error: " . $e->getMessage());
                }
            }
        }
    }
}
echo "OK"; // Wajib membalas "OK" agar sistem Duitku tahu notifikasinya sudah diterima
?>