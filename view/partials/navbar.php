<?php
require_once __DIR__ . "/../../authorization/home.php";
$homeModel = new Home();
$admin = $homeModel->admin();
$user = $homeModel->user();
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?= ROOT ?>/view/home.php">
                    <span data-feather="home"></span>
                    Home
                </a>
            </li>
            <?php if ($admin): ?>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">
                        <span data-feather="home"></span>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= ROOT ?>/view/users/index.php">
                        <span data-feather="users"></span>
                        Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= ROOT ?>/view/categories/index.php">
                        <span data-feather="categories"></span>
                        Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= ROOT ?>/view/posts/index.php">
                        <span data-feather="posts"></span>
                        Posts
                    </a>
                </li>
            <?php elseif ($user): ?>


            <?php endif; ?>
        </ul>
    </div>
</nav>