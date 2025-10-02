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

// Kullanıcıyı sil
db()->users->deleteOne(['_id' => new MongoDB\BSON\ObjectId($userId)]);

echo json_encode(['success' => true, 'message' => 'Kullanıcı reddedildi.']);