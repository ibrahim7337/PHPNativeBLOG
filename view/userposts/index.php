<?php
require_once __DIR__ . "/../partials/header.php";

$postModel = new UserPost();
$posts = $postModel->index();

$homeModel = new Home();
$admin = $homeModel->admin();
$user = $homeModel->user();

$csrf_token = $postModel->csrf->generate();
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
                <h1 class="h2">My Posts</h1>
            </div>

            <a href="create.php" class="btn btn-primary mb-2">Create</a>

            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle">
                    <thead>
                        <tr>
                            <th></th>
                            <th>#</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td></td>
                                <td><?= htmlspecialchars($post['id']) ?></td>
                                <td>
                                    <?php if (!empty($post['image'])): ?>
                                        <img src="<?= ROOT ?>/uploads/<?= htmlspecialchars($post['image']) ?>" width="60"
                                            alt="Post Image">
                                    <?php else: ?>
                                        <img src="<?= ROOT ?>/assets/images/no_image.jpg" width="60" alt="Post Image">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($post['title']) ?></td>
                                <td><?= htmlspecialchars($post['username']) ?></td>
                                <td><?= htmlspecialchars($post['category_name']) ?></td>
                                <td><?= htmlspecialchars($post['created_at']) ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-primary">Edit</a>

                                    <form action="delete.php" method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure?')">
                                        <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . "/../partials/footer.php"; ?>