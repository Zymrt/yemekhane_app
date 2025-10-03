<?php
require_once '../config.php';
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); exit(json_encode(['error' => 'Giriş yapılmamış.']));
}
$user = db()->users->findOne(['_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])]);
if (!$user || !isset($user['role_id'])) {
    http_response_code(404); exit(json_encode(['error' => 'Kullanıcı veya rol bulunamadı.']));
}
$role = db()->roles->findOne(['_id' => $user['role_id']]);
if (!$role || !isset($role['meal_price'])) {
    http_response_code(404); exit(json_encode(['price' => 0, 'error' => 'Role ait fiyat yok.']));
}
echo json_encode(['price' => $role['meal_price']]);