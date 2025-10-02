<?php
require_once '../../config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Oturum açılmamış.']);
    exit;
}

// Admin mi kontrol et (opsiyonel)
$user = db()->users->findOne(['_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])]);
if ($user['role_name'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Bu işlemi sadece admin yapabilir.']);
    exit;
}

$users = db()->users->find(['status' => 'pending']);

$output = [];
foreach ($users as $user) {
    $output[] = [
        '_id' => (string)$user['_id'],
        'name' => $user['name'],
        'phone' => $user['phone'],
        'role_name' => $user['role_name'],
        'document_path' => $user['document_path'],
        'created_at' => $user['created_at']->toDateTime()->format('Y-m-d H:i:s')
    ];
}

echo json_encode($output);