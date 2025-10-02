<?php

class Ticket {
    private $collection;

    public function __construct() {
        global $database;
        $this->collection = $database->tickets;
    }

    // Yeni fiş oluştur
    public function create($userId, $menuDate, $price) {
        $result = $this->collection->insertOne([
            'user_id' => new MongoDB\BSON\ObjectId($userId),
            'menu_date' => new MongoDB\BSON\UTCDateTime(strtotime($menuDate) * 1000),
            'price' => $price,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        return $result->getInsertedId();
    }

    // Kullanıcının fişlerini listele
    public function getByUser($userId) {
        $tickets = $this->collection->find(['user_id' => new MongoDB\BSON\ObjectId($userId)], [
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
        return $output;
    }
}