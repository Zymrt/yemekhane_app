<?php
require_once '../../config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Oturum açılmamış.']);
    exit;
}

// Admin mi kontrol et
$user = db()->users->findOne(['_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])]);
if ($user['role_name'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Bu işlemi sadece admin yapabilir.']);
    exit;
}

$tickets = db()->tickets->find([], [
    'sort' => ['created_at' => -1]
]);

$output = [];
foreach ($tickets as $ticket) {
    // Kullanıcı bilgilerini al
    $user_info = db()->users->findOne(['_id' => $ticket['user_id']]);

    $output[] = [
        'id' => (string)$ticket['_id'],
        'user_name' => $user_info['name'],
        'menu_date' => $ticket['menu_date']->toDateTime()->format('Y-m-d'),
        'price' => $ticket['price'],
        'created_at' => $ticket['created_at']->toDateTime()->format('Y-m-d H:i:s')
    ];
}

echo json_encode($output);