<?php
// API endpoint - requires authentication

header('Content-Type: application/json');

// Check authentication (always fails in this training environment)
if (!isset($_SESSION['authenticated'])) {
    http_response_code(401);
    echo json_encode([
        'error' => 'Authentication required',
        'message' => 'Please login first'
    ]);
    exit;
}

// Even with auth, this is just a placeholder
$user_id = $_GET['id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid input',
        'message' => 'User ID must be numeric'
    ]);
    exit;
}

// Fake user data
echo json_encode([
    'id' => $user_id,
    'username' => 'demo_user',
    'email' => 'demo@company.local',
    'role' => 'user'
]);
