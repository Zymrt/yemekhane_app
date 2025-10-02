<?php
require_once 'config.php';

try {
    // Varsa silme, her seferinde tekrar eklememek için kontrol et
    $existing = db()->roles->countDocuments(['name' => 'Belediye Personeli']);
    if ($existing == 0) {
        db()->roles->insertMany([
            [
                'name' => 'Belediye Personeli',
                'meal_price' => 125.00
            ],
            [
                'name' => 'Kaymakamlık Personeli',
                'meal_price' => 150.00
            ],
            [
                'name' => 'Stajyer',
                'meal_price' => 75.00
            ]
        ]);
        echo "<h2 style='color:green;'>✅ Veritabanı ve Birimler başarıyla oluşturuldu!</h2>";
        echo "<p>Şimdi Compass'te 'yemekhane' veritabanını görebilirsin.</p>";
    } else {
        echo "<h2>ℹ️ Veritabanı zaten ayarlanmış.</h2>";
    }
} catch (Exception $e) {
    echo "<h2 style='color:red;'>❌ Hata:</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}