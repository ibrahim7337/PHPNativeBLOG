<?php
require_once __DIR__ . "/../partials/header.php";

$categoryModel = new Category();
$csrf_token = $categoryModel->csrf->generate();
$categoryModel->create();
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
                <h1 class="h2">Create Category</h1>
                <a href="<?= ROOT ?>/view/categories/index.php" class="btn btn-primary">Back</a>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea type="text" class="form-control" id="description" name="description" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Create</button>
            </form>
        </main>
    </div>
</div>

<?php require_once __DIR__ . "/../partials/footer.php"; ?>