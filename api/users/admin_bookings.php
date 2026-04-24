<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

include_once '../config/database.php';
include_once '../config/auth.php';

$database = new Database();
$db = $database->getConnection();

requireAdmin($db);

try {
    $query = "SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
                     p.name as product_name, p.image_url as product_image, p.rental_price
              FROM bookings b
              JOIN users u ON b.user_id = u.id
              JOIN products p ON b.product_id = p.id
              ORDER BY b.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $bookings = $stmt->fetchAll();

    $summary = [
        'total'     => count($bookings),
        'pending'   => count(array_filter($bookings, fn($b) => $b['status'] === 'pending')),
        'confirmed' => count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed')),
        'cancelled' => count(array_filter($bookings, fn($b) => $b['status'] === 'cancelled')),
        'completed' => count(array_filter($bookings, fn($b) => $b['status'] === 'completed')),
        'revenue'   => array_sum(array_map(fn($b) => $b['total_price'], array_filter($bookings, fn($b) => $b['status'] === 'completed')))
    ];

    echo json_encode(["success" => true, "data" => $bookings, "summary" => $summary]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
