<?php
require_once '../config.php';

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

$amount = $input['amount'] ?? 0;
if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçerli bir tutar girin.']);
    exit;
}

try {
    db()->users->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])],
        ['$inc' => ['balance' => $amount]]
    );

    $user = db()->users->findOne(['_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])]);

    echo json_encode([
        'success' => true,
        'message' => 'Bakiye yüklendi!',
        'new_balance' => $user['balance']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Bakiye yüklenirken hata oluştu.']);
}