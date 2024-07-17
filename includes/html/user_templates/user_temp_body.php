<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
$crud = new AbstractCrudObject();

$accessLevel = $_SESSION["access_level"];
if ((isset($_GET["uid"]) && !empty($_GET["uid"]))) $accessLevel = $crud->user($_GET["uid"])->accessLevel();

$userRoles = $crud->userRoles($accessLevel);
$accessDepth = $userRoles->depth();
$roleName = $userRoles->name();

switch ($accessDepth){
    default:
        include_once ROOT . "includes/html/dashboards/marketplace.php";
//        include_once ROOT . "includes/html/global_utility/404.php";
    break;

//    case "all":
//        include_once ROOT . "includes/html/dashboards/user.php";
//    break;
//
//    case "user":
//        include_once ROOT . "includes/html/dashboards/user.php";
//    break;
}


$crud->closeConnection();

?>
