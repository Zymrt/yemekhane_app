<?php
// backend/api/auth.php

// Projemizin beyni olan config dosyasını dahil et.
// Bu dosya, veritabanı bağlantısını ve CORS ayarlarını zaten yapıyor.
require_once '../config.php';

// Frontend'den gelen JSON formatındaki isteği al ve PHP dizisine çevir.
$input = json_decode(file_get_contents('php://input'), true);

// İstekte bir 'action' (eylem) parametresi var mı kontrol et.
// Bu parametre 'login' (giriş) mi yoksa 'register' (kayıt) mı olacak.
$action = $input['action'] ?? '';

// Gelen eyleme göre ilgili fonksiyonu çalıştır.
if ($action === 'login') {
    login($input);
} elseif ($action === 'register') {
    register($input);
} else {
    // Eğer 'login' veya 'register' dışında bir eylem gelirse hata ver.
    http_response_code(400); // 400 Bad Request
    echo json_encode(['error' => 'Geçersiz eylem belirtildi.']);
}


/**
 * Kullanıcı Giriş Fonksiyonu
 */
function login($data) {
    $phone = $data['phone'] ?? '';
    $password = $data['password'] ?? '';

    // Telefon ve şifre alanları boş mu kontrol et.
    if (empty($phone) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Telefon ve şifre alanları zorunludur.']);
        exit;
    }

    // Kullanıcıyı veritabanında telefon numarasına göre bul.
    $user = db()->users->findOne(['phone' => $phone]);

    // Kullanıcı bulunduysa VE girilen şifre veritabanındaki hash'lenmiş şifre ile uyuşuyorsa...
    // password_verify() fonksiyonu bu karşılaştırmayı güvenli bir şekilde yapar.
    if ($user && password_verify($password, $user['password'])) {

        // Admin, kullanıcıyı onaylamış mı kontrol et.
        if ($user['status'] !== 'active') {
            http_response_code(403); // 403 Forbidden
            echo json_encode(['error' => 'Hesabınız henüz yönetici tarafından onaylanmadı.']);
            exit;
        }

        // Giriş başarılı. Kullanıcı bilgilerini oturuma (session) kaydet.
        $_SESSION['user_id'] = (string)$user['_id'];
        $_SESSION['role_name'] = $user['role_name'];

        // Frontend'e gönderilecek kullanıcı bilgilerini hazırla (şifreyi gönderme!)
        $userData = [
            'id' => (string)$user['_id'],
            'name' => $user['name'],
            'phone' => $user['phone'],
            'role_name' => $user['role_name'],
            'role_id' => (string)$user['role_id'],
            'balance' => $user['balance'] ?? 0
        ];

        // Başarılı cevabı ve kullanıcı verisini frontend'e gönder.
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Giriş başarılı!',
            'user' => $userData
        ]);

    } else {
        // Kullanıcı bulunamadıysa veya şifre yanlışsa...
        http_response_code(401); // 401 Unauthorized
        echo json_encode(['error' => 'Telefon numarası veya şifre hatalı.']);
    }
}


/**
 * Kullanıcı Kayıt Fonksiyonu
 */
function register($data) {
    // Formdan gelen verileri al
    $name = $data['name'] ?? '';
    $phone = $data['phone'] ?? '';
    $password = $data['password'] ?? '';
    $institution = $data['institution'] ?? ''; // Kurum adı
    $document_path = $data['document_path'] ?? ''; // Belge yolu

    // Alanların boş olup olmadığını kontrol et
    if (empty($name) || empty($phone) || empty($password) || empty($institution) || empty($document_path)) {
        http_response_code(400);
        echo json_encode(['error' => 'Tüm alanların doldurulması zorunludur.']);
        exit;
    }

    // Bu telefon numarası daha önce alınmış mı diye kontrol et
    $existingUser = db()->users->findOne(['phone' => $phone]);
    if ($existingUser) {
        http_response_code(409); // 409 Conflict
        echo json_encode(['error' => 'Bu telefon numarası zaten kayıtlı.']);
        exit;
    }

    // Şifreyi güvenli bir şekilde hash'le. Veritabanına asla düz metin olarak kaydetme!
    // PASSWORD_BCRYPT, PHP'nin en güncel ve güvenli şifreleme algoritmasıdır.
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Varsayılan rolü "Stajyer" olarak bulalım (veya adını siz nasıl belirlediyseniz)
    $defaultRole = db()->roles->findOne(['name' => 'Stajyer']);
    if (!$defaultRole) {
        http_response_code(500);
        echo json_encode(['error' => 'Varsayılan "Stajyer" rolü bulunamadı. Lütfen yöneticiyle iletişime geçin.']);
        exit;
    }

    // Yeni kullanıcı verisini hazırla
    $newUser = [
        'name' => $name,
        'phone' => $phone,
        'password' => $hashedPassword,
        'institution' => $institution,
        'document_path' => $document_path,
        'role_id' => $defaultRole['_id'], // ObjectId olarak
        'role_name' => $defaultRole['name'], // Kolay erişim için
        'status' => 'pending', // Yeni kullanıcılar onaya düşer
        'balance' => 0,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];

    // Yeni kullanıcıyı veritabanına ekle
    $result = db()->users->insertOne($newUser);

    // Başarılı cevabı gönder
    http_response_code(201); // 201 Created
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Kaydınız başarıyla alındı. Yönetici onayından sonra giriş yapabilirsiniz.'
    ]);
}