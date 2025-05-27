<?php
require_once __DIR__ . "/../partials/header.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid post ID.";
    header("Location: index.php");
    exit;
}

require_once __DIR__ . "/../../admin/Post.php";
$postModel = new Post();
$post = $postModel->show($_GET['id']);

if (!$post) {
    $_SESSION['error'] = "Post not found.";
    header("Location: index.php");
    exit;
}
?>

<div class="container mt-5">
    <a href="<?= ROOT ?>/view/home.php" class="btn btn-secondary mb-3">← Back to Posts</a>

    <div class="card">
        <img src="<?= !empty($post['image']) ? ROOT . '/uploads/' . htmlspecialchars($post['image']) : ROOT . '/assets/images/no_image.jpg' ?>"
            class="card-img-top" alt="Post Image">
        <div class="card-body">
            <h2 class="card-title"><?= htmlspecialchars($post['title']) ?></h2>
            <p class="text-muted">
                By <strong><?= htmlspecialchars($post['username']) ?></strong>
                in <em><?= htmlspecialchars($post['category_name']) ?></em> |
                <?= htmlspecialchars($post['created_at']) ?>
            </p>
            <p class="card-text mt-4"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/../partials/footer.php"; ?>