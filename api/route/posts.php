<?php
require_once __DIR__ . "/../admin/PostAPI.php";

$postAPI = new PostAPI();
$method = $_SERVER['REQUEST_METHOD'];
$uri = explode("/", trim($_SERVER['REQUEST_URI'], '/'));
$id = is_numeric(end($uri)) ? (int) end($uri) : null;

// Use method override (_method) when uploading files
//PHP does not populate $_FILES and $_POST for PUT, PATCH, or DELETE methods.
// So, we send a POST request, but include a special field like _method=PUT.

$actualMethod = $_POST['_method'] ?? $method;

switch ($actualMethod) {
    case 'GET':
        echo json_encode($id ? $postAPI->get($id) : $postAPI->getAll());
        break;

    case 'POST':
        echo json_encode($postAPI->create($_POST, $_FILES));
        break;

    case 'PUT': // To work with image using POST method
        if ($id) {
            echo json_encode($postAPI->update($id, $_POST, $_FILES));
        } else {
            echo json_encode(['error' => 'ID required for update']);
        }
        break;

    case 'DELETE':
        echo json_encode($id ? $postAPI->delete($id) : ['error' => 'ID required for deletion']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}