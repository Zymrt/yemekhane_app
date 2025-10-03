<?php

// Hata raporlamayı geliştirme aşaması için açalım.
// Bu sayede bir hata olduğunda boş sayfa yerine hatanın ne olduğunu görürüz.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- YENİ EKLENEN CORS BAŞLIKLARI ---
// Frontend'in çalıştığı adrese (origin) izin veriyoruz.
header("Access-Control-Allow-Origin: http://localhost:5173");
// Frontend'in oturum bilgileri (cookie) gönderebilmesi için izin veriyoruz.
header("Access-Control-Allow-Credentials: true");
// Frontend'in hangi HTTP metodlarını (POST, GET vb.) kullanabileceğini belirtiyoruz.
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
// Frontend'in hangi başlıkları (Content-Type vb.) gönderebileceğini belirtiyoruz.
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Tarayıcılar, asıl istek öncesi bir "OPTIONS" ön kontrol isteği gönderir.
// Bu isteği görüp "tamam, izinler yukarıdaki gibi" deyip çıkmasını sağlıyoruz.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
// --- CORS BÖLÜMÜ SONU ---


// Composer'ın oluşturduğu otomatik yükleyiciyi dahil et.
require_once __DIR__ . '/vendor/autoload.php';

// Güvenli oturum ayarları
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => false, // Geliştirme ortamında (localhost) false, canlıda true olmalı
    'cookie_samesite' => 'Lax'
]);

// Veritabanı bağlantısını yönetecek bir fonksiyon.
function db() {
    static $client = null;
    static $database = null;

    if ($client === null) {
        try {
            $connectionString = "mongodb://localhost:27017";
            $client = new MongoDB\Client($connectionString);

            // Veritabanı adını burada belirtiyoruz.
            $dbName = "yemekhane"; 
            $database = $client->selectDatabase($dbName);

        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            die(json_encode(['error' => 'Veritabanı bağlantısı kurulamadı: ' . $e->getMessage()]));
        }
    }
    
    return $database;
}