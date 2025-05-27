<?php
session_start();

require_once __DIR__ . "/../../admin/User.php";

$user = new User();
$user->delete($_POST['id']);
