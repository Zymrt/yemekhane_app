<?php
require_once '../config.php';
$menu = db()->menu->findOne([], ['sort' => ['date' => -1]]);
if ($menu) {
    echo json_encode([
        'date' => $menu['date']->toDateTime()->format('Y-m-d'),
        'description' => $menu['description']
    ]);
} else {
    echo json_encode([
        'date' => 'Menü Yok', 'description' => 'Yönetici henüz menü eklemedi.'
    ]);
}