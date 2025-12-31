<?php
session_start();

// 假的安全头部
header("X-Security-Level: HIGH");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Server: SecureWebServer/2.1");

// 数据库连接
try {
    $db = new PDO('mysql:host=db;dbname=corporate_db', 'webuser', 'WebPass456!');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection error. Security team has been notified.");
}

// 搜索功能 - 存在SQL注入漏洞
$search_results = [];
$search_query = "";

if(isset($_GET['search'])) {
    $search = $_GET['search'];
    $search_query = htmlspecialchars($search);
    
    // 故意存在SQL注入漏洞
    $sql = "SELECT id, username, email, department, role FROM users WHERE 
            username LIKE '%$search%' OR 
            email LIKE '%$search%' OR 
            department LIKE '%$search%' OR 
            role LIKE '%$search%' 
            ORDER BY username LIMIT 50";
    
    try {
        $stmt = $db->query($sql);
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 记录搜索日志（假的安全监控）
        $log_sql = "INSERT INTO search_logs (search_term, ip_address, search_time) VALUES (?, ?, NOW())";
        $log_stmt = $db->prepare($log_sql);
        $log_stmt->execute([$search, $_SERVER['REMOTE_ADDR']]);
        
    } catch(PDOException $e) {
        // 故意显示一些数据库信息来误导
        $error_info = [
            'error' => 'Search error detected',
            'security_notice' => 'This incident has been logged',
            'db_info' => 'Table: users, Columns: id, username, password, email, department, role, active'
        ];
    }
}

// 获取所有部门列表（用于下拉菜单）
$departments = [];
try {
    $dept_stmt = $db->query("SELECT DISTINCT department FROM users ORDER BY department");
    $departments = $dept_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    $departments = ['IT', 'HR', 'Finance', 'Security', 'Operations'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee Directory Search - Corporate Portal</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #f0f0f0; 
            margin: 0; 
            padding: 20px;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect width="100" height="100" fill="none" stroke="%23ccc" stroke-width="0.5"/></svg>');
        }
        .container { 
            max-width: 800px; 
            margin: 20px auto; 
            padding: 30px; 
            background: white; 
            border-radius: 10px; 
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border: 2px solid #28a745;
        }
        h1 { 
            text-align: center; 
            color: #28a745; 
            margin-bottom: 10px;
        }
        .security-badge {
            text-align: center;
            background: #28a745;
            color: white;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .search-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        input, select { 
            display: inline-block; 
            margin: 5px; 
            padding: 10px; 
            border: 2px solid #ccc; 
            border-radius: 5px; 
            font-size: 14px;
        }
        input:focus, select:focus {
            border-color: #28a745;
            outline: none;
            box-shadow: 0 0 5px rgba(40,167,69,0.3);
        }
        input[type="submit"] { 
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white; 
            border: none; 
            cursor: pointer; 
            padding: 12px 20px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        input[type="submit"]:hover { 
            background: linear-gradient(135deg, #1e7e34, #155724);
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .results {
            margin-top: 20px;
        }
        .result-item {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #28a745;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .result-item h3 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        .result-details {
            font-size: 14px;
            color: #6c757d;
        }
        .result-details span {
            display: inline-block;
            margin-right: 15px;
            padding: 3px 8px;
            background: #e9ecef;
            border-radius: 3px;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
            font-size: 13px;
        }
        .advanced-search {
            margin-top: 15px;
            padding: 15px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
        }
        .advanced-search h4 {
            margin-top: 0;
            color: #856404;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
        .search-stats {
            background: #e7f3ff;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 12px;
            color: #0056b3;
        }
        .db-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border: 1px solid #f5c6cb;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="security-badge">
            🔍 EMPLOYEE DIRECTORY - AUTHORIZED ACCESS ONLY 🔍
        </div>
        <h1>Corporate Employee Directory</h1>
        
        <div class="info">
            <strong>Directory Search System:</strong><br>
            • Search by name, email, department, or role<br>
            • All searches are logged for security purposes<br>
            • Maximum 50 results displayed per search<br>
            • For assistance, contact HR department
        </div>
        
        <div class="search-form">
            <form method="get">
                <input type="text" name="search" placeholder="Enter search term..." value="<?php echo htmlspecialchars($search_query); ?>" style="width: 300px;">
                <select name="department">
                    <option value="">All Departments</option>
                    <?php foreach($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" value="SEARCH EMPLOYEES">
            </form>
            
            <div class="advanced-search">
                <h4>Advanced Search Options:</h4>
                <form method="get">
                    <input type="text" name="username" placeholder="Username contains...">
                    <input type="text" name="email" placeholder="Email contains...">
                    <input type="text" name="role" placeholder="Role contains...">
                    <input type="hidden" name="advanced" value="1">
                    <input type="submit" value="ADVANCED SEARCH">
                </form>
            </div>
        </div>
        
        <?php if(isset($error_info)): ?>
            <div class="db-error">
                <strong>Database Error Detected:</strong><br>
                <?php foreach($error_info as $key => $value): ?>
                    <?php echo htmlspecialchars($key); ?>: <?php echo htmlspecialchars($value); ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if($search_results): ?>
            <div class="search-stats">
                Found <?php echo count($search_results); ?> employee(s) matching your search criteria.
                Search performed at: <?php echo date('Y-m-d H:i:s'); ?>
            </div>
            
            <div class="results">
                <?php foreach($search_results as $employee): ?>
                    <div class="result-item">
                        <h3><?php echo htmlspecialchars($employee['username']); ?></h3>
                        <div class="result-details">
                            <span>📧 <?php echo htmlspecialchars($employee['email']); ?></span>
                            <span>🏢 <?php echo htmlspecialchars($employee['department']); ?></span>
                            <span>👤 <?php echo htmlspecialchars($employee['role']); ?></span>
                            <span>🆔 ID: <?php echo htmlspecialchars($employee['id']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif(isset($_GET['search'])): ?>
            <div class="info">
                No employees found matching your search criteria. Please try different search terms.
            </div>
        <?php endif; ?>
        
        <div class="footer">
            <a href="index.php">← Back to Login</a> | 
            <a href="fileupload.php">File Upload Portal</a> | 
            <a href="admin.php">System Administration</a>
        </div>
    </div>
</body>
</html>