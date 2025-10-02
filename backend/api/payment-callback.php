<?php
require_once '../config.php';

$payment_id = $_GET['payment_id'] ?? null;
$status = $_GET['status'] ?? null; // success / fail

if (!$payment_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz ödeme ID.']);
    exit;
}

// Ödeme kaydını getir
$payment = db()->payments->findOne(['payment_id' => $payment_id]);

if (!$payment) {
    http_response_code(404);
    echo json_encode(['error' => 'Ödeme bulunamadı.']);
    exit;
}

if ($status === 'success') {
    // Bakiyeyi güncelle
    db()->users->updateOne(
        ['_id' => $payment['user_id']],
        ['$inc' => ['balance' => $payment['amount']]]
    );

    // Ödeme durumunu güncelle
    db()->payments->updateOne(
        ['payment_id' => $payment_id],
        ['$set' => ['status' => 'completed']]
    );

    echo json_encode(['message' => 'Ödeme başarılı, bakiye güncellendi.']);
} else {
    // Ödeme durumunu güncelle
    db()->payments->updateOne(
        ['payment_id' => $payment_id],
        ['$set' => ['status' => 'failed']]
    );

    echo json_encode(['message' => 'Ödeme başarısız.']);
}