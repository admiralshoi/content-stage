<?php
if(!defined("IN_VIEW") || !defined("ROOT")) exit;
$activePage = $_GET["page"] ?? "";
use classes\src\Enum\DesignPaths;
use classes\src\Object\transformer\URL;
?>
<!--<div class="landingTopSection">-->
<div class="row">
    <div class="col-12 pt-4 m-0 flex-row-between flex-align-center" id="main-top-nav">
        <div>
            <a href="<?=HOST?>">
                <p class="w-150px color-dark font-25 font-weight-bold"><?=BRAND_NAME?></p>
            </a>
        </div>

        <div class="flex-row-start flex-align-center" id="main-top-nav-bar">
            <a href="<?=URL::addParam(HOST, array("page" => "login"), true)?>" class="btn btn-base">Login</a>
            <a href="<?=URL::addParam(HOST, array("page" => "signup"), true)?>" class="btn btn-white-orange btn-base">SIGN UP</a>
        </div>
    </div>
</div>


<div class="font-weight-bold"
<!--</div>-->