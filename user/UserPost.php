<?php
require_once __DIR__ . "/../core/CSRF.php";
require_once __DIR__ . "/../core/Database.php";
class UserPost
{
    private $db;
    public $csrf;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->csrf = new CSRF();
    }

    // Index method for showing posts by the logged-in user
    public function index()
    {
        if (!isset($_SESSION['user'])) {
            return []; // or handle unauthorized access
        }

        $user_id = $_SESSION['user']['id'];

        $query = "
        SELECT posts.*, users.username, categories.name AS category_name
        FROM posts
        LEFT JOIN users ON posts.user_id = users.id
        LEFT JOIN categories ON posts.category_id = categories.id
        WHERE posts.user_id = :user_id
        ORDER BY posts.created_at DESC
    ";

        $stmt = $this->db->prepare($query);
        $stmt->execute(['user_id' => $user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Categories method
    public function getCategories()
    {
        $stmt = $this->db->query("SELECT id, name FROM categories ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create method
    public function create()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $category_id = trim($_POST['category_id']);
            $csrf_token = trim($_POST['csrf_token']);

            if (!isset($_SESSION['user'])) {
                $_SESSION['error'] = "You must be logged in to create a post.";
                return;
            }
            $user_id = $_SESSION['user']['id'];

            // Basic validations
            if (!$this->csrf->validate($csrf_token)) {
                $_SESSION['error'] = "Invalid CSRF token.";
                return;
            }

            if (empty($title) || empty($content)) {
                $_SESSION['error'] = "Title and content are required.";
                return;
            }

            // Handle image upload
            $image_name = null;
            if (!empty($_FILES['image']['name'])) {
                $upload_dir = __DIR__ . '/../uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $original_name = basename($_FILES['image']['name']);
                $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($extension, $allowed)) {
                    $image_name = uniqid('post_', true) . '.' . $extension;
                    $target_path = $upload_dir . $image_name;

                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                        $_SESSION['error'] = "Failed to upload image.";
                        return;
                    }
                } else {
                    $_SESSION['error'] = "Only JPG, PNG, and GIF files are allowed.";
                    return;
                }
            }


            // Insert post into database
            $query = "INSERT INTO posts (user_id, category_id,title, content, image) VALUES (:user_id, :category_id, :title, :content, :image)";
            $stmt = $this->db->prepare($query);
            $executed = $stmt->execute([
                'user_id' => $user_id,
                'category_id' => $category_id,
                'title' => $title,
                'content' => $content,
                'image' => $image_name
            ]);


            if ($executed) {
                $_SESSION['success'] = "Post account created successfully.";
                $this->csrf->refresh();
                header("Location: /PHPBlog/view/userposts/index.php");
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
        $stmt = $this->db->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Update method
    public function update()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $id = (int) $_POST['id'];
            $title = trim($_POST['title']);
            $content = trim($_POST['content']);
            $category_id = trim($_POST['category_id']);
            $csrf_token = trim($_POST['csrf_token']);
            $user_id = $_SESSION['user']['id'] ?? null;

            // Validate CSRF token
            if (!$this->csrf->validate($csrf_token)) {
                $_SESSION['error'] = "Invalid CSRF token.";
                return;
            }

            // Basic validation
            if (empty($title) || empty($content) || empty($category_id)) {
                $_SESSION['error'] = "Title, content, and category are required.";
                return;
            }

            if (!$user_id) {
                $_SESSION['error'] = "Unauthorized action.";
                return;
            }

            // Default to old image if no new upload
            $existingPost = $this->edit($id);
            $image_name = $existingPost['image'];

            // Handle new image upload
            if (!empty($_FILES['image']['name'])) {
                // Remove old image
                $old_image_path = __DIR__ . '/../uploads/' . $existingPost['image'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
                $upload_dir = __DIR__ . '/../uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $original_name = basename($_FILES['image']['name']);
                $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($extension, $allowed)) {
                    $image_name = uniqid('post_', true) . '.' . $extension;
                    $target_path = $upload_dir . $image_name;

                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                        $_SESSION['error'] = "Failed to upload image.";
                        return;
                    }
                } else {
                    $_SESSION['error'] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
                    return;
                }
            }

            // Update the post
            $stmt = $this->db->prepare("
            UPDATE posts 
            SET image = :image, title = :title, content = :content, category_id = :category_id, user_id = :user_id 
            WHERE id = :id
        ");

            $executed = $stmt->execute([
                'image' => $image_name,
                'title' => $title,
                'content' => $content,
                'category_id' => $category_id,
                'user_id' => $user_id,
                'id' => $id
            ]);

            if ($executed) {
                $_SESSION['success'] = "Post updated successfully.";
                $this->csrf->refresh();
                header("Location: /PHPBlog/view/userposts/index.php");
                exit;
            } else {
                $_SESSION['error'] = "Update failed. Please try again.";
                return;
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
                $_SESSION['error'] = "Invalid CSRF token.";
                header("Location: /PHPBlog/view/userposts/index.php");
                exit;
            }

            // Fetch the post to get image info
            $stmt = $this->db->prepare("SELECT * FROM posts WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$post) {
                $_SESSION['error'] = "Post not found.";
                header("Location: /PHPBlog/view/userposts/index.php");
                exit;
            }

            // Delete image if it exists
            if (!empty($post['image'])) {
                $image_path = __DIR__ . "/../uploads/" . $post['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }

            // Delete post from database
            try {
                $stmt = $this->db->prepare("DELETE FROM posts WHERE id = :id");
                $executed = $stmt->execute(['id' => $id]);

                if ($executed) {
                    $_SESSION['success'] = "Post deleted successfully.";
                } else {
                    $_SESSION['error'] = "Delete failed. Please try again.";
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
            }

            header("Location: /PHPBlog/view/userposts/index.php");
            exit;
        }
    }

}