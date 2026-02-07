<?php
$password = bin2hex(random_bytes(8)) . 'A1!';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n";
