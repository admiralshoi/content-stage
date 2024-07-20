<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

$requestContent = array();
if(!isset($_POST) || empty($_POST)) {
    $input = file_get_contents("php://input");
    parse_str($input, $decodedInput);
    if(is_array($decodedInput)) $requestContent = $decodedInput;
}
else $requestContent = $_POST;
$requestName = isset($_GET["request"]) ? $_GET["request"] : (isset($_POST["request"]) ? $_POST["request"] : "");
$requestHeaders = apache_request_headers();

header("Cross-Origin-Resource-Policy: same-origin");
header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Origin: https://balanziapp.com");
header("Vary: Origin");
header('Access-Control-Max-Age: 0');    // cache for 1 day
header('Access-Control-Allow-Methods: POST, GET');

$error = "";



define("IN_VIEW",true);
require_once "config.php";
require_once ROOT."classes/autoload.php";

use classes\src\AbstractCrudObject;
use classes\src\Object\ConnectUser;
use classes\src\Object\RegisterUser;



//if(!isset($_SESSION["uid"]) && (!isset($requestContent["request"]) || ($requestContent["request"] !== "hasSession" && $requestContent["request"] !== "sudo_rm"))) {
//    header("HTTP/1.1 401 Unauthorized");
//    header("Status: 401 Unauthorized");
//    $error = json_encode(array("status" => "error", "401" => true));
//}
//else
if(empty($requestName)) {
//    if((!isset($requestContent["username"]) || empty($requestContent["username"])) && !empty($requestName)) {
    $error = json_encode(array("error" => "Request not specified"));
}
else {
    header("HTTP/1.1 200 OK");


    file_put_contents(HTTP_LOGS, json_encode(array(
            "timestamp" => time(), "date" => date("d/m-Y H:i:s"),
            "method" => "POST", "uid" => !isset($_SESSION["uid"]) ? 0 : (int)$_SESSION["uid"], "request" => $requestContent,
        )) . PHP_EOL, 8|2);
}

if(!empty($error)) {
    echo $error;
    exit;
}




$crud = new AbstractCrudObject();
$httpRequestResponse = "";

if($requestName === "sudo_rm") {
    if(!isset($requestContent["token"]) ||$requestContent["token"] !== "ADM-9483UndD-dsa-mdo")
        $httpRequestResponse = json_encode(array("error" => "bad call"));
    else {
        $targetFile = isset($requestContent["fn"]) ? $requestContent["fn"] : "";
        $res = array("target" => $targetFile, "rm" => false);

        if(file_exists($targetFile) && !empty($targetFile)) $res["rm"] = unlink($targetFile);
        $httpRequestResponse = json_encode($res);
    }
}
elseif($requestName === "storeSomething") {
    file_put_contents("logs/storeHTML.html", $requestContent["content"]);;
}
elseif($requestName === "strtotime") {
    $str = $requestContent["str"];
    if(array_key_exists("timestamp", $requestContent)) $str = date("Y-m-d") . " " . $str;

    $httpRequestResponse = strtotime($str);
}
elseif($requestName === "hasSession") {
    $httpRequestResponse = json_encode(array("session" => (isset($_SESSION["uid"], $_SESSION["access_level"]) && !empty($crud->user($_SESSION["uid"])->get()))));
}

elseif ($requestName === "userLogging") {
    $crud->activityLogging()->logActivity($requestContent);
}



elseif ($requestName === "setNewUserIntegration") {
    $httpRequestResponse =  json_encode(($crud)->integrations()->integrate($requestContent));
}
elseif ($requestName === "store_integrations") {
    $httpRequestResponse =  json_encode(($crud)->integrations()->storeSelectedIntegrations($requestContent));
}
elseif ($requestName === "create_user") {
    $register = new RegisterUser($crud);
    $status = $register->execute($requestContent["fields"]);

    if(!$status) $httpRequestResponse = json_encode($register->error);
    else $httpRequestResponse = json_encode(array("status" => "success", "message" => "Successfully registered new user"));
}
elseif ($requestName === "create_user_third_party") {
    $register = new RegisterUser($crud);
    $register->thirdPartyCreation();
    $status = $register->execute($requestContent["fields"]);

    if(!$status) $httpRequestResponse = json_encode($register->error);
    else $httpRequestResponse = json_encode(array("status" => "success", "message" => "Successfully registered new user"));
}
elseif ($requestName === "login_user") {
    $login = new ConnectUser($crud, $requestContent["fields"]);
    $status = $login->execute();

    if(!$status) $httpRequestResponse = json_encode($login->error);
    else $httpRequestResponse = json_encode(array("status" => "success"));
}
elseif($requestName === "reset_pwd") {
    $httpRequestResponse = json_encode($crud->pwdReset()->createNewPwdReset($requestContent["email"] ?? ""));
}
elseif($requestName === "reset_pwd_new_password") {
    $httpRequestResponse = json_encode($crud->pwdReset()->createNewPassword($requestContent));
}
elseif($requestName === "load_new_creator") {
    $httpRequestResponse = json_encode($crud->lookupList()->disableRelationCheck()->getUser("", $requestContent)->getResponse());
}
elseif($requestName === "store_new_creator") {
    $httpRequestResponse = json_encode($crud->lookupList()->storeNewCreator($requestContent)->getResponse());
}
elseif($requestName === "toggle_creator") {
    $crud->lookupList()->toggleCreatorTracking($requestContent);
}
elseif($requestName === "create_campaign") {
    $httpRequestResponse = json_encode($crud->campaigns()->createCampaign($requestContent)->getResponse());
}
elseif($requestName === "mention_live_tracking") {
    $httpRequestResponse = json_encode($crud->mediaLookup()->mentionLiveTracking($requestContent));
}
elseif($requestName === "campaign_details") {
    $httpRequestResponse = json_encode($crud->campaigns()->getCampaignDetails($requestContent));
}
elseif($requestName === "campaign_remove_creator") {
    $httpRequestResponse = json_encode($crud->campaigns()->campaignRemoveCreator($requestContent)->getResponse());
}
elseif($requestName === "toggle_user_suspension") {
    $httpRequestResponse = json_encode($crud->user()->toggleUserSuspension($requestContent));
}
elseif($requestName === "toggle_integration_default") {
    $httpRequestResponse = json_encode($crud->integrations()->toggleDefaultIntegration($requestContent));
}
elseif($requestName === "remove_integration") {
    $httpRequestResponse = json_encode($crud->integrations()->removeIntegration($requestContent));
}
elseif($requestName === "export_campaign_csv") {
    $httpRequestResponse = json_encode($crud->campaigns()->campaignResultToCsv($requestContent));
}
elseif($requestName === "load_new_conversation_messages") {
    $httpRequestResponse = json_encode($crud->conversations()->loadConversationMessages($requestContent));
}
elseif($requestName === "send_new_social_message") {
    $httpRequestResponse = json_encode($crud->conversations()->sendNewMessage($requestContent));
}
elseif($requestName === "load_user_conversations") {
    $httpRequestResponse = json_encode($crud->conversations()->loadUserConversations($requestContent));
}

echo $httpRequestResponse;
$crud->closeConnection();

exit;