<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Manual run: https://app.contentstage.de/cronJob_hashtag_tracking.php?token=3e1db3e395f7e7c351ce6b54c9931f7da9393886d1eeda1a365da6363cfbff7f
//cronjob handler run: https://app.contentstage.de/cronjobs/cronCaller.php?job=HASHTAG_TRACKING

$token = "3e1db3e395f7e7c351ce6b54c9931f7da9393886d1eeda1a365da6363cfbff7f";

if(!isset($_GET["token"]) || $_GET["token"] !== $token) exit;

define("IN_VIEW",true);
require_once "config.php";
require_once ROOT."classes/autoload.php";

use classes\src\AbstractCrudObject;
use classes\src\Object\CronWorker as Worker;

$crud = new AbstractCrudObject();
$worker = new Worker($crud,"hashtag_tracking");

$timeOfInit = time();
$access = $worker->init($timeOfInit);
if(!$access) {
    $crud->closeConnection();
    echo "Access was not granted";
    exit;
}


ini_set('max_execution_time', '-1');
set_time_limit(-1);

try {
    $worker->log("Cron init approved");

    if(!isset($_SESSION["access_level"])) $_SESSION["access_level"] = 8;
    if(!isset($_SESSION["id"])) $_SESSION["id"] = 1;

    $campaignHandler = $crud->campaigns();
    $hashtagsToTrack = $campaignHandler->findHashtagsToTrack($worker);

    $worker->log("Found " . count($hashtagsToTrack) . " hashtag(s) to track.");
    $worker->log("Proceeding...");

    if(!empty($hashtagsToTrack)) $campaignHandler->hashtagTracking($hashtagsToTrack, $worker);

    $worker->log("Finished running hashtag tracking");

    $worker->end();
} catch (\Exception $exception) {

}


$crud->closeConnection();
ini_set('max_execution_time', '150');
set_time_limit(150);