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

$action = $input['action'] ?? '';

if ($action === 'get_history') {
    getTicketHistory();
} elseif ($action === 'buy') {
    buyTicket($input);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz işlem.']);
}

function getTicketHistory() {
    $tickets = db()->tickets->find(['user_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])], [
        'sort' => ['created_at' => -1]
    ]);

    $output = [];
    foreach ($tickets as $ticket) {
        $output[] = [
            'id' => (string)$ticket['_id'],
            'menu_date' => $ticket['menu_date']->toDateTime()->format('Y-m-d'),
            'price' => $ticket['price'],
            'created_at' => $ticket['created_at']->toDateTime()->format('Y-m-d H:i:s')
        ];
    }

    echo json_encode($output);
}

function buyTicket($data) {
    $menu_date = $data['menu_date'] ?? '';
    if (!$menu_date) {
        http_response_code(400);
        echo json_encode(['error' => 'Menü tarihi gerekli.']);
        exit;
    }

    $user = db()->users->findOne(['_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])]);
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Kullanıcı bulunamadı.']);
        exit;
    }

    // Makam fiyatını al
    $role = db()->roles->findOne(['_id' => new MongoDB\BSON\ObjectId($user['role_id'])]);
    if (!$role) {
        http_response_code(500);
        echo json_encode(['error' => 'Makam bilgisi alınamadı.']);
        exit;
    }

    $price = $role['meal_price'];

    // Fiyat 0 TL ise, fiş alınamaz
    if ($price <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Fiş fiyatı 0 TL olamaz.']);
        exit;
    }

    // Bakiye yeterli mi?
    if ($user['balance'] < $price) {
        http_response_code(400);
        echo json_encode(['error' => 'Yetersiz bakiye.']);
        exit;
    }

    // Fiş oluştur
    try {
        $ticket = db()->tickets->insertOne([
            'user_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id']),
            'menu_date' => new MongoDB\BSON\UTCDateTime(strtotime($menu_date) * 1000),
            'price' => $price,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);

        // Bakiyeyi düş
        db()->users->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])],
            ['$inc' => ['balance' => -$price]]
        );

        echo json_encode([
            'success' => true,
            'message' => 'Fiş başarıyla alındı!',
            'ticket_id' => (string)$ticket->getInsertedId(),
            'price' => $price,
            'new_balance' => $user['balance'] - $price
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Fiş alınırken hata oluştu.']);
    }
}