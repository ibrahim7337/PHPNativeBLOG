<?php
require_once __DIR__ . "/../partials/header.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Basic validation
if ($id <= 0) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: index.php");
    exit;
}
$userModel = new User();
$csrf_token = $userModel->csrf->generate();
$userModel->edit($id);
$userModel->update();
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
                <h1 class="h2">Edit User</h1>
                <a href="<?= ROOT ?>/view/users/index.php" class="btn btn-primary">Back</a>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <input type="hidden" name="id" value="<?= htmlspecialchars($userModel->edit($id)['id']) ?>">

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control"
                        value="<?= htmlspecialchars($userModel->edit($id)['username']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control"
                        value="<?= htmlspecialchars($userModel->edit($id)['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="user" <?= $userModel->edit($id)['role'] === 'user' ? 'selected' : '' ?>>User
                        </option>
                        <option value="admin" <?= $userModel->edit($id)['role'] === 'admin' ? 'selected' : '' ?>>Admin
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Update User</button>
            </form>
        </main>
    </div>
</div>

<?php require_once __DIR__ . "/../partials/footer.php"; ?>