<?php
// hash_maker.php
// 1. Change "MySecretKey123" below to your desired Master Key (make it strong!)
$mySecret = "COSTAction*1"; 

// 2. Upload this file to your site and open it in your browser.
// 3. Copy the long code starting with $2y$10$...
// 4. DELETE this file from the server immediately after using.

echo "<h1>Your Encrypted Hash:</h1>";
echo "<div style='background:#eee;padding:20px;font-family:monospace;font-size:1.5em;'>";
echo password_hash($mySecret, PASSWORD_DEFAULT);
echo "</div>";
?>