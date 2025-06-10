<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../core/Database.php";

require_once __DIR__ . '/../middleware/auth.php';

class UserPostAPI
{
    private $db;
    private $user; // authenticated user

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->user = authenticate();
    }

    public function getAll()
    {
        // Auth
        $user_id = $this->user['id'];

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

    public function get($id)
    {
        // Auth
        $user_id = $this->user['id'];
        $stmt = $this->db->prepare("
            SELECT posts.*, users.username, categories.name AS category_name
            FROM posts
            LEFT JOIN users ON posts.user_id = users.id
            LEFT JOIN categories ON posts.category_id = categories.id
            WHERE posts.id = :id AND posts.user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute([
            'id' => $id,
            'user_id' => $user_id
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function handleImageUpload($file)
    {
        $upload_dir = __DIR__ . "/../../uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $original_name = basename($file['name']);
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($extension, $allowed)) {
            return ['error' => "Only JPG, JPEG, PNG, and GIF files are allowed."];
        }

        $image_name = uniqid('post_', true) . "." . $extension;
        $target_path = $upload_dir . $image_name;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            return ['success' => $image_name];
        } else {
            return ['error' => "Image upload failed."];
        }
    }

    public function create($data, $files)
    {
        // Auth
        $user_id = $this->user['id'];

        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');
        $category_id = (int) ($data['category_id'] ?? 0);

        if (!$title || !$content || !$category_id) {
            return ['error' => 'Missing required fields.'];
        }

        $image_name = null;
        if (!empty($files['image']['name'])) {
            $result = $this->handleImageUpload($files['image']);
            if (isset($result['error'])) {
                return ['error' => $result['error']];
            } else {
                $image_name = $result['success'];
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO posts (user_id, category_id, title, content, image) 
            VALUES (:user_id, :category_id, :title, :content, :image)
        ");

        $stmt->execute([
            'user_id' => $user_id,
            'category_id' => $category_id,
            'title' => $title,
            'content' => $content,
            'image' => $image_name
        ]);

        return ['success' => "Post created successfully."];
    }

    public function update($id, $data, $files)
    {
        // Auth
        $user_id = $this->user['id'];

        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');
        $category_id = (int) ($data['category_id'] ?? 0);
        $image = trim($data['image'] ?? null);
        var_dump($title, $content, $category_id, $user_id, $image);

        // Check if post belongs to user
        $stmt = $this->db->prepare("SELECT user_id FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$post || $post['user_id'] != $user_id) {
            return ['error' => 'Unauthorized or post not found.'];
        }

        // Check if category_id exists
        $stmt = $this->db->prepare("SELECT id FROM categories WHERE id = :id");
        $stmt->execute(['id' => $category_id]);
        if (!$stmt->fetch()) {
            return ['error' => 'Invalid category_id.'];
        }

        // Check if user_id exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        if (!$stmt->fetch()) {
            return ['error' => 'Invalid user_id.'];
        }

        $stmt = $this->db->prepare("
            UPDATE posts 
            SET title = :title, content = :content, category_id = :category_id, user_id = :user_id, image = :image
            WHERE id = :id
        ");

        $image_name = $image;
        if (!empty($files['image']['name'])) {
            $result = $this->handleImageUpload($files['image']);
            if (isset($result['error'])) {
                return ['error' => $result['error']];
            } else {
                $image_name = $result['success'];
            }
        }

        $success = $stmt->execute([
            'title' => $title,
            'content' => $content,
            'category_id' => $category_id,
            'user_id' => $user_id,
            'image' => $image_name,
            'id' => $id
        ]);

        return ['success' => "Post updated successfully."];
    }

    public function delete($id)
    {
        // Auth
        $user_id = $this->user['id'];


        // Fetch the post
        $stmt = $this->db->prepare("SELECT * FROM posts WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            return ['error' => "Invalid Post ID."];
        }

        // Check ownership
        if ($post['user_id'] != $user_id) {
            http_response_code(403);
            echo json_encode(['error' => "You are not authorized to delete this post."]);
            exit;
        }


        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = :id");
        $executed = $stmt->execute(['id' => $id]);

        if ($executed) {
            return ['success' => "Post deleted successfully."];
        } else {
            return ['error' => "Delete failed. Please try again."];
        }
    }
}