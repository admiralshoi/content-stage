<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

if(!isset($_GET["job"]) || !in_array($_GET["job"],["HASHTAG_TRACKING", "MEDIA_UPDATE", "TAG_MENTION", "ACCOUNT_INSIGHTS"])) exit;

define("IN_VIEW",true);
require_once "../config.php";

switch ($_GET["job"]) {
    default:
        $cronFile = "";
        $token = "";
        break;
    case "HASHTAG_TRACKING":
        $cronFile = "cronJob_hashtag_tracking.php";
        $token = "3e1db3e395f7e7c351ce6b54c9931f7da9393886d1eeda1a365da6363cfbff7f";
        break;
    case "MEDIA_UPDATE":
        $cronFile = "cronJob_update_campaign_media.php";
        $token = "e8d547876f65b23af8796860b02caa775f980754590df6c63ae42b8c05ca21af";
        break;
    case "TAG_MENTION":
        $cronFile = "cronJob_tag_mentions.php";
        $token = "23dc6925c191f8278284bf69baa6594835ec007ead25bdb44b7c41e11995e008";
        break;
    case "ACCOUNT_INSIGHTS":
        $cronFile = "cronJob_account_insight.php";
        $token = "eb3f2012dbbcf2b5b9243ad2e5ab717a2b8e000246f3033423216bed4108a46f";
        break;
}

$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => HOST.$cronFile."?token=$token",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => false
));
curl_exec($ch);
curl_close($ch);








