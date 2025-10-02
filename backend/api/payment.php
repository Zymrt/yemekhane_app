<?php
require_once '../config.php';

// Hata ayıklama için
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

$amount = $input['amount'] ?? 0;
if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçerli bir tutar girin.']);
    exit;
}

// ISBANK SANAL POS BİLGİLERİ (TEST ORTAMI)
$terminal_id = '100100000';
$client_id = '100100000';
$username = 'ISBANKAPI';
$password = 'ISBANK07';
$store_key = 'TRPS1234';

// Ödeme ID'si oluştur (benzersiz)
$payment_id = bin2hex(random_bytes(16));

// Ödeme isteğini geçici olarak veritabanına kaydet
db()->payments->insertOne([
    'payment_id' => $payment_id,
    'user_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id']),
    'amount' => $amount,
    'status' => 'pending',
    'created_at' => new MongoDB\BSON\UTCDateTime()
]);

// Hash Hesaplama
$rnd = time();
$hash_str = $client_id . $terminal_id . $amount . $payment_id . $rnd . $store_key;
$hash = base64_encode(sha1($hash_str, true));

// XML Oluştur
$xml = '<?xml version="1.0" encoding="UTF-8"?>
<CC5Request>
    <Name>' . $username . '</Name>
    <Password>' . $password . '</Password>
    <ClientId>' . $client_id . '</ClientId>
    <Amount>' . $amount . '</Amount>
    <OrderId>' . $payment_id . '</OrderId>
    <OkUrl>http://localhost:8000/api/payment-callback.php?status=success</OkUrl>
    <FailUrl>http://localhost:8000/api/payment-callback.php?status=fail</FailUrl>
    <Rnd>' . $rnd . '</Rnd>
    <HashData>' . $hash . '</HashData>
    <TransId></TransId>
    <UserId></UserId>
    <CardNumber></CardNumber>
    <CardExpireDate></CardExpireDate>
    <CardCvc></CardCvc>
    <PayerTxnId></PayerTxnId>
    <PayerSecurityLevel></PayerSecurityLevel>
    <PayerAuthenticationCode></PayerAuthenticationCode>
    <CardholderPresentCode>0</CardholderPresentCode>
    <MotoInd>N</MotoInd>
    <CurrencyCode>949</CurrencyCode>
    <InstallmentCount>0</InstallmentCount>
    <WalletId></WalletId>
    <PayerPoint></PayerPoint>
    <PointAmount></PointAmount>
    <PointOkUrl></PointOkUrl>
    <PointFailUrl></PointFailUrl>
    <BonusAmount></BonusAmount>
    <SubMerchantId></SubMerchantId>
    <SubMerchantName></SubMerchantName>
    <SubMerchantType></SubMerchantType>
    <SubMerchantSubType></SubMerchantSubType>
    <SubMerchantPhone></SubMerchantPhone>
    <SubMerchantEmail></SubMerchantEmail>
    <SubMerchantAddress></SubMerchantAddress>
    <SubMerchantCity></SubMerchantCity>
    <SubMerchantCountry></SubMerchantCountry>
    <SubMerchantPostalCode></SubMerchantPostalCode>
    <SubMerchantTaxNumber></SubMerchantTaxNumber>
    <SubMerchantTaxOffice></SubMerchantTaxOffice>
    <SubMerchantLegalName></SubMerchantLegalName>
    <SubMerchantLegalLastName></SubMerchantLegalLastName>
    <SubMerchantLegalIdentityNumber></SubMerchantLegalIdentityNumber>
    <SubMerchantLegalBirthDate></SubMerchantLegalBirthDate>
    <SubMerchantLegalNationality></SubMerchantLegalNationality>
    <SubMerchantLegalGender></SubMerchantLegalGender>
    <SubMerchantLegalMaritalStatus></SubMerchantLegalMaritalStatus>
    <SubMerchantLegalEducationStatus></SubMerchantLegalEducationStatus>
    <SubMerchantLegalOccupation></SubMerchantLegalOccupation>
    <SubMerchantLegalEmploymentStatus></SubMerchantLegalEmploymentStatus>
    <SubMerchantLegalMonthlyIncome></SubMerchantLegalMonthlyIncome>
    <SubMerchantLegalYearlyIncome></SubMerchantLegalYearlyIncome>
    <SubMerchantLegalCreditLimit></SubMerchantLegalCreditLimit>
    <SubMerchantLegalCreditCardNumber></SubMerchantLegalCreditCardNumber>
    <SubMerchantLegalCreditCardExpireDate></SubMerchantLegalCreditCardExpireDate>
    <SubMerchantLegalCreditCardCvc></SubMerchantLegalCreditCardCvc>
    <SubMerchantLegalCreditCardHolderName></SubMerchantLegalCreditCardHolderName>
    <SubMerchantLegalCreditCardType></SubMerchantLegalCreditCardType>
    <SubMerchantLegalCreditCardInstallment></SubMerchantLegalCreditCardInstallment>
    <SubMerchantLegalCreditCardInstallmentCount></SubMerchantLegalCreditCardInstallmentCount>
    <SubMerchantLegalCreditCardInstallmentAmount></SubMerchantLegalCreditCardInstallmentAmount>
    <SubMerchantLegalCreditCardInstallmentTotalAmount></SubMerchantLegalCreditCardInstallmentTotalAmount>
</CC5Request>';

// cURL ile NestPay'e gönder
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://setmpos-test.isbank.com.tr/fim/api");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/xml; charset=utf-8',
    'Content-Length: ' . strlen($xml)
]);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    curl_close($ch);
    http_response_code(500);
    echo json_encode(['error' => 'cURL hatası: ' . $error_msg]);
    exit;
}

curl_close($ch);

// XML cevabı parse et
$result = simplexml_load_string($response);

if ($result && $result->OrderId) {
    // Kullanıcıyı 3D Secure sayfasına yönlendir
    $redirect_url = "https://setmpos-test.isbank.com.tr/fim/est3Dgate";

    echo json_encode([
        'success' => true,
        'redirect_url' => $redirect_url,
        'payment_id' => (string)$result->OrderId
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'error' => 'Ödeme başlatılamadı.',
        'details' => (string)$result->ErrMsg ?? 'Bilinmeyen hata.',
        'response' => $response // Hata ayıklama için
    ]);
}