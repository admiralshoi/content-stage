<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
use classes\src\Enum\DesignPaths;
use classes\src\Object\transformer\URL;

$crud = new AbstractCrudObject();
$userHandler = $crud->user();
$integrationHandler = $crud->integrations();
$tiktokOauthLink = $integrationHandler->tiktokOauthLink();

$pageTitle = "Integrate";

?>
<script>
    var pageTitle = <?=json_encode($pageTitle)?>;
    var apiState = <?=json_encode((isset($_SESSION["tiktok_params"]) ? $_SESSION["tiktok_params"]["state"] : ""))?>;
</script>





<div class="row mt-5">

    <div class="col-12 col-lg-7 pt-5 pb-5 pl-3 pr-3 pl-sm-5 pr-sm-5 border-radius-tl-bl-20px border-lg-left border-lg-top border-lg-bottom  border-lg-right-0" >
        <div class="flex-col-start dataParentContainer" id="user_integrate_tiktok">
            <img class="w-150px noSelect" src="<?=HOST . DesignPaths::dark_nav_logo?>" />
            <div class="flex-col-start mt-5 ">
                <p class="font-25 font-weight-bold">Welcome! First things first...</p>
                <p class="font-16 text-gray">Connect your tiktok account</p>

                <a href="<?=$tiktokOauthLink?>" class="noReaction color-white btn-base tiktok-btn btn-base mt-4 flex-row-around w-100">
                    <div class="flex-row-start flex-align-center">
                        <img src="<?=HOST?>images/icons/tik-tok-white.svg" class="icon-lg" />
                        <p class="font-18 ml-2">Connect to Tiktok</p>
                    </div>
                </a>


<!--                            <button class="btn btn-orange-white btn-base mt-4" name="integrate_tiktok">Next</button>-->
            </div>

        </div>
    </div>

    <div class="d-none d-lg-block col-lg-5 border border-radius-tr-br-20px border-lg-left-0 bg-lighter-blue" >
        <div class="flex-col-around h-100">
            <img class="w-100" src="<?=HOST . DesignPaths::s_design?>" />

            <div class="flex-col-start flex-align-center">
                <p class="font-22 font-weight-bold text-center">Already have an account?</p>
                <p class="font-16 text-gray text-center">Sign in and continue your Simplif Experience  </p>
                <a href="<?=URL::addParam(HOST, array("page" => "login"), true)?>" class="btn btn-white-orange btn-base mt-4 mxw-150px">Sign in</a>
            </div>

            <img class="w-100" src="<?=HOST . DesignPaths::s_design?>" />
        </div>
    </div>


</div>

