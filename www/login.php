<?php
// Simulated login with fake SQL injection vulnerability

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simulate "vulnerable" but actually not exploitable
    // Add random delay to simulate SQL time-based injection
    if (strpos($username, 'SLEEP') !== false || strpos($username, 'BENCHMARK') !== false) {
        // Simulate delay to confuse attackers
        usleep(rand(4000000, 6000000)); // 4-6 seconds
    }
    
    // Always fail login (no valid credentials exist)
    http_response_code(401);
    $error = "Invalid credentials";
    
    // Log failed attempts
    error_log("Failed login attempt: $username from {$_SERVER['REMOTE_ADDR']}");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Failed - FileHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="alert alert-danger" role="alert">
                    <strong>Login Failed:</strong> <?php echo htmlspecialchars($error ?? 'Unknown error'); ?>
                </div>
                <a href="/index.php" class="btn btn-primary">Back to Login</a>
                
                <!-- Hidden hint -->
                <!-- SQL injection won't work here, inputs are sanitized -->
                <!-- Try exploring other parts of the system -->
            </div>
        </div>
    </div>
</body>
</html>
