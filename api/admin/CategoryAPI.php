<?php

header("Content-Type: application/json");
require_once __DIR__ . "/../../core/Database.php";

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../middleware/role.php';
class CategoryAPI
{
    private $db;
    private $user; // authenticated user
    private $checkAdmin; // authorization

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->user = authenticate();
        $this->checkAdmin = admin($this->user['id']);
    }

    public function getAll()
    {
        // Auth
        $this->user['id'];

        // Authorization
        $this->checkAdmin;

        $stmt = $this->db->query("SELECT * FROM categories");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get($id)
    {
        // Auth
        $this->user['id'];

        // Authorization
        $this->checkAdmin;

        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create method
    public function create($data)
    {
        // Auth
        $this->user['id'];

        // Authorization
        $this->checkAdmin;

        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');

        $errors = [];

        if (!$name) {
            $errors[] = "Name is required.";
        }
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $stmt = $this->db->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
        $stmt->execute([
            'name' => $name,
            'description' => $description,
        ]);

        return ['success' => "Category created successfully."];

    }

    // Update method
    public function update($id, $data)
    {
        // Auth
        $this->user['id'];

        // Authorization
        $this->checkAdmin;

        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');

        $stmt = $this->db->prepare("UPDATE categories SET name = :name, description = :description WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'description' => $description
        ]);

        return ['success' => "Category updated successfully."];

    }


    // Delete
    public function delete($id)
    {
        // Auth
        $this->user['id'];

        // Authorization
        $this->checkAdmin;

        // Check if category exists
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        if (!$stmt->fetch()) {
            return ['error' => "Invalid category ID."];
        }
        $stmt = $this->db->prepare("DELETE FROM categories WHERE id = :id");
        $executed = $stmt->execute(['id' => $id]);

        if ($executed) {
            return ['success' => "Category deleted successfully."];
        } else {
            return ['error' => "Delete failed. Please try again."];
        }

    }
}