<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Manual run: https://app.contentstage.de/cronJob_update_campaign_media.php?token=e8d547876f65b23af8796860b02caa775f980754590df6c63ae42b8c05ca21af
//cronjob handler run: https://app.contentstage.de/cronjobs/cronCaller.php?job=MEDIA_UPDATE

$token = "e8d547876f65b23af8796860b02caa775f980754590df6c63ae42b8c05ca21af";

if(!isset($_GET["token"]) || $_GET["token"] !== $token) exit;

define("IN_VIEW",true);
require_once "config.php";
require_once ROOT."classes/autoload.php";

use classes\src\AbstractCrudObject;
use classes\src\Object\CronWorker as Worker;

$crud = new AbstractCrudObject();
$worker = new Worker($crud,"media_update");

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

    $requestHandler = $crud->cronRequestHandler();
    $media = $requestHandler->getCreatorMediaToUpdate($worker);

    $worker->log("Found " . count($media) . " media to update.");
    $worker->log("Proceeding...");

    if(!empty($media)) $requestHandler->queryCampaignMedias($media, $worker);

    $worker->log("Finished running media updates");

    $worker->end();
} catch (\Exception $exception) {

}


$crud->closeConnection();
ini_set('max_execution_time', '150');
set_time_limit(150);