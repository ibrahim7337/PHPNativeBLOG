<?php
require_once __DIR__ . "/../../admin/User.php";
require_once __DIR__ . "/../../admin/Category.php";
require_once __DIR__ . "/../../admin/Post.php";
require_once __DIR__ . "/../../user/UserPost.php";
require_once __DIR__ . "/../../authorization/Home.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title><?= APP_NAME ?></title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/dashboard/">



    <!-- Bootstrap core CSS -->
    <link href="<?= ROOT ?>/assets/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <!-- Favicons -->
    <link rel="apple-touch-icon" href="<?= ROOT ?>/assets/img/favicons/apple-touch-icon.png" sizes="180x180">
    <link rel="icon" href="<?= ROOT ?>/assets/img/favicons/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="<?= ROOT ?>/assets/img/favicons/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="manifest" href="<?= ROOT ?>/assets/img/favicons/manifest.json">
    <link rel="mask-icon" href="<?= ROOT ?>/assets/img/favicons/safari-pinned-tab.svg" color="#7952b3">
    <link rel="icon" href="<?= ROOT ?>/assets/img/favicons/favicon.ico">
    <meta name="theme-color" content="#7952b3">


    <style>
    .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
    }

    @media (min-width: 768px) {
        .bd-placeholder-img-lg {
            font-size: 3.5rem;
        }
    }
    </style>


    <!-- Custom styles for this template -->
    <link href="dashboard.css" rel="stylesheet">
</head>

<body>

    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">
            <?= isset($_SESSION['user']) ? "" . htmlspecialchars($_SESSION['user']['username']) : "Home Page" ?>
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <input class="form-control form-control-dark w-100" type="text" placeholder="Search" aria-label="Search">

        <!-- inside HTML header -->
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <?php if (isset($_SESSION['user'])): ?>
                <a class="nav-link px-3" href="<?= ROOT ?>/auth/logout.php">Logout</a>
                <?php else: ?>
                <div class="d-flex">
                    <a class="nav-link px-3" href="<?= ROOT ?>/view/login.php">Login</a>
                    <a class="nav-link px-3 ms-2" href="<?= ROOT ?>/view/register.php">Register</a>
                </div>
                <?php endif; ?>


            </div>
        </div>
    </header>
