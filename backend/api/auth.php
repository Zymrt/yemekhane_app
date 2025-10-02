<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$action = $input['action'] ?? '';

if ($action === 'register') {
    registerUser($input);
} elseif ($action === 'login') {
    loginUser($input);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz işlem.']);
}

function registerUser($data) {
    $name = trim($data['name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'] ?? '';
    $role_name = trim($data['role_name'] ?? ''); // Kullanıcıdan gelen kurum adı

    if (!$name || !$phone || !$password || !$role_name) {
        http_response_code(400);
        echo json_encode(['error' => 'Tüm alanlar zorunludur.']);
        exit;
    }

    // Telefon numarası zaten kayıtlı mı?
    $existing = db()->users->findOne(['phone' => $phone]);
    if ($existing) {
        http_response_code(409);
        echo json_encode(['error' => 'Bu telefon numarası zaten kayıtlı.']);
        exit;
    }

    // Kullanıcıyı veritabanına ekle (onay bekliyor)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    db()->users->insertOne([
        'name' => $name,
        'phone' => $phone,
        'password' => $hashedPassword,
        'role_name' => $role_name, // Onay bekleyen kurum
        'status' => 'pending',
        'balance' => 0.00,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Kayıt isteğiniz alındı. Admin onayı bekleniyor.'
    ]);
}

function loginUser($data) {
    $phone = trim($data['phone'] ?? '');
    $password = $data['password'] ?? '';

    if (!$phone || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Telefon ve şifre gerekli.']);
        exit;
    }

    $user = db()->users->findOne(['phone' => $phone]);
    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Telefon veya şifre yanlış.']);
        exit;
    }

    if ($user['status'] !== 'active') {
        http_response_code(401);
        echo json_encode(['error' => 'Hesabınız onaylanmadı.']);
        exit;
    }

    // Oturumu başlat
    $_SESSION['user_id'] = (string)$user['_id'];
    $_SESSION['phone'] = $user['phone'];

    // Kullanıcı bilgilerini döndür (şifresiz)
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => (string)$user['_id'],
            'name' => $user['name'],
            'phone' => $user['phone'],
            'balance' => $user['balance'],
            'role_name' => $user['role_name'],
            'status' => $user['status']
        ]
    ]);
}