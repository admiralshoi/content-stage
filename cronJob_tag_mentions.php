<?php

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Manual run: https://app.contentstage.de/cronJob_creator_integration.php?token=23dc6925c191f8278284bf69baa6594835ec007ead25bdb44b7c41e11995e008
//cronjob handler run: https://app.contentstage.de/cronjobs/cronCaller.php?job=CREATOR_INTEGRATION

$token = "23dc6925c191f8278284bf69baa6594835ec007ead25bdb44b7c41e11995e008";

if (!isset($_GET["token"]) || $_GET["token"] !== $token) exit;

define("IN_VIEW", true);
require_once "config.php";
require_once ROOT . "classes/autoload.php";

use classes\src\AbstractCrudObject;
use classes\src\Object\CronWorker as Worker;

$crud = new AbstractCrudObject();
$worker = new Worker($crud, "tag_mentions");

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
    $worker->log("Starting tagMentionFlow...");
    $requestHandler->tagMentionFlow($worker);
    $worker->log("Finished tagMentionFlow.");



    $worker->end();
} catch (\Exception $exception) {

}


$crud->closeConnection();
ini_set('max_execution_time', '150');
set_time_limit(150);