<?php
header('Content-Type: application/json');

// --- SETTING URL GOOGLE APPS SCRIPT ---
// Masukkan URL Web App GAS penerima audio yang baru akan Bos buat
define('GAS_AI_URL', 'https://script.google.com/macros/s/AKfycbxlBuoRWpQJ3k1mLWSuJzrBnHHcvEodgmlgcM4WRKqt7SFfmYfIyrTWGqkxg7__cSpo/exec');

if (!isset($_FILES['audio']) || !isset($_POST['target_text'])) {
    echo json_encode(["status" => "error", "pesan" => "Audio atau teks target tidak ditemukan."]);
    exit;
}

$target_text = $_POST['target_text'];
$audio_tmp_path = $_FILES['audio']['tmp_name'];

// --- 1. PROSES AUDIO KE BASE64 (TANPA DISIMPAN) ---
// File suara ada di "TMP/Memory" RAM Server Hostinger, 
// Setelah script ini mati/selesai, server otomatis melenyapkannya.
$audio_data = file_get_contents($audio_tmp_path);
$base64_audio = base64_encode($audio_data);

if (GAS_AI_URL === 'MASUKKAN_URL_GAS_DISINI') {
    sleep(2); // Simulasi mikir jika API key belum diganti
    echo json_encode([
        "status" => "salah", 
        "pesan" => "URL GAS belum diatur di ai_koreksi.php. Ini adalah simulasi dari sistem! Teks target: $target_text"
    ]);
    exit;
}

// --- 2. KIRIM AUDIO & TEKS KE GOOGLE APPS SCRIPT ---
$payload = [
    "target_text" => $target_text,
    "audio_base64" => $base64_audio,
    "mime_type" => "audio/webm"
];

$ch = curl_init(GAS_AI_URL);
curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Google Script butuh Follow Location (Redirect 302) untuk merespon POST
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
$response = curl_exec($ch); curl_close($ch);

// --- 3. KEMBALIKAN JAWABAN DARI GAS KE FRONTEND ---
echo $response;
?>