<?php

class Menu {
    private $collection;

    public function __construct() {
        global $database;
        $this->collection = $database->menu;
    }

    // En son menüyü al
    public function getLast() {
        $menu = $this->collection->find([], ['sort' => ['date' => -1], 'limit' => 1]);
        foreach ($menu as $item) {
            return [
                'date' => $item['date']->toDateTime()->format('Y-m-d'),
                'description' => $item['description']
            ];
        }
        return ['date' => 'Henüz menü eklenmedi.', 'description' => 'Yarın menü burada olacak.'];
    }

    // Tarihe göre menü al
    public function getByDate($dateString) {
        $date = new MongoDB\BSON\UTCDateTime(strtotime($dateString) * 1000);
        return $this->collection->findOne(['date' => $date]);
    }
}