<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : null;
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

if (!$product_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Product ID is required"]);
    exit;
}

try {
    $productQuery = "SELECT id, buffer_days, status FROM products WHERE id = :id";
    $productStmt = $db->prepare($productQuery);
    $productStmt->execute([':id' => $product_id]);
    $product = $productStmt->fetch();

    if (!$product) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Product not found"]);
        exit;
    }

    $bufferDays = $product['buffer_days'];
    $startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $endDate = date("Y-m-t", strtotime($startDate));

    // ✅ FIX #4: ใช้ positional ? แทน named params ซ้ำ เพื่อหลีกเลี่ยง PDO duplicate key error
    $query = "SELECT b.pickup_date, b.return_date, b.status
              FROM bookings b
              WHERE b.product_id = ?
              AND b.status NOT IN ('cancelled', 'completed')
              AND (
                  (b.pickup_date BETWEEN ? AND ?)
                  OR (b.return_date BETWEEN ? AND ?)
                  OR (b.pickup_date <= ? AND b.return_date >= ?)
              )";

    $stmt = $db->prepare($query);
    $stmt->execute([
        $product_id,
        $startDate, $endDate,
        $startDate, $endDate,
        $startDate, $endDate
    ]);

    $bookings = $stmt->fetchAll();

    $unavailableDates = [];
    foreach ($bookings as $booking) {
        $pickupDate = new DateTime($booking['pickup_date']);
        $returnDate = new DateTime($booking['return_date']);
        $returnDate->modify("+{$bufferDays} days");

        $interval = new DateInterval('P1D');
        $endPlusOne = clone $returnDate;
        $endPlusOne->modify('+1 day');
        $dateRange = new DatePeriod($pickupDate, $interval, $endPlusOne);

        foreach ($dateRange as $date) {
            $unavailableDates[] = $date->format('Y-m-d');
        }
    }

    $unavailableDates = array_values(array_unique($unavailableDates));
    sort($unavailableDates);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "data" => [
            "product_id"     => $product_id,
            "product_status" => $product['status'],
            "buffer_days"    => $bufferDays,
            "month"          => $month,
            "year"           => $year,
            "unavailable_dates" => $unavailableDates,
            "bookings"       => $bookings
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
