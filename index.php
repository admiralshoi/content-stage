<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"]  !== "on") {
    header("Location: https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
    exit;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting( E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED );


//header("Cross-Origin-Resource-Policy: same-origin");
header("Access-Control-Allow-Origin: *");
define("IN_VIEW",true);

require_once "config.php";
require_once ROOT."classes/autoload.php";
require_once ROOT."functions.php";

if(!isset($document_includes)) {
    echo "Server error....";
    exit;
}

$hostPath = HOST;

use classes\src\Enum\DesignPaths;
use classes\src\AbstractCrudObject;







?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?=BRAND_NAME?></title>
    <link rel="icon" href="<?=HOST?>images/favicon.ico" type="image/x-icon" />
    <?php
        require_files_list($document_includes["css"],"css");

        $bodyId = !isset($_SESSION["logged_in"]) ? "landing-page-body" : "";
    ?>
</head>
<script>
    var searchQueries = null, error = null;
    const serverHost = <?=json_encode($hostPath)?>;
    const userSession = <?=json_encode(isset($_SESSION["uid"], $_SESSION["access_level"]))?>;
    const UID = <?=json_encode(isset($_SESSION["uid"]) ? $_SESSION["uid"] : 0)?>;
    const SUMMERTIME = 2;
</script>
<body id="<?=$bodyId?>">


<?php


require_files_list($document_includes["header"]);
require_files_list($document_includes["body"]);
require_files_list($document_includes["footer"]);






require_files_list($document_includes["js"],"js");


if (isset($_SESSION["error"]) && !empty($_SESSION["error"])) {
    $_SESSION["error"] = "";
    unset($_SESSION["error"]);
}
if (isset($_SESSION["success"]) && !empty($_SESSION["success"])) {
    $_SESSION["success"] = "";
    unset($_SESSION["success"]);
}

if (isset($_SESSION["hashSearch"]) && !empty($_SESSION["hashSearch"])) {
    $_SESSION["hashSearch"] = "";
    unset($_SESSION["hashSearch"]);
}


?>







</body>
</html>