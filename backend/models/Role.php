<?php

class Role {
    private $collection;

    public function __construct() {
        global $database;
        $this->collection = $database->roles;
    }

    // Tüm rolleri listele
    public function getAll() {
        $roles = $this->collection->find();
        $output = [];
        foreach ($roles as $role) {
            $output[] = [
                'id' => (string)$role['_id'],
                'name' => $role['name'],
                'meal_price' => $role['meal_price']
            ];
        }
        return $output;
    }

    // ID'ye göre rol bul
    public function findById($id) {
        return $this->collection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
    }
}