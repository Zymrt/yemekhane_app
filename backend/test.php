<?php
require_once 'config.php';

try {
    $roles = db()->roles->find();
    echo "<h2>✅ MongoDB bağlantısı çalışıyor!</h2>";
    echo "<pre>";
    foreach ($roles as $role) {
        print_r($role);
    }
    echo "</pre>";
} catch (Exception $e) {
    echo "<h2>❌ Hata:</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}