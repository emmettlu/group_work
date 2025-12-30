<html>
<head><title>Search</title><style>body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; } h1 { text-align: center; color: #333; } form { max-width: 300px; margin: 50px auto; padding: 20px; background: white; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); } input { display: block; width: calc(100% - 22px); margin-bottom: 10px; padding: 10px; border: 1px solid #ccc; border-radius: 3px; } input[type="submit"] { background: #007bff; color: white; border: none; cursor: pointer; width: 100%; } input[type="submit"]:hover { background: #0056b3; }</style></head>
<body>
<h1>Search Users</h1>
<form method="get">
Search: <input name="search">
<input type="submit">
</form>
<?php
if(isset($_GET['search'])) {
    $db = new SQLite3('database.db');
    $search = $_GET['search'];
    $query = "SELECT username FROM users WHERE username LIKE '%$search%'";
    $result = $db->query($query);
    while($row = $result->fetchArray()) {
        echo "<p>" . $row[0] . "</p>";
    }
}
?>
</body>
</html>
