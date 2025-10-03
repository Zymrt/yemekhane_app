<?php
// backend/api/upload.php

// Projemizin beyni olan config dosyasını dahil et.
// Bu dosya, özellikle CORS ayarları için gerekli.
require_once '../config.php';

// Dosya yükleme işlemleri için HTTP POST metodu beklenir.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // 405 Method Not Allowed
    echo json_encode(['success' => false, 'error' => 'Geçersiz istek metodu.']);
    exit;
}

// 'document' adında bir dosyanın gönderilip gönderilmediğini ve bir hata olup olmadığını kontrol et.
if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400); // 400 Bad Request
    $errorMessage = 'Dosya yüklenirken bir hata oluştu. Hata Kodu: ' . ($_FILES['document']['error'] ?? 'Bilinmiyor');
    echo json_encode(['success' => false, 'error' => $errorMessage]);
    exit;
}

// --- GÜVENLİK KONTROLLERİ ---

// Yüklenen dosyaların kaydedileceği klasör
$uploadDir = '../uploads/';

// Güvenlik için dosya adını temizle. Sadece harf, rakam, nokta, tire ve alt çizgiye izin ver.
// Bu, ../ gibi tehlikeli karakterleri temizler.
$fileName = basename($_FILES['document']['name']);
$safeFileName = preg_replace("/[^A-Za-z0-9\.\-\_]/", '', $fileName);

// Dosya adını eşsiz (unique) yapalım ki aynı isimli dosyalar birbirinin üzerine yazılmasın.
// Başına zaman damgası ve rastgele bir karakter ekliyoruz.
$fileExtension = pathinfo($safeFileName, PATHINFO_EXTENSION);
$uniqueFileName = uniqid() . '-' . md5($safeFileName) . '.' . $fileExtension;

// Dosyanın tam yolu
$targetPath = $uploadDir . $uniqueFileName;

// İzin verilen dosya uzantıları
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Geçersiz dosya türü. Sadece JPG, PNG ve PDF dosyaları yükleyebilirsiniz.']);
    exit;
}

// İzin verilen maksimum dosya boyutu (örn: 5 MB)
$maxFileSize = 5 * 1024 * 1024; // 5 MB in bytes
if ($_FILES['document']['size'] > $maxFileSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dosya boyutu çok büyük. Maksimum 5 MB olabilir.']);
    exit;
}

// --- DOSYAYI TAŞIMA ---

// Geçici klasördeki dosyayı asıl hedef klasörümüze taşı.
if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
    // Başarılı olursa, frontend'in kullanması için dosyanın yolunu geri gönder.
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Dosya başarıyla yüklendi.',
        'filePath' => 'uploads/' . $uniqueFileName // auth.php'nin veritabanına kaydedeceği yol
    ]);
} else {
    // Başarısız olursa sunucu hatası ver.
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Dosya sunucuya kaydedilemedi.']);
}