<?php
session_start();

require_once __DIR__ . "/../../admin/Category.php";

$category = new Category();
$category->delete($_POST['id']);
