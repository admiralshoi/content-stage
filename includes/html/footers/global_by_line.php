<?php
if(!defined("IN_VIEW") || !defined("ROOT")) exit;
use classes\src\Enum\DesignPaths;
use classes\src\Object\transformer\URL;
?>

<div id="landing-footer">
    <div class="flex-row-between flex-wrap">
<!--        <div class="flex-row-start flex-align-center mt-2">-->
            <img src="<?=LOGO_ICON_WHITE?>" class="font-40 font-weight-bold w-150px mt-2" />
<!--        </div>-->

        <div class="flex-row-start flex-wrap">
            <div class="flex-col-start flex-wrap pl-2 pr-2 ml-1 mr-1 mt-2">
                <p class="font-20 font-weight-bold">Company</p>
                <a class="font-14 mt-2" href="<?=HOST?>privacy-policy.php" target="_blank">Privacy Policy</a>
                <a class="font-14 mt-2" href="<?=HOST?>terms-of-use.php" target="_blank">Terms of Use</a>
            </div>
            <div class="flex-col-start flex-wrap pl-2 pr-2 ml-1 mr-1 mt-2">
                <p class="font-20 font-weight-bold">Support</p>
                <a class="font-14 mt-2" href="mailto:support@contentstage.de">Support@contentstage.de</a>
            </div>
        </div>
    </div>
</div>