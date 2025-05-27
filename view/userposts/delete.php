<?php
session_start();

require_once __DIR__ . "/../../user/UserPost.php";

$post = new UserPost();
$post->delete($_POST['id']);
