<?php

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Manual run: https://app.contentstage.de/cronJob_account_insight.php?token=eb3f2012dbbcf2b5b9243ad2e5ab717a2b8e000246f3033423216bed4108a46f
//cronjob handler run: https://app.contentstage.de/cronjobs/cronCaller.php?job=ACCOUNT_INSIGHTS

$token = "eb3f2012dbbcf2b5b9243ad2e5ab717a2b8e000246f3033423216bed4108a46f";

if (!isset($_GET["token"]) || $_GET["token"] !== $token) exit;

define("IN_VIEW", true);
require_once "config.php";
require_once ROOT . "classes/autoload.php";

use classes\src\AbstractCrudObject;
use classes\src\Object\CronWorker as Worker;

$crud = new AbstractCrudObject();
$worker = new Worker($crud, "account_insights");

$timeOfInit = time();
$access = $worker->init($timeOfInit);
if (!$access) {
    $crud->closeConnection();
    echo "Access was not granted";
    exit;
}


ini_set('max_execution_time', '-1');
set_time_limit(-1);

try {
    $worker->log("Cron init approved");

    if (!isset($_SESSION["access_level"])) $_SESSION["access_level"] = 8;
    if (!isset($_SESSION["id"])) $_SESSION["id"] = 1;

    $requestHandler = $crud->cronRequestHandler();
    $creatorItems = $requestHandler->findCreatorsToQueryAccountAnalytics($worker);

    $worker->log("Found " . count($creatorItems) . " creators to fetch account insights and analytics from.");
    $worker->log("Proceeding...");

    if (!empty($creatorItems)) $requestHandler->queryAccountAnalytics($creatorItems, $worker);

    $worker->log("Finished running creator integrations");

    $worker->end();
} catch (\Exception $exception) {

}


$crud->closeConnection();
ini_set('max_execution_time', '150');
set_time_limit(150);