<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'config.php';
include_once 'Todo.php';

$database = new Database();
$db = $database->getConnection();

if ($db === null) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed"));
    exit();
}

$todo = new Todo($db);

$request_method = $_SERVER["REQUEST_METHOD"];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Log method and path for debugging
file_put_contents('php://stderr', "METHOD: $request_method, PATH: $path\n", FILE_APPEND);

// Health check endpoint
if ($path === '/health' || $path === '/backend/health') {
    http_response_code(200);
    echo json_encode(array("status" => "healthy", "message" => "Backend is running"));
    exit();
}

switch ($request_method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get single todo
            $todo->id = $_GET['id'];
            if ($todo->readOne()) {
                $todo_arr = array(
                    "id" => $todo->id,
                    "title" => $todo->title,
                    "description" => $todo->description,
                    "completed" => $todo->completed === 't' || $todo->completed === true,
                    "created_at" => $todo->created_at,
                    "updated_at" => $todo->updated_at
                );
                http_response_code(200);
                echo json_encode($todo_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Todo not found"));
            }
        } else {
            // Get all todos
            $stmt = $todo->readAll();
            $num = $stmt->rowCount();

            if ($num > 0) {
                $todos_arr = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $todo_item = array(
                        "id" => $id,
                        "title" => $title,
                        "description" => $description,
                        "completed" => $completed === 't' || $completed === true,
                        "created_at" => $created_at,
                        "updated_at" => $updated_at
                    );
                    array_push($todos_arr, $todo_item);
                }
                http_response_code(200);
                echo json_encode($todos_arr);
            } else {
                http_response_code(200);
                echo json_encode(array());
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->title)) {
            $todo->title = $data->title;
            $todo->description = isset($data->description) ? $data->description : '';
            $todo->completed = isset($data->completed) ? $data->completed : false;

            if ($todo->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Todo was created successfully"));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create todo"));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create todo. Data is incomplete"));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id) && !empty($data->title)) {
            $todo->id = $data->id;
            $todo->title = $data->title;
            $todo->description = isset($data->description) ? $data->description : '';
            $todo->completed = isset($data->completed) ? $data->completed : false;

            if ($todo->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Todo was updated successfully"));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update todo"));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update todo. Data is incomplete"));
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $todo->id = $_GET['id'];

            if ($todo->delete()) {
                http_response_code(200);
                echo json_encode(array("message" => "Todo was deleted successfully"));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete todo"));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete todo. ID is required"));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}
