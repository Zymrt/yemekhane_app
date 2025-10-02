<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// JSON veri al
$input = json_decode(file_get_contents('php://input'), true);

// action: 'add' veya 'get' olacak
$action = $input['action'] ?? '';

if ($action === 'add') {
    addMenu($input);
} elseif ($action === 'get') {
    getMenu();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz işlem.']);
}

function addMenu($data) {
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

    $menu_date = $data['menu_date'] ?? '';
    $menu_description = $data['menu_description'] ?? '';

    if (!$menu_date || !$menu_description) {
        http_response_code(400);
        echo json_encode(['error' => 'Tüm alanlar zorunludur.']);
        exit;
    }

    // Menüyü veritabanına ekle
    $result = db()->menu->insertOne([
        'date' => new MongoDB\BSON\UTCDateTime(strtotime($menu_date) * 1000),
        'description' => $menu_description,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Menü başarıyla eklendi.'
    ]);
}

function getMenu() {
    // En son menüyü getir
    $menu = db()->menu->findOne([], ['sort' => ['date' => -1]]);

    if ($menu) {
        echo json_encode([
            'date' => $menu['date']->toDateTime()->format('Y-m-d'),
            'description' => $menu['description']
        ]);
    } else {
        echo json_encode([
            'date' => 'Henüz menü eklenmedi.',
            'description' => 'Yarın menü burada olacak.'
        ]);
    }
}