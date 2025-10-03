<?php
// backend/api/price.php

// Veritabanı bağlantısı ve ayarları
require_once '../config.php';

// Oturumu başlat
session_start();

// Tarayıcıların ön kontrol (preflight) isteklerine izin ver
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Bu işlemi yapmak için giriş yapmalısınız.']);
    exit;
}

try {
    // Giriş yapmış kullanıcının ID'sini al
    $user_id = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
    
    // Kullanıcıyı veritabanında bul
    $user = db()->users->findOne(['_id' => $user_id]);

    // Kullanıcı veya rol bilgisi yoksa hata ver
    if (!$user || !isset($user['role_id'])) {
        http_response_code(404);
        echo json_encode(['error' => 'Kullanıcı veya atanmış rol bulunamadı.']);
        exit;
    }

    // Kullanıcının rol ID'sini al
    $role_id = new MongoDB\BSON\ObjectId($user['role_id']);

    // Rolü, roles koleksiyonunda bul
    $role = db()->roles->findOne(['_id' => $role_id]);

    // Rol veya role ait fiyat bilgisi yoksa hata ver (ve fiyatı 0 gönder)
    if (!$role || !isset($role['meal_price'])) {
        http_response_code(404);
        echo json_encode(['price' => 0, 'error' => 'Bu role ait bir yemek fiyatı tanımlanmamış.']);
        exit;
    }

    // Her şey yolundaysa, fiyatı JSON olarak gönder
    header('Content-Type: application/json');
    echo json_encode(['price' => $role['meal_price']]);

} catch (Exception $e) {
    // Beklenmedik bir hata olursa sunucu hatası ver
    http_response_code(500);
    echo json_encode(['error' => 'Sunucu hatası: ' . $e->getMessage()]);
}