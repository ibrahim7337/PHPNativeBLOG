<?php

require_once __DIR__ . "/../admin/UserAPI.php";

$userAPI = new UserAPI();
$method = $_SERVER['REQUEST_METHOD'];
$uri = explode("/", trim($_SERVER['REQUEST_URI'], '/'));
$id = end($uri);
$input = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case 'GET':
        if (is_numeric($id)) {
            echo json_encode($userAPI->get($id));
        } else {
            echo json_encode($userAPI->getAll());
        }
        break;

    case 'POST':
        echo json_encode($userAPI->create($input));
        break;

    case 'PUT':
        echo json_encode($userAPI->update($id, $input));
        break;

    case 'DELETE':
        echo json_encode($userAPI->delete($id));
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}