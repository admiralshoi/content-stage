<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW")) {
    require $_SERVER["DOCUMENT_ROOT"]."/includes/html/global_utility/404.php";
    exit;
}


use classes\src\AbstractCrudObject;
use classes\src\Fields\page_settings\Pages;

function getPage() {
    $pages = new Pages();
    $crud = new AbstractCrudObject();
    $userHandler = $crud->user();

    if(!isset($_GET["page"]) && !isset($_SESSION["uid"])) return $pages->pageContent("landing_page");
    else if(isset($_SESSION["uid"]) && !$crud->isAdmin()  && !isset($_SESSION["guest"]) && (!$userHandler->setCompleteRegistrationIfComplete() || $userHandler->integrationUnderway())) {
        if(!isset($_GET["page"]) || $_GET["page"] !== "integrations") header("location: ?page=integrations");
        return $pages->pageContent("integrations");
    }
    else if(!isset($_GET["page"]) && isset($_SESSION["uid"])) return $pages->pageContent("home");
    else if(isset($_GET["page"]) && !empty($_GET["page"])) {
        if(!$pages->pageSettings($_GET["page"])) return $pages->pageContent("404");
        else {
            $page = $pages->pageSettings($_GET["page"]);
            if($page["logged_in"] === true && !isset($_SESSION["logged_in"])) return $pages->pageContent("404");
            elseif($page["logged_in"] === false && isset($_SESSION["logged_in"])) return $pages->pageContent("404");
            else {
                $access_level = isset($_SESSION["access_level"]) ? $_SESSION["access_level"] : 0;
                if(!empty($page["access_level"]) && !in_array($access_level,$page["access_level"])) return $pages->pageContent("404");
                return $pages->pageContent($_GET["page"]);
            }
        }
    }
    return "";
}

function getPageContentPages($page) {
    return (new Pages())->innerPages($page);
}

function switchPageContent() {
    $pages = new Pages();
    $default = "integrations";
    $userRoles = (new AbstractCrudObject())->userRoles();

    if(isset($_GET["page"]) && !empty($_GET["page"])) return $pages->innerPagesContent($_GET["page"],$default);
    elseif(isset($_SESSION["access_level"])) {
        $userId = (isset($_GET["uid"]) && !empty($_GET["uid"])) ? (int)$_GET["uid"] : (int)$_SESSION["uid"];
        $userHandler = (new AbstractCrudObject())->user();
        $userHandler->get($userId);
        $role = $userRoles->name($userHandler->accessLevel());

        if($role === "creator") $page = "creator_dashboard";
        elseif($role === "brand") $page = "brand_dashboard";
        elseif($role === "admin" || $role === "system_admin") $page = "admin_dashboard";
        else $page = $default;
        return $pages->innerPagesContent($page, array($default));
    }
    return $default;


}

function sideBarHeaders($loggedIn) {
    $pages = new Pages();
    $access_level = isset($_SESSION["access_level"]) ? $_SESSION["access_level"] : 1;

    return $pages->sideBarMenuAccess($loggedIn,$access_level);
}

function sideBarLinks($barName) {
    $pages = new Pages();
    return $pages->sideBarMenuLinks($barName);
}







