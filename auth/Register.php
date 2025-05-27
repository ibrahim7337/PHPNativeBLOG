<?php
require_once __DIR__ . "/../core/Database.php";
require_once __DIR__ . "/../core/CSRF.php";

class Register
{
    private $db;
    private $csrf;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->csrf = new CSRF();
    }

    public function handleRegistration()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            $csrf_token = trim($_POST['csrf_token']);

            // Basic validations
            if (!$this->csrf->validate($csrf_token)) {
                $_SESSION['error'] = "Invalid CSRF token.";
                return;
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
                $_SESSION['error'] = "Email already registered.";
                return;
            }

            // Hash and insert
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $executed = $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword,
            ]);

            if ($executed) {
                // Get inserted user ID
                $userId = $this->db->lastInsertId();

                // Set session to log the user in
                $_SESSION['user'] = [
                    'id' => $userId,
                    'username' => $username,
                    'email' => $email,
                ];

                $_SESSION['success'] = "Registration successful. Redirecting...";
                $this->csrf->refresh();
                header("Location: /PHPBlog/view/home.php");
                exit;
            } else {
                $_SESSION['error'] = "Registration failed. Please try again.";
            }
        }
    }
}
