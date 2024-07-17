<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;
use classes\src\Enum\DesignPaths;
$crud = new \classes\src\AbstractCrudObject();
?>


<div class="row mt-5 hideOnMobileBlock">
    <div class="col-12">
        <div class="flex-col-start flex-align-center" id="main-top-cover">


            <p class="font-40 font-weight-bold">Influencer Marketing Content On Demand</p>
            <p class="font-16">The all-in-one  completely FREE platform to minimize costs and maximise returns</p>

            <div class="mt-4 bg-hidden-white ">
                <img src="<?=DesignPaths::main_banner?>" class="w-100" />
            </div>

            <?php if($crud->isMobile()): ?>
            <?php else: ?>

<!--                <div class="mt-4 bg-hidden-white w-100">-->
<!--                    <video autoplay muted loop class="w-100">-->
<!--                        <source src="--><?//=DesignPaths::promo_video?><!--" type="video/mp4">-->
<!--                        Your browser does not support the video tag.-->
<!--                    </video>-->
<!--                </div>-->
            <?php endif; ?>
        </div>
    </div>
</div>
