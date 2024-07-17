<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!isset($_SESSION["username"]) && $_SESSION["access_level"] >= 9) {
    require $_SERVER["DOCUMENT_ROOT"]."/includes/html/global_utility/404.php";
    exit;
}
phpinfo();
