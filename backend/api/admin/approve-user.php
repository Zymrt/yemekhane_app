<?php
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Oturum açılmamış.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$userId = $input['user_id'] ?? null;
if (!$userId) {
    http_response_code(400);
    echo json_encode(['error' => 'Kullanıcı ID gerekli.']);
    exit;
}

// Kullanıcıyı bul
$user = db()->users->findOne(['_id' => new MongoDB\BSON\ObjectId($userId)]);
if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'Kullanıcı bulunamadı.']);
    exit;
}

// Rol ID'sini bul (eğer yoksa oluştur)
$role = db()->roles->findOne(['name' => $user['role_name']]);
if (!$role) {
    // Yeni rol oluştur
    $result = db()->roles->insertOne([
        'name' => $user['role_name'],
        'meal_price' => 125.00 // Varsayılan fiyat
    ]);
    $roleId = $result->getInsertedId();
} else {
    $roleId = $role['_id'];
}

// Kullanıcıyı onayla
db()->users->updateOne(
    ['_id' => new MongoDB\BSON\ObjectId($userId)],
    [
        '$set' => [
            'status' => 'active',
            'role_id' => $roleId
        ]
    ]
);

echo json_encode(['success' => true, 'message' => 'Kullanıcı onaylandı.']);