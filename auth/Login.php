<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../core/Database.php";

class Login
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function handleLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            // Basic validations
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "Invalid email address.";
                return;
            }

            // Prepare and execute query
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) { // Check if user found
                if (!password_verify($password, $user['password'])) {
                    $_SESSION['error'] = "Password is not correct.";
                    return;
                }
                // Login success, set session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                ];

                $_SESSION['success'] = "Login successful. Redirecting...";
                header("Location: /PHPBlog/view/home.php");
                exit;
            } else {
                $_SESSION['error'] = "User not found.";
            }
        }
    }
}
?>