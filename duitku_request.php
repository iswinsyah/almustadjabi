<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['username']) || empty($data['nominal'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

$nominal = (int)$data['nominal'];
if ($nominal < 100000) { // Batas minimal transaksi Duitku
    echo json_encode(['status' => 'error', 'message' => 'Berikan Infaq terbaik minimal Rp 100.000 berlaku seumur hidup. Dana Infaq akan dialokasikan untuk biaya operasional aplikasi dan pembangunan pesantren tahfidz Villa Quran di Malang.']);
    exit;
}

// --- KONFIGURASI DUITKU BOS (GANTI DENGAN MILIK BOS) ---
$merchantCode = "DS30340"; 
$apiKey = "32fec46926469cc28b13b4986308e770";
$isSandbox = true; // Ubah ke 'false' jika nanti mau diaktifkan ke Production (Live)

// Persiapkan data transaksi
$merchantOrderId = "SDKH-" . $data['username'] . "-" . time(); // Kita sisipkan username di dalam Order ID
$productDetails = "Sedekah & Buka Akses Premium Qiroatul Kutub";
$email = "santri@villaquranindonesia.com"; // Email dummy/default
$phoneNumber = "081234567890";
$customerVaName = $data['username'];
$callbackUrl = "https://almustadjabi.villaquranindonesia.com/duitku_callback.php";
$returnUrl = "https://almustadjabi.villaquranindonesia.com/menu.html";

$signature = md5($merchantCode . $merchantOrderId . $nominal . $apiKey);

$params = array(
    'merchantCode' => $merchantCode,
    'paymentAmount' => $nominal,
    'merchantOrderId' => $merchantOrderId,
    'productDetails' => $productDetails,
    'email' => $email,
    'phoneNumber' => $phoneNumber,
    'customerVaName' => $customerVaName,
    'callbackUrl' => $callbackUrl,
    'returnUrl' => $returnUrl,
    'signature' => $signature,
    'expiryPeriod' => 1440 // Tagihan kadaluarsa dalam 24 jam
);

$url = $isSandbox ? 'https://api-sandbox.duitku.com/api/merchant/v2/inquiry' : 'https://api-prod.duitku.com/api/merchant/v2/inquiry';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen(json_encode($params))));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); $responseStr = curl_exec($ch); curl_close($ch);

$response = json_decode($responseStr, true);
if (isset($response['statusCode']) && $response['statusCode'] == '00') {
    echo json_encode(['status' => 'success', 'paymentUrl' => $response['paymentUrl']]);
} else {
    echo json_encode(['status' => 'error', 'message' => $response['statusMessage'] ?? 'Sistem Duitku sedang gangguan']);
}
?>