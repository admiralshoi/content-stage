<?php
if(!defined("IN_VIEW") || !defined("ROOT")) exit;
$activePage = $_GET["page"] ?? "";
use classes\src\Enum\DesignPaths;
use classes\src\Object\transformer\URL;
?>
<!--<div class="landingTopSection">-->
<div class="row">
    <div class="col-12 pt-4 m-0 flex-row-between flex-align-center" id="main-top-nav-dark">
        <div>
            <a href="<?=HOST?>">
                <div class="flex-row-start flex-align-center mt-2">
                    <p class="pb-0 font-25 color-dark"><?=BRAND_NAME?></p>
                    <img src="<?=LOGO_ICON?>" class="h-25px ml-2" />
                </div>
            </a>
        </div>

        <div class="flex-row-start flex-align-center" id="main-top-nav-bar">
            <a href="<?=URL::addParam(HOST, array("page" => "login"), true)?>" class="btn-base link-prim font-weight-bold">Login</a>
            <a href="<?=URL::addParam(HOST, array("page" => "signup"), true)?>" class="btn-prim btn-base font-weight-bold">SIGN UP</a>
        </div>
    </div>
</div>
<!--</div>-->