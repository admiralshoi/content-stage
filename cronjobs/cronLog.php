<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
define("IN_VIEW", true);
require_once "../config.php";


if(!isset($_SESSION["uid"],$_SESSION["access_level"]) && $_SESSION["access_level"] >= 8) {
    require ROOT ."includes/html/global_utility/404.php";
    exit;
}

if(!isset($_GET["job"]) || !in_array($_GET["job"],["HASHTAG_TRACKING", "ERROR_LOG", "MEDIA_UPDATE", "TAG_MENTION", "ACCOUNT_INSIGHTS"])) exit;

header( "refresh:60;url=".$_SERVER["REQUEST_URI"]);

if($_GET["job"] === "ERROR_LOG") {
    $logFile = ERR_LOG;
    $content = file_get_contents($logFile);
} else {

    switch ($_GET["job"]) {
        default:
            $logFile = "";
            $dateFile = "";
            break;
        case "HASHTAG_TRACKING":
            $logFile = CRON_LOGS . "cronLog_hashtag_tracking.log";
            $dateFile = CRON_LOGS . "cronDate_hashtag_tracking.log";
            break;
        case "MEDIA_UPDATE":
            $logFile = CRON_LOGS . "cronLog_media_update.log";
            $dateFile = CRON_LOGS . "cronDate_media_update.log";
            break;
        case "TAG_MENTION":
            $logFile = CRON_LOGS . "cronLog_tag_mentions.log";
            $dateFile = CRON_LOGS . "cronDate_tag_mentions.log";
            break;
        case "ACCOUNT_INSIGHTS":
            $logFile = CRON_LOGS . "cronLog_account_insights.log";
            $dateFile = CRON_LOGS . "cronDate_account_insights.log";
            break;
    }


    $content = file_exists($logFile) ? file_get_contents($logFile) : (file_exists("../" . $logFile) ? file_get_contents("../" . $logFile) : "");
    $content = empty($content) ? array() : explode(PHP_EOL, $content);
    $content = array_reverse($content);

    $dates = file_exists($dateFile) ? file_get_contents($dateFile) : (file_exists("../" . $dateFile) ? file_get_contents("../" . $dateFile) : "");
    if($dates === "") exit;
    $dates = explode(PHP_EOL,$dates);
    $lastDate = "";
    for($x = count($dates)-1; $x >= 0; $x--) {
        if(!empty($dates[$x])) {
            $lastDate = $dates[$x];
            break;
        }
    }
    $timer = translateSeconds(time()-$lastDate);
}



function translateSeconds($time) {
    $store = array("h" => 0, "m" => 0, "s" => 0); $remaining = $time;
    if($remaining >= 3600) {
        $store["h"] = floor($remaining/3600);
        $remaining = $remaining - $store["h"] * 3600;
    }
    if($remaining >= 60) {
        $store["m"] = floor($remaining/60);
        $remaining = $remaining - $store["m"] * 60;
    }
    $store["s"] = $remaining;

    return $store["h"]." Hours - ".$store["m"]." Minutes - ".$store["s"]." seconds";
}


?>
<div style="display:flex;justify-content: space-around;flex-flow: row nowrap">
    <div style='color:#727272; text-align: left;'>
    <?=implode("<br/>", $content)?>
    </div>
    <div style="text-align: left; color: #727272; font-size: 18px;">
        <?php if(isset($timer)): ?>
            <span style="font-weight: 600;">Time since last log initialization: </span><?=$timer?>
        <?php endif; ?>
    </div>
</div>
