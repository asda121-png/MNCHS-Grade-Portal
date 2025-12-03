<?php
// generate_hash.php
$password = "password123";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "<h3>Fresh Hash for 'password123':</h3>";
echo "<textarea style='width:100%;height:100px;'>$hash</textarea>";
echo "<hr>";
echo "Use this EXACT hash in SQL below.";
?>