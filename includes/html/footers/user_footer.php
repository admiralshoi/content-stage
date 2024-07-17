<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

?>
<footer id="user_footer" class="footer d-flex flex-column flex-md-row align-items-center justify-content-between">
    <div class="row w-100">
        <div class="col-sm-12 col-lg-6 mt-1">

            <div class="flex-row-start wrap flex-align-center">
                <p class="text-muted text-center text-md-left">
                    Copyright © <?=date("Y")?>&nbsp;
                    <a href="<?=$_SERVER["REQUEST_URI"]?>" target="_blank"><?=ucfirst(SITE_NAME)?>.</a>&nbsp;
                    All rights reserved
                </p>
            </div>
        </div>
        <div class="col-sm-12 col-lg-6 mt-1">
            <div class="flex-row-start wrap flex-align-center">
                <p class="text-muted text-center text-md-left">
                    <a class="" href="<?=HOST?>?page=privacy_policy">Privacy Policy</a>
                </p>
                &nbsp;&nbsp;-&nbsp;&nbsp;
                <p class="text-muted text-center text-md-left">
                    <a class="" href="<?=HOST?>?page=user_agreement">User agreement</a>
                </p>
                &nbsp;&nbsp;-&nbsp;&nbsp;
                <p class="text-muted text-center text-md-left">
                    <a class="" href="<?=HOST?>?page=FAQ">FAQ</a>
                </p>
                &nbsp;&nbsp;-&nbsp;&nbsp;
                <p class="text-muted text-center text-md-left">
                    <a class="" href="<?=HOST?>?page=video_guides">Help</a>
                </p>
            </div>
        </div>
    </div>


</footer>
