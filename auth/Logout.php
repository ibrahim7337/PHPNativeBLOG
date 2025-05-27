<?php
session_start();

// Destroy the entire session
if ($_SESSION['user']) {
    session_destroy();
}

// Redirect to home page after logout
header("Location: /PHPBlog/view/home.php");
exit;