<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;
use classes\src\Fields\Fields;
$pageContentList = new Fields(getPageContentPages("user_template"));
$pageContent = $pageContentList->getFields();
?>


<div class="main-wrapper">
    <?php


    require_files_list($pageContent["body"]);


    ?>
</div>
