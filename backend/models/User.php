<?php

class User {
    private $collection;

    public function __construct() {
        global $database;
        $this->collection = $database->users;
    }

    // Telefon numarasına göre kullanıcı bul
    public function findByPhone($phone) {
        return $this->collection->findOne(['phone' => $phone]);
    }

    // ID'ye göre kullanıcı bul
    public function findById($id) {
        return $this->collection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
    }

    // Yeni kullanıcı ekle
    public function create($data) {
        $result = $this->collection->insertOne([
            'phone' => $data['phone'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'name' => $data['name'] ?? '',
            'role_id' => new MongoDB\BSON\ObjectId($data['role_id']),
            'balance' => $data['balance'] ?? 0.00,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        return $result->getInsertedId();
    }

    // Bakiyeyi güncelle
    public function updateBalance($userId, $amount) {
        $this->collection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($userId)],
            ['$inc' => ['balance' => $amount]]
        );
    }

    // Kullanıcı bilgilerini al
    public function getProfile($userId) {
        $user = $this->findById($userId);
        if (!$user) return null;

        return [
            'id' => (string)$user['_id'],
            'name' => $user['name'],
            'phone' => $user['phone'],
            'balance' => $user['balance'],
            'role_id' => (string)$user['role_id']
        ];
    }
}