<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;
use classes\src\Fields\Fields;
use classes\src\Object\transformer\URL;

$p_content = getPageContentPages("user_page");


foreach($p_content["body"] as $k => $v) {
    if($v === "PAGE_SWITCH") {
        $p_content["body"] = array_merge(
            $p_content["body"],
            switchPageContent()
        );
        break;
    }
}

$pageContentList = new Fields($p_content);
$pageContent = $pageContentList->getFields();


?>



<div class="page-wrapper">
    <div class="flex-row-between flex-align-center pl-2 pr-2 mobileOnlyFlex">
        <div class="square-50">
            <i class="font-30 text-gray mdi mdi-menu " id="leftSidebarOpenBtn"></i>
        </div>
        <div class="flex-row-end flex-align-center">
            <div class="pr-2 border-right">
                <a href="<?=HOST?>" class="noReaction color-orange-dark hover-underline">Home</a>
            </div>
            <div class="pl-2">
                <a href="<?=URl::addParam(HOST, array("logout" => ""), true);?>" class="noReaction color-orange-dark hover-underline">Logout</a>
            </div>
        </div>
    </div>
    <?php


    require_files_list($pageContent["body"]);
    require_files_list($pageContent["footer"]);



    ?>

</div>
