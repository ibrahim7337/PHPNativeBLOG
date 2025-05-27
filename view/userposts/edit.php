<?php
require_once __DIR__ . "/../partials/header.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['error'] = "Invalid post ID.";
    header("Location: index.php");
    exit;
}

$postModel = new UserPost();

// Handle update if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postModel->update(); // This handles validation, upload, and update
}

// Load post data
$csrf_token = $postModel->csrf->generate();
$post = $postModel->edit($id);
$categories = $postModel->getCategories();
?>

<div class="container-fluid mt-4">
    <div class="row">
        <?php require_once __DIR__ . "/../partials/navbar.php"; ?>

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
                <h1 class="h2">Edit Post</h1>
                <a href="<?= ROOT ?>/view/posts/index.php" class="btn btn-primary">Back</a>
            </div>

            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="id" value="<?= htmlspecialchars($post['id']) ?>">

                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" name="title" id="title" class="form-control"
                        value="<?= htmlspecialchars($post['title']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <textarea name="content" id="content" rows="6" class="form-control"
                        required><?= htmlspecialchars($post['content']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Image (leave blank to keep current)</label>
                    <input type="file" name="image" id="image" class="form-control">
                    <?php if (!empty($post['image'])): ?>
                        <div class="mt-2">
                            <img src="<?= ROOT ?>/uploads/<?= htmlspecialchars($post['image']) ?>" alt="Current Image"
                                style="max-width: 150px;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $post['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Update Post</button>
            </form>
        </main>
    </div>
</div>

<?php require_once __DIR__ . "/../partials/footer.php"; ?>