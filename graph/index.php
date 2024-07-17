<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"]  !== "on") {
//    header("Location: https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
define("IN_VIEW", true);

require_once "../config.php";
require_once ROOT . "classes/autoload.php";

use classes\src\AbstractCrudObject;
$crud = new AbstractCrudObject();

if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 0');
}


$error = null; $request = array(); $method = "";

if((isset($_SERVER["REQUEST_METHOD"]) && (!empty($_POST) || !empty($_GET))))  {
    $request = ($_SERVER["REQUEST_METHOD"] === "POST" ? $_POST : $_GET);
    $method = $_SERVER["REQUEST_METHOD"];
}
elseif(!empty(file_get_contents("php://input"))) {
    $request = json_decode(file_get_contents("php://input"), true);
    $method = "POST";
}
else {
    header("HTTP/1.1 400 Bad Request");
    header("Status: 400 Bad Request");
    $error = json_encode(array("status" => "error", "error_message" => "No request was made"));
}

file_put_contents(ROOT . "testLogs/specialLogs/graphRequests.log", json_encode(array(
    "timestamp" => time(), "date" => date("d/m-Y H:i:s"),
    "method" => $method, "request" => $request, "token" => $crud->getBearerToken(),
    "authHeaders" => $crud->getAuthorizationHeader()
    )) . PHP_EOL, 8|2);

$token = $crud->getBearerToken();
$allowedUserAgents = $allowedOrigins = "";

if($token === null) {
    header("HTTP/1.1 401 Unauthorized");
    header("Status: 401 Unauthorized");
    $error = json_encode(array("status" => "error", "error_message" => "Unauthorized request"));
} else {
    $externalRequestRow = $crud->externalAccess()->get($token);
    if(empty($externalRequestRow)) {
        header("HTTP/1.1 401 Unauthorized");
        header("Status: 401 Unauthorized");
        $error = json_encode(array("status" => "error", "error_message" => "Unauthorized token"));
    } else {
        $allowedUserAgents = strtolower($externalRequestRow["user_agent"]);
        $allowedOrigins = strtolower($externalRequestRow["origin"]);
    }
}

if(!isset($error)) {
    $headers = apache_request_headers();

    if(!is_array($headers)) {
        header("HTTP/1.1 401 Unauthorized");
        header("Status: 401 Unauthorized");
        $error = json_encode(array("status" => "error", "error_message" => "Missing authorization headers"));
    }
    elseif(!array_key_exists("User-Agent", $headers) || ($allowedUserAgents !== "*" && strtolower($headers["User-Agent"]) !== $allowedUserAgents)) {
        header("HTTP/1.1 401 Unauthorized");
        header("Status: 401 Unauthorized");
        $error = json_encode(array("status" => "error", "error_message" => "Unauthorized User-Agent: " . $headers["User-Agent"]));
    }
    elseif(!array_key_exists("Origin", $headers) || ($allowedOrigins !== "*" && strtolower($headers["Origin"]) !== $allowedOrigins)) {
        header("HTTP/1.1 401 Unauthorized");
        header("Status: 401 Unauthorized");
        $error = json_encode(array("status" => "error", "error_message" => "Unauthorized Origin"));
    }
}

//if(!$error) $error = json_encode(array("status" => "error", "error_message" => "No errors. Keeping this until we are ready to test creation for real..."));

if(!$error) header("HTTP/1.1 200 OK");
else {
    echo $error;
    $crud->closeConnection();
    exit;
}


if(!array_key_exists("request", $request)) {
    echo json_encode(array("status" => "error", "error_message" => "Request was not specified"));
    $crud->closeConnection();
    exit;
}


/*
 * REQUEST HANDLING START
 */


$httpRequestResponse = $uniqueId = null;
$fields = array();

if($method === "POST") {
    if(!array_key_exists("fields", $request)) {
        echo json_encode(array("status" => "error", "error_message" => "Expected 'fields' to be provided"));
        $crud->closeConnection();
        exit;
    }

    $fields = $request["fields"];
}



if(!isset($_SESSION["access_level"])) $_SESSION["access_level"] = 9;
if(!isset($_SESSION["id"])) $_SESSION["id"] = 1;



switch ($request["request"]) {
    default:
        $httpRequestResponse = json_encode(array("status" => "error", "error_message" => "Unknown request"));
        break;

    case "user":

        break;
}


echo $httpRequestResponse;
$crud->closeConnection();

session_destroy();
exit;