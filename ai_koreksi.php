<?php
header('Content-Type: application/json');

// --- SETTING API KEY ---
// Jika Bos belum punya, bisa buat gratis di https://aistudio.google.com/
define('GEMINI_API_KEY', 'MASUKKAN_API_KEY_GEMINI_BOS_DISINI');

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

if (GEMINI_API_KEY === 'MASUKKAN_API_KEY_GEMINI_BOS_DISINI') {
    sleep(2); // Simulasi mikir jika API key belum diganti
    echo json_encode([
        "status" => "salah", 
        "pesan" => "API Key Gemini belum diatur di ai_koreksi.php. Ini adalah simulasi dari sistem! Teks yang seharusnya dibaca adalah: $target_text"
    ]);
    exit;
}

// --- 2. KIRIM LANGSUNG KE GEMINI API ---
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . GEMINI_API_KEY;

$prompt = "Kamu adalah Ustadz ahli Nahwu dan Shorof. Dengarkan rekaman suara bacaan kitab kuning ini. 
Santri seharusnya membaca kalimat ini (lengkap dengan I'rob/Harokat akhirnya): '$target_text'.
Tugasmu:
1. Dengarkan apakah bacaan santri persis sama harokat akhirnya dengan target teks.
2. Jika benar, balas dengan format JSON: {\"status\": \"benar\", \"pesan\": \"Pujian singkat\"}
3. Jika salah, balas dengan JSON: {\"status\": \"salah\", \"pesan\": \"Koreksi spesifik kata apa yang salah harokatnya, jelaskan secara singkat kenapa secara kaidah Nahwu/Shorof.\"}
PENTING: Jangan tambahkan teks apapun selain format JSON tersebut.";

$payload = [
    "contents" => [ [ "parts" => [
        ["text" => $prompt],
        ["inlineData" => ["mimeType" => "audio/webm", "data" => $base64_audio]]
    ] ] ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch); curl_close($ch);

// --- 3. BACA JAWABAN GEMINI ---
$result = json_decode($response, true);
if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $ai_text = $result['candidates'][0]['content']['parts'][0]['text'];
    $ai_text = str_replace(['```json', '```'], '', $ai_text); // Bersihkan sisa format Markdown JSON
    $ai_json = json_decode(trim($ai_text), true);
    echo json_encode($ai_json ? $ai_json : ["status" => "error", "pesan" => "Gagal mengolah format Ustadz AI."]);
} else {
    echo json_encode(["status" => "error", "pesan" => "Ustadz AI gagal merespon.", "debug" => $response]);
}
?>