    <?php
    require_once '../config.php';

    try {
        $roles = db()->roles->find([], ['projection' => ['_id' => 1, 'name' => 1, 'meal_price' => 1]]);
        $output = [];
        foreach ($roles as $role) {
            $output[] = [
                'id' => (string)$role['_id'],
                'name' => $role['name'],
                'meal_price' => $role['meal_price']
            ];
        }
        echo json_encode($output);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Birimler yüklenemedi.']);
    }