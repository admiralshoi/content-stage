<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

if(!defined("IN_VIEW")) {
    require $_SERVER["DOCUMENT_ROOT"]."/includes/html/global_utility/404.php";
    exit;
}

require_once ROOT."class_settings.php";
use classes\src\Fields\Fields;
use classes\src\Object\ConnectUser;


function request_handler($request) {
    if(isset($request["logout"])) {
        $_SESSION = array();
        session_destroy();
        header("location:?");
    }
}

$guestShareBool = isset($_GET["page"], $_GET["campaign"], $_GET["share"]);
if(isset($_SESSION["guest"], $_SESSION["guest_share"]) && $_SESSION["guest"]) {
    if(!$guestShareBool || $_SERVER["QUERY_STRING"] !== $_SESSION["guest_share"])
        header("Location: " . HOST . "?" . $_SESSION["guest_share"]);
}

if($guestShareBool) {
    if($_GET["page"] === "campaigns" && !empty($_GET["campaign"]) && !empty($_GET["share"]) && !isset($_SESSION["logged_in"])) {
        $crud = new \classes\src\AbstractCrudObject();
        $login = new ConnectUser($crud, ["email" => "guest", "password" => 123456]);
        $login->guest = true;
        $status = $login->execute();

        $campaignHandler = $crud->campaigns();
        if(empty($campaignHandler->getByX(["id" => $_GET["campaign"], "share_token" => $_GET["share"]])))
            request_handler(["logout" => true]);
        else {
            $_SESSION["guest_share"] = $_SERVER["QUERY_STRING"];
        }
    }
}






$page = getPage();



$fields = new Fields($page);
$document_includes = $fields->getFields();

$error = $searchQueries = "";
if(isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST" || $_SERVER["REQUEST_METHOD"] === "GET") {
    request_handler($_SERVER["REQUEST_METHOD"] === "POST" ? $_POST : $_GET);
}

function prettyPrint(mixed $content): void {
    if(is_array($content)  || is_object($content)) $content = json_encode($content, JSON_PRETTY_PRINT);
    echo "<pre>$content</pre>";
}


function backslashToSlash(string &$str): void { $str = str_replace("\\", "/", $str); }
function resolveImportUrl(string $url, bool $includeVersion = false): string {
    $path = $url;
    if(array_key_exists("scheme", parse_url($url))) backslashToSlash($path);
    else $path = HOST . $url;
    return $includeVersion ? "$path?version=" . PLATFORM_VERSION : $path;
}

function require_files_list(array $list,$type="html"){
    if(count($list) > 0) {
        foreach ($list as $file) {
            if(file_exists(ROOT.$file) || array_key_exists("scheme", parse_url($file))) {

                switch ($type) {
                    case "html":
                        require_once ROOT.$file;
                        break;
                    case "css":
                        echo "<link rel='stylesheet' href='" . resolveImportUrl($file, true) . "'>";
                        break;
                    case "js":
                        echo "<script src='" . resolveImportUrl($file, true) . "' defer></script>";
                        break;
                    default: echo "";
                }
            }
        }
    }
}


function cleanHttpResponseHeaders(array $headers): array {
    if(empty($headers)) return [];
    $collection = [];
    foreach ($headers as $header) {
        $split = explode(":", $header);
        $key = array_shift($split);
        $collection[$key] = trim(implode(":",$split));
    }

    if(array_key_exists("Content-Disposition", $collection)) {
        $disposition = $collection["Content-Disposition"];
        if(str_contains($disposition, "filename=")) {
            $split = explode(";", $disposition);
            foreach ($split as $str) {
                if(!str_contains($str, "filename=")) continue;
                $keyPair = explode("=", $disposition);
                if(count($keyPair) > 1) {
                    $collection["filename"] = $keyPair[1];
                    break;
                }
            }
        }
    }

    file_put_contents(TESTLOGS . "filehead.json", json_encode($collection, JSON_PRETTY_PRINT));
    return $collection;
}
