<?php
require_once __DIR__ . "/../core/CSRF.php";
require_once __DIR__ . "/../core/Database.php";
class Home
{
    private $db;
    public $csrf;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->csrf = new CSRF();
    }

    // Admin method
    public function admin()
    {
        if (!isset($_SESSION['user'])) {
            return;
        }
        $user_id = $_SESSION['user']['id'];
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user['role'] === "admin";
    }

    // User method
    public function user()
    {
        if (!isset($_SESSION['user'])) {
            return;
        }
        $user_id = $_SESSION['user']['id'];
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user['role'] === "user";
    }


}