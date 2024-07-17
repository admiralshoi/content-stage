<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();


if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
    // you want to allow, and if so:
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

define("IN_VIEW", true);
require_once "config.php";
require_once ROOT . "classes/autoload.php";

use classes\src\Object\WebHook;
use classes\src\AbstractCrudObject;


if(isset($_SERVER["REQUEST_METHOD"]) && (!empty($_POST) || !empty($_GET))) {
    echo request_handler($_SERVER["REQUEST_METHOD"] === "POST" ? $_POST : $_GET);
    exit;
}
elseif(!empty(file_get_contents("php://input"))) {
    $input = json_decode(file_get_contents("php://input"), true);
    file_put_contents("testLogs/" . date("Ymd") . ".txt",json_encode($input) . PHP_EOL, 8 | 2);
    echo request_handler($input);
    exit;
}

echo "Unknown request!";


function request_handler($request) {
    file_put_contents(TESTLOGS . "hook.json", json_encode($request, JSON_PRETTY_PRINT));
    $crud = new AbstractCrudObject();
    if(!is_array($request)) $request = json_decode($request,true);
    if(!(array_key_exists("hub_challenge",$request) || (array_key_exists("object",$request) && $request["object"] === "instagram"))) {
        $crud->closeConnection();
        return json_encode(array("error" => "Unknown request"));
    }

    $reqType = array_key_exists("hub_challenge",$request) ? "hub_challenge" : "hook";
    $hookHandler = new WebHook($crud,$reqType);

    $requestResponse = $hookHandler->handle($request);
    $crud->closeConnection();

    return $requestResponse;
}


