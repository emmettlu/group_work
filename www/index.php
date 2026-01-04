<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FileHub - Enterprise File Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- 
        FileHub v2.3 - Migration Notes
        - Old file system migrated to network share
        - Legacy PHP code needs refactoring
        - TODO: Remove debug endpoints before production
        - Database contains historical records from old system
        - Check /backup/ for configuration archives
    -->
    
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="bi bi-folder2"></i> FileHub</h3>
                        <small>Enterprise File Management System v2.3</small>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-4">Login</h5>
                        <form action="/login.php" method="POST" id="loginForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <hr>
                        <div class="text-center">
                            <small class="text-muted">
                                Forgot password? Contact IT Department<br>
                                System Status: <span class="text-success">●</span> All services operational
                            </small>
                        </div>
                    </div>
                    <div class="card-footer text-muted text-center">
                        <small>&copy; 2023 FileHub Inc. | Powered by Apache/PHP/MySQL</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
