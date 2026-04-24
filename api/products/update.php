<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->id)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Product ID is required"
    ]);
    exit;
}

try {
    // Build dynamic update query
    $fields = [];
    $params = [':id' => $data->id];

    if (isset($data->name)) {
        $fields[] = "name = :name";
        $params[':name'] = $data->name;
    }
    if (isset($data->description)) {
        $fields[] = "description = :description";
        $params[':description'] = $data->description;
    }
    if (isset($data->category)) {
        $fields[] = "category = :category";
        $params[':category'] = $data->category;
    }
    if (isset($data->size)) {
        $fields[] = "size = :size";
        $params[':size'] = $data->size;
    }
    if (isset($data->color)) {
        $fields[] = "color = :color";
        $params[':color'] = $data->color;
    }
    if (isset($data->rental_price)) {
        $fields[] = "rental_price = :rental_price";
        $params[':rental_price'] = $data->rental_price;
    }
    if (isset($data->deposit_price)) {
        $fields[] = "deposit_price = :deposit_price";
        $params[':deposit_price'] = $data->deposit_price;
    }
    if (isset($data->image_url)) {
        $fields[] = "image_url = :image_url";
        $params[':image_url'] = $data->image_url;
    }
    if (isset($data->status)) {
        $fields[] = "status = :status";
        $params[':status'] = $data->status;
    }
    if (isset($data->buffer_days)) {
        $fields[] = "buffer_days = :buffer_days";
        $params[':buffer_days'] = $data->buffer_days;
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "No fields to update"
        ]);
        exit;
    }

    $query = "UPDATE products SET " . implode(", ", $fields) . " WHERE id = :id";

    $stmt = $db->prepare($query);
    $stmt->execute($params);

    // ตรวจสอบว่า product มีอยู่จริง
    $checkStmt = $db->prepare("SELECT id FROM products WHERE id = :id");
    $checkStmt->execute([':id' => $data->id]);

    if ($checkStmt->fetch()) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Product updated successfully"
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Product not found"
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
