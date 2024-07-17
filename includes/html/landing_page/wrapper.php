<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;
use classes\src\Fields\Fields;

if(!isset($_GET["page"])) $name = "landing_wrapper_login";
elseif ($_GET["page"] === "login") $name = "landing_wrapper_login";
elseif ($_GET["page"] === "reset_pwd") $name = "reset_password";
elseif ($_GET["page"] === "signup") $name = "signup";
elseif ($_GET["page"] === "privacy_policy") $name = "privacy_policy";
elseif ($_GET["page"] === "terms_of_use") $name = "terms_of_use";
else $name = "landing_wrapper_main";



$pageContentList = new Fields(getPageContentPages($name));
$pageContent = $pageContentList->getFields();
$pageTitle = SITE_NAME;
?>
<script> var pageTitle = <?=json_encode($pageTitle)?>; </script>




<div class="landing-wrapper">
    <?php

    require_files_list($pageContent["body"]);
    require_files_list($pageContent["footer"]);

    ?>
</div>
