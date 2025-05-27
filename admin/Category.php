<?php
require_once __DIR__ . "/../core/CSRF.php";
require_once __DIR__ . "/../core/Database.php";
class Category
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
        $stmt = $this->db->prepare("SELECT * FROM categories");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create method
    public function create()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $csrf_token = trim($_POST['csrf_token']);

            if (!$this->csrf->validate($csrf_token)) {
                $_SESSION['error'] = "Invalid CSRF token.";
                header("Location: create.php");
                exit;
            }

            $stmt = $this->db->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
            $executed = $stmt->execute([
                'name' => $name,
                'description' => $description,
            ]);

            if ($executed) {
                $_SESSION['success'] = "Category created successfully.";
                $this->csrf->refresh();
                header("Location: /PHPBlog/view/categories/index.php");
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
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Update method
    public function update()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $id = (int) $_POST['id'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $csrf_token = trim($_POST['csrf_token']);

            if (!$this->csrf->validate($csrf_token)) {
                $_SESSION['error'] = "Invalid CSRF token.";
                header("Location: edit.php?id=$id");
                exit;
            }

            $stmt = $this->db->prepare("UPDATE categories SET name = :name, description = :description WHERE id = :id");
            $executed = $stmt->execute([
                'id' => $id,
                'name' => $name,
                'description' => $description
            ]);

            if ($executed) {
                $_SESSION['success'] = "Category updated successfully.";
                $this->csrf->refresh();
                header("Location: /PHPBlog/view/categories/index.php");
                exit;
            } else {
                $_SESSION['error'] = "Update failed. Please try again.";
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
                $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
                $executed = $stmt->execute(['id' => $id]);
                if ($executed) {
                    $_SESSION['success'] = "User deleted successfully.";
                    header("Location: /PHPBlog/view/categories/index.php");
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