<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
use classes\src\Object\transformer\Titles;
use classes\src\Object\transformer\URL;

$crud = new AbstractCrudObject();

$newCookieStatus = null;
$newCookieMessage = null;
$cookieManager = new \classes\src\Object\CookieManager($crud);
if(isset($_GET["set-default"]) && $cookieManager->changeDefault($_GET["set-default"])) $cookieManager->reloadCookies();

if(isset($_POST["set_new_cookie"])) {
    $newCookieResponse = $cookieManager->createNewCookie($_POST);
    $newCookieStatus = $newCookieResponse["status"];
    if($newCookieStatus === "error") $newCookieMessage = $newCookieResponse["error"]["message"];
    else {
        $newCookieMessage = $newCookieResponse["data"];
        $cookieManager->reloadCookies();
    }


}

$cookies = $cookieManager->getCookieDisplayList();
$pageTitle = "Cookie manager";

?>
<script>
    var pageTitle = <?=json_encode($pageTitle)?>;
</script>
<div class="page-content position-relative" data-page="dashboard_user">

    <div class="row">
        <div class="col-12">


            <p class="font-22 font-weight-bold">Cookie manager</p>

            <div class="card border-radius-10px mt-4">
                <div class="card-body">
                    <p class="font-18 font-weight-bold color-primary-cta">Set new cookie</p>

                    <?php if(!is_null($newCookieStatus)): ?>
                        <div class="alert alert-<?=$newCookieStatus === "success" ? "success" : "danger"?> eNotice mt-2 mb-2" role="alert"><?=$newCookieMessage?></div>
                    <?php endif; ?>

                    <form method="post" action="<?=URL::addParam(HOST, ["page" => "cookie-manager"], true)?>" name="cookie_form" class="row">
                        <div class="col-12 col-md-6 col-xl-3 mt-1">
                            <div class="flex-col-start flex-align-start">
                                <p class="font-16">Name</p>
                                <input type="text" name="name" placeholder="EG. the name of the instagram account" class="form-control" required />
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3 mt-1">
                            <div class="flex-col-start flex-align-start">
                                <p class="font-16">Cookie</p>
                                <input type="text" name="cookie" placeholder="Paste the cookie here" class="form-control" required/>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-xl-2 mt-1">
                            <div class="flex-col-start flex-align-start">
                                <p class="font-16">Max error streak</p>
                                <input type="number" min="1" value="20" name="max_error_streak" placeholder="Defaults to 20" class="form-control" required/>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-xl-2 mt-1">
                            <div class="flex-col-start flex-align-center">
                                <p class="font-16">Default ?</p>
                                <input type="checkbox" name="default_cookie" class="form-control" />
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-xl-2 mt-1">
                            <div class="flex-col-end flex-align-end">
                                <p style="visibility: hidden">Create</p>
                                <button class=" btn-base btn-prim border-transparent" name="set_new_cookie" value="1">Set</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>

            <?php foreach ($cookies as $type => $cookieList): ?>
                <div class="card border-radius-10px mt-4">
                    <div class="card-body">
                        <p class="font-18 font-weight-bold color-primary-cta"><?=ucfirst($type)?> cookies</p>
                        <div class="table-responsive container-fluid overflow-x-hidden mt-3">
                            <table class="table table-hover dataTable prettyTable plainDataTable" id="cookie_<?=$type?>_table"
                                   data-pagination-limit="30" data-sorting-col="1" data-sorting-order="desc">
                                <thead>
                                <tr>
                                    <th class="hideOnMobileTableCell">Name</th>
                                    <th class="hideOnMobileTableCell">Total uses</th>
                                    <th>Successes</th>
                                    <th>Failures</th>
                                    <th>Error streak</th>
                                    <th>Max streak</th>
                                    <th>Default</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if(!empty($cookieList)):
                                    foreach ($cookieList as $cookie): ?>

                                        <tr>
                                            <td><?=$cookie["name"]?></td>
                                            <td><?=$cookie["total_uses"]?></td>
                                            <td><?=$cookie["successes"]?></td>
                                            <td><?=$cookie["failures"]?></td>
                                            <td><?=$cookie["error_streak"]?></td>
                                            <td><?=$cookie["max_error_streak"]?></td>
                                            <td class="<?=$cookie["default_cookie"] ? "text-success font-weight-bold" : "No"?>">
                                                <div class="flex-row-start flex-align-center">
                                                    <p><?=$cookie["default_cookie"] ? "Yes" : "No"?></p>
                                                    <?php if($type === "valid" && (int)$cookie["default_cookie"] === 0): ?>
                                                        <i class="mdi mdi-key-change ml-2 cursor-pointer hover-color-blue" title="Make default"
                                                            data-href="<?=URL::addParam($_SERVER["REQUEST_URI"], ["set-default" => $cookie["id"]])?>"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>







        </div>
    </div>

</div>

<?php
$crud->closeConnection();