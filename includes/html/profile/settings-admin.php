<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
use classes\src\Object\transformer\Titles;
use classes\src\Object\transformer\URL;

$crud = new AbstractCrudObject();

$userId = (int)$_SESSION["uid"];
$userHandler = $crud->user($userId);
$metaHandler = $crud->appMeta();

$pageTitle = "Settings";



//$hook = new \classes\src\Object\WebHook($crud, "hook");
//$hookContent = json_decode(file_get_contents(TESTLOGS . "specialLogs/hook-1693837646.json"), true);
//$hook->simulateWebhook($hookContent, null);





?>
    <script>
        var pageTitle = <?=json_encode($pageTitle)?>;
    </script>
    <div class="page-content position-relative" data-page="admin_settings">

        <div class="row">
            <div class="col-12">

                <div class="row mt-4">
                    <div class="col-12 col-sm-6 mt-1 flex-col-around">
                        <p class="font-25 font-weight-bold">Settings</p>
                    </div>
                    <div class="col-12 col-sm-6 mt-1 text-left text-sm-right flex-col-around">
                        <form method="get" action="?logout" class="">
                            <button class="btn-base btn-prim border-0" name="logout">Sign out</button>
                        </form>
                    </div>

                    <?php if($crud->isAdmin()): ?>

                        <div class="col-12 mt-5">
                            <div class="card border-radius-10px">
                                <div class="card-body">



                                    <div class="row border-bottom pb-2 mt-3">
                                        <div class="col-12">
                                            <p class="font-weight-bold font-16 color-primary-cta">Edit pages</p>
                                        </div>
                                        <div class="col-md-6 col-12 mt-1">
                                            <a href="<?=URL::addParam(HOST, array("page" => "edit_page", "t" => "pp"), true)?>"
                                               class="noReaction font-16 link-prim flex-row-start flex-align-center hover-underline">
                                                <i class="mdi mdi-folder-edit mr-2"></i>
                                                Edit Privacy Policy
                                            </a>
                                        </div>
                                        <div class="col-md-6 col-12 mt-1">
                                            <a href="<?=URL::addParam(HOST, array("page" => "edit_page", "t" => "tou"), true)?>"
                                               class="noReaction font-16 link-prim flex-row-start flex-align-center hover-underline">
                                                <i class="mdi mdi-folder-edit mr-2"></i>
                                                Edit Terms of Use
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-5">
                            <div class="card border-radius-10px">
                                <div class="card-body">

                                    <div class="row border-bottom pb-2 mt-3">
                                        <div class="col-12">
                                            <p class="font-weight-bold font-16 color-primary-cta">Cron logs</p>
                                        </div>
                                        <div class="col mt-1">
                                            <a href="<?=URL::addParam(HOST . "cronjobs/cronLog.php", array("job" => "ACCOUNT_ANALYTICS"), true)?>"
                                               target="_blank"
                                               class="noReaction font-16 link-prim flex-row-start flex-align-center hover-underline">
                                                <i class="mdi mdi-folder-edit mr-2"></i>
                                                Account analytics
                                            </a>
                                        </div>
                                        <div class="col mt-1">
                                            <a href="<?=URL::addParam(HOST . "cronjobs/cronLog.php", array("job" => "MEDIA_DISCOVERY"), true)?>"
                                               target="_blank"
                                               class="noReaction font-16 link-prim flex-row-start flex-align-center hover-underline">
                                                <i class="mdi mdi-folder-edit mr-2"></i>
                                                Media discovery
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php  endif; ?>
                </div>
            </div>
        </div>

    </div>

<?php
$crud->closeConnection();