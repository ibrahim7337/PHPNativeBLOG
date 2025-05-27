<?php
require_once __DIR__ . "/../core/CSRF.php";
require_once __DIR__ . "/../core/Database.php";
class User
{
    private $db;
    public $csrf;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->csrf = new CSRF();
    }

    // Index method
    public function index()
    {
        $stmt = $this->db->prepare("SELECT * FROM users");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create method
    public function create()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            $role = trim($_POST['role']);
            $csrf_token = trim($_POST['csrf_token']);

            // Basic validations
            if (!$this->csrf->validate($csrf_token)) {
                $_SESSION['error'] = "Invalid CSRF token.";
                header("Location: edit.php?id=" . $_POST['id']);
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Invalid email address.";
                return;
            }

            if ($password !== $confirm_password) {
                $_SESSION['error'] = "Passwords do not match.";
                return;
            }

            if (strlen($password) < 6) {
                $_SESSION['error'] = "Password must be at least 6 characters.";
                return;
            }

            // Check if email exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Email already exist.";
                return;
            }

            // Hash and insert
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password , role) VALUES (:username, :email, :password , :role)");
            $executed = $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => $role,
            ]);

            if ($executed) {
                $_SESSION['success'] = "User account created successfully.";
                $this->csrf->refresh();
                header("Location: /PHPBlog/view/users/index.php");
                exit;
            } else {
                $_SESSION['error'] = "Creation failed. Please try again.";
            }
        }
    }

    // Edit method
    public function edit(int $id)
    {
        // Get user
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update method
    public function update()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $id = (int) $_POST['id'];
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            $role = trim($_POST['role']);
            $csrf_token = trim($_POST['csrf_token']);

            // CSRF check
            if (!$this->csrf->validate($csrf_token)) {
                $_SESSION['error'] = "Invalid CSRF token.";
                exit;
            }

            // Email validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Invalid email address.";
                exit;
            }

            // Check if email belongs to another user
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
            $stmt->execute(['email' => $email, 'id' => $id]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Email already exists for another user.";
                exit;
            }

            // Handle password change only if provided
            if (!empty($password)) {
                if ($password !== $confirm_password) {
                    $_SESSION['error'] = "Passwords do not match.";
                    exit;
                }

                if (strlen($password) < 6) {
                    $_SESSION['error'] = "Password must be at least 6 characters.";
                    exit;
                }

                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $this->db->prepare("UPDATE users SET username = :username, email = :email, password = :password, role = :role WHERE id = :id");
                $executed = $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'role' => $role,
                    'id' => $id
                ]);
            } else {
                // Update without changing password
                $stmt = $this->db->prepare("UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id");
                $executed = $stmt->execute([
                    'username' => $username,
                    'email' => $email,
                    'role' => $role,
                    'id' => $id
                ]);
            }

            if ($executed) {
                $_SESSION['success'] = "User updated successfully.";
                $this->csrf->refresh();
                header("Location: /PHPBlog/view/users/index.php");
                exit;
            } else {
                $_SESSION['error'] = "Update failed. Please try again.";
                exit;
            }
        }
    }

    // Delete
    public function delete($id)
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $id = (int) $_POST['id'];
            $csrf_token = $_POST['csrf_token'];

            if (!$this->csrf->validate($csrf_token)) {
                die("Invalid CSRF token.");
            }

            try {
                $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
                $executed = $stmt->execute(['id' => $id]);
                if ($executed) {
                    $_SESSION['success'] = "User deleted successfully.";
                    header("Location: /PHPBlog/view/users/index.php");
                    exit;
                } else {
                    $_SESSION['error'] = "Delete failed. Please try again.";
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
            }

        }

    }

}