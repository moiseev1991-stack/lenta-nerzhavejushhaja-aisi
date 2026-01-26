<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_destroy();
require __DIR__ . '/../helpers.php';
redirect('/admin/login');
?>
