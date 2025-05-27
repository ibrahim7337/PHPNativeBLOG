<?php
require_once __DIR__ . "/partials/header.php";

$postModel = new Post();
$posts = $postModel->index();

$homeModel = new Home();
$admin = $homeModel->admin();
$user = $homeModel->user();

$csrf_token = $postModel->csrf->generate();
?>

<div class="container-fluid mt-4">
    <div class="row">
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">

                <!-- Navbar  -->
                <ul class="nav flex-column">
                    <?php if ($admin): ?>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="dashboard.php">
                                <span data-feather="home"></span>
                                Dashboard
                            </a>
                        </li>
                    <?php elseif ($user): ?>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= ROOT ?>/view/userposts/index.php">
                                <span data-feather="posts"></span>
                                My Posts
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>

        <!-- Main content  -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Posts</h1>
            </div>

            <div class="row">

                <?php foreach ($posts as $post): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <img src="<?= !empty($post['image']) ? ROOT . '/uploads/' . htmlspecialchars($post['image']) : ROOT . '/assets/images/no_image.jpg' ?>"
                                class="card-img-top" alt="Post Image">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
                                <p class="card-text text-muted small mb-1">By <?= htmlspecialchars($post['username']) ?> in
                                    <?= htmlspecialchars($post['category_name']) ?>
                                </p>
                                <p class="card-text small mb-2"><i class="bi bi-clock"></i>
                                    <?= htmlspecialchars($post['created_at']) ?></p>
                                <a href="<?= ROOT ?>/view/posts/show.php?id=<?= $post['id'] ?>"
                                    class="btn btn-sm btn-primary mt-auto">Show</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>