<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'error' => 'Geçersiz istek.']));
}
if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Dosya yüklenirken bir hata oluştu.']));
}

$uploadDir = '../uploads/';
$fileName = basename($_FILES['document']['name']);
$safeFileName = preg_replace("/[^A-Za-z0-9\.\-\_]/", '', $fileName);
$fileExtension = pathinfo($safeFileName, PATHINFO_EXTENSION);
$uniqueFileName = uniqid() . '-' . md5($safeFileName) . '.' . $fileExtension;
$targetPath = $uploadDir . $uniqueFileName;

if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'filePath' => 'uploads/' . $uniqueFileName
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Dosya sunucuya kaydedilemedi.']);
}```

#### **Dosya 3: `backend/api/auth.php` (Kayıt ve Giriş Sistemi)**
```php
<?php
require_once '../config.php';
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'login') {
    login($input);
} elseif ($action === 'register') {
    register($input);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz eylem.']);
}

function login($data) {
    $phone = $data['phone'] ?? '';
    $password = $data['password'] ?? '';
    if (empty($phone) || empty($password)) {
        http_response_code(400); exit(json_encode(['error' => 'Telefon ve şifre zorunludur.']));
    }
    $user = db()->users->findOne(['phone' => $phone]);
    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] !== 'active') {
            http_response_code(403); exit(json_encode(['error' => 'Hesabınız henüz onaylanmadı.']));
        }
        $_SESSION['user_id'] = (string)$user['_id'];
        $userData = [
            'id' => (string)$user['_id'], 'name' => $user['name'], 'phone' => $user['phone'],
            'role_name' => $user['role_name'], 'role_id' => (string)$user['role_id'],
            'balance' => $user['balance'] ?? 0
        ];
        http_response_code(200);
        echo json_encode(['success' => true, 'user' => $userData]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Telefon veya şifre hatalı.']);
    }
}

function register($data) {
    $name = $data['name'] ?? ''; $phone = $data['phone'] ?? ''; $password = $data['password'] ?? '';
    $institution = $data['institution'] ?? ''; $document_path = $data['document_path'] ?? '';

    if (empty($name) || empty($phone) || empty($password) || empty($document_path)) {
        http_response_code(400); exit(json_encode(['error' => 'Tüm alanlar zorunludur.']));
    }
    if (db()->users->findOne(['phone' => $phone])) {
        http_response_code(409); exit(json_encode(['error' => 'Bu telefon numarası zaten kayıtlı.']));
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $defaultRole = db()->roles->findOne(['name' => 'Stajyer']);
    if (!$defaultRole) {
        http_response_code(500); exit(json_encode(['error' => 'Varsayılan "Stajyer" rolü bulunamadı.']));
    }
    
    $newUser = [
        'name' => $name, 'phone' => $phone, 'password' => $hashedPassword,
        'institution' => $institution, 'document_path' => $document_path,
        'role_id' => $defaultRole['_id'], 'role_name' => $defaultRole['name'],
        'status' => 'pending', 'balance' => 0,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];
    db()->users->insertOne($newUser);
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Kayıt başarılı. Onay sonrası giriş yapabilirsiniz.']);
}