<?php
// ✅ Auth helper - รองรับ PHP 7 และ PHP 8
function getAuthenticatedUser($db) {
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] 
                : (isset($headers['authorization']) ? $headers['authorization'] : '');

    if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
        return null;
    }

    $token = substr($authHeader, 7);
    if (empty($token)) return null;

    $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE session_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    return $user ? $user : null;
}

function requireAuth($db) {
    $user = getAuthenticatedUser($db);
    if (!$user) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Unauthorized"]);
        exit;
    }
    return $user;
}

function requireAdmin($db) {
    $user = requireAuth($db);
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Forbidden: Admin only"]);
        exit;
    }
    return $user;
}
