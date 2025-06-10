<?php

require_once __DIR__ . "/../admin/CategoryAPI.php";

$categoryAPI = new CategoryAPI();
$method = $_SERVER['REQUEST_METHOD'];
$uri = explode("/", trim($_SERVER['REQUEST_URI'], '/'));
$id = end($uri);
$input = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case 'GET':
        if (is_numeric($id)) {
            echo json_encode($categoryAPI->get($id));
        } else {
            echo json_encode($categoryAPI->getAll());
        }
        break;

    case 'POST':
        echo json_encode($categoryAPI->create($input));
        break;

    case 'PUT':
        echo json_encode($categoryAPI->update($id, $input));
        break;

    case 'DELETE':
        echo json_encode($categoryAPI->delete($id));
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}