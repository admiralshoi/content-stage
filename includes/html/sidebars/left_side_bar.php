<?php
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
use classes\src\Enum\DesignPaths;
use classes\src\Object\transformer\URL;

$crud = new AbstractCrudObject();
$userHandler = $crud->user();
$userRoleHandler = $crud->userRoles();

$roleName = $userRoleHandler->name($_SESSION["access_level"]);
$accessPoint = $crud->accessPoints();


?>

<div id="sidebar" class="flex-col-between flex-align-start">
    <div class="flex-col-between h-100">
        <div class="flex-col-start">
            <div class="h-100px p-4 position-relative" id="sidebar-top-logo">
                <div class="flex-row-start flex-align-center w-100 cursor-pointer" >
                    <p class="mb-0 w-75 font-22 color-white"><?=BRAND_NAME?></p>
                    <img src="<?=LOGO_ICON_WHITE?>" class="w-10 noSelect ml-2" data-href="<?=HOST?>"/>
                </div>
                <i class="absolute-tr-5-5 mdi mdi-close font-30 text-gray hideOnDesktopBlock" id="leftSidebarCloseBtn" ></i>
            </div>

            <div class="h-100px mt-2 tabletOnlyFlex" id="sidebar-top-nav">
                <div class="h-50 w-100 flex-row-around">
                    <i class="font-30 text-gray mdi mdi-menu " id="leftSidebarOpenBtn"></i>
                </div>
            </div>

            <div id="side-bar-menu-content" style="max-height: calc(100vh - 225px); overflow-x: auto;" class="w-100">


                <?php

                $sections = sideBarHeaders(isset($_SESSION["logged_in"]));
                $accessLevel = isset($_SESSION["access_level"]) ? $_SESSION["access_level"] : 0;
                foreach ($sections as $menuOpt):
                    if(empty($menuOpt)) continue;
                    $menuItems = sideBarLinks($menuOpt["pathName"]);

                    foreach ($menuItems as $key => $items):
                        if(isset($_SESSION["guest"]) && $key !== "logout") continue;
                        if(!$accessPoint->userCanAccess($accessLevel,$items["access_level"])) continue; ?>

                        <a class="sidebar-nav-link h-75px pl-3 pr-3 pt-2 pb-2 m-0 flex-row-start flex-align-center" data-value-type="<?=$items["data-value"]?>" href="<?=$items["link"]?>"
                           data-page="?">

<!--                            <div class="square-25 img-placeholder"></div>-->
                            <i class="<?=$items['icon-class']?> font-16"></i>
                            <p class="font-16 ml-3 sidebar-text"><?=$items["title"]?></p>

                            <span id="<?=$items["data-value"]?>_notify"
                                  class="border-radius-50 p-1 ml-1 bg-orange color-white flex-row-around flex-align-center h-20px mnw-20px font-10 no-vis sidebar-notify">9</span>
                        </a>

                    <?php endforeach;
                endforeach; ?>
            </div>

        </div>

        <div class="my-2 px-4 flex-col-end" id="sidebar-brand-box">
            <p class="font-13 ">&copy; <?=BRAND_NAME?></p>
            <div class="flex-row-start flex-align-center">
                <a href="<?=HOST?>privacy-policy.php" class="mb-2 mr-2">Privacy policy</a>
                <a href="<?=HOST?>terms-of-use.php" class="mb-2 mr-2">Terms of Use</a>
            </div>
        </div>
    </div>
















</div>

