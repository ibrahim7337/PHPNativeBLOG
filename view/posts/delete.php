<?php
session_start();

require_once __DIR__ . "/../../admin/Post.php";

$post = new Post();
$post->delete($_POST['id']);
