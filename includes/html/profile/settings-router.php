<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
$crud = new AbstractCrudObject();
//$userHandler = $crud->user();
//$userRoleHandler = $crud->userRoles();
//
//$accessLevel = $_SESSION["access_level"];
//$targetUserAccessLevel = (isset($_GET["uid"]) && !empty($_GET["uid"])) ? $userHandler->accessLevel($_GET["uid"]) : $accessLevel;
//
//$roleName = $userRoleHandler->name($accessLevel);
//$targetUserRoleName = $userRoleHandler->name($targetUserAccessLevel);

include_once ROOT . "includes/html/profile/settings-admin.php";
//switch ($targetUserRoleName){
//    default: include_once ROOT . "includes/html/global_utility/404.php"; break;
//    case "influencer": include_once ROOT . "includes/html/profile/settings-influencer.php"; break;
//    case "brand":
//        if($roleName === "influencer") include_once ROOT . "includes/html/profile/settings-influencer.php";
//        else include_once ROOT . "includes/html/profile/settings-brand.php";
//        break;
//    case "admin":
//    case "system_admin":
//        if(in_array($roleName, array("admin", "system_admin"))) include_once ROOT . "includes/html/profile/settings-admin.php";
//        else include_once ROOT . "includes/html/global_utility/404.php";
//
//
//}


$crud->closeConnection();

