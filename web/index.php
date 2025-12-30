<html>
<head><title>Login</title><style>body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; } h1 { text-align: center; color: #333; } form { max-width: 300px; margin: 50px auto; padding: 20px; background: white; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); } input { display: block; width: calc(100% - 22px); margin-bottom: 10px; padding: 10px; border: 1px solid #ccc; border-radius: 3px; } input[type="submit"] { background: #007bff; color: white; border: none; cursor: pointer; width: 100%; } input[type="submit"]:hover { background: #0056b3; }</style></head>
<body>
<h1>Login</h1>
<?php
if(isset($_POST['login'])) {
    $db = new SQLite3('database.db');
    $username = $_POST['username'];
    $password = $_POST['password'];
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $db->query($query);
    if($result->fetchArray()) {
        echo "<p>Login successful</p>";
    } else {
        echo "<p>Login failed</p>";
    }
}
?>
<form method="post">
Username: <input name="username"><br>
Password: <input name="password"><br>
<input type="submit" name="login" value="Login">
</form>
<p><a href="search.php">Search Users</a></p>
</body>
</html>
