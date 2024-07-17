<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
use classes\src\Object\transformer\URL;
use classes\src\Auth\Auth;
use classes\src\Object\objects\Integrations;
$crud = new AbstractCrudObject();

$pageTitle = "Creators";

$mediaHandler = $crud->mediaLookup();
$lookupList = $crud->lookupList();
$creatorId = $crud->isCreator() ? (int)$_SESSION["uid"] : (isset($_GET["creator"]) && !empty($_GET["creator"]) ? (int)$_GET["creator"] : 0);
$campaignId = isset($_GET["campaign"]) && !empty($_GET["campaign"]) ? (int)$_GET["campaign"] : 0;
$creator = !empty($creatorId) ? $lookupList->getWithMergedData($creatorId, $campaignId) : [];



?>
    <script>
        var pageTitle = <?=json_encode($pageTitle)?>;
    </script>
    <div class="page-content position-relative" data-page="creators">

        <div class="flex-col-start">
            <p class="font-22 font-weight-medium">Creators</p>
            <p class="font-18">Currently viewing:
                <span id="creator_current_view_text">
                    <?php if(empty($creator)): ?>
                    All creators
                    <?php else: ?>
                    <a href="https://www.instagram.com/<?=$creator["username"]?>" class="" target="_blank"><?=$creator["username"]?></a>
                    <?php endif; ?>
                </span>
            </p>
        </div>






        <?php if(!empty($creator)):
            include_once ROOT . "includes/html/other/display-creators.php";

        else:
            $creators = $lookupList->getByXWithMergedData(["deactivated" => 0]);
            $inactiveCreators = $lookupList->getByXWithMergedData(["deactivated" => 1]);
        ?>



        <div class="row mt-5">
            <div class="col-12">
                <div class="card mt-2">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <p class="font-18 font-weight-bold color-primary-cta">Add new creator</p>
                            </div>
                            <div class="col-12 mt-1">
                                <div class="row">
                                    <div class="col-12 col-xl-4 mt-1">
                                        <div class="flex-row-start flex-align-center flex-nowrap">
                                            <input type="text" name="new_creator_username" placeholder="@username" class="form-control mb-0" />
                                            <button class="btn-base btn-prim border-transparent ml-2" name="new_creator_load_user" style="border-radius: 3px; padding: .4rem 1rem">Load</button>
                                        </div>
                                    </div>
                                    <div class="col-12 col-xl-8 mt-1">
                                        <div class="row" id="profile_preview" style="display: none;">
                                            <div class="col-12 col-lg-6 mt-1 overflow-hidden">
                                                <img id="profile_image_preview" src="" class="mxh-200px noSelect float-right" />
                                            </div>
                                            <div class="col-12 col-lg-6 mt-1 overflow-hidden">
                                                <div class="flex-col-start">
                                                    <div class="border-bottom pb-2">
                                                        <p class="font-18" id="username_preview"></p>
                                                    </div>

                                                    <p class="font-16 mt-3">Is this the creator you're looking for?</p>
                                                    <button name="store_new_creator" class="btn-base btn-ter float-right mt-2">Yes, store creator</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>







        <div class="row mt-5">
            <div class="col-12">
                <div class="card mt-2">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <p class="font-18 font-weight-bold color-primary-cta">Creators</p>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="table-responsive">

                                    <table class="w-100 table-padding">
                                        <thead>
                                        <tr class="color-primary-dark bg-wrapper">
                                            <th class="font-weight-normal"></th>
                                            <th class="font-weight-normal">Username</th>
                                            <th class="font-weight-normal">Engagement</th>
                                            <th class="font-weight-normal">Followers</th>
                                            <th class="font-weight-normal hideOnMobileTableCell">Last update</th>
                                            <?php if($crud->isAdmin()): ?>
                                                <th class="font-weight-normal">Tracking</th>
                                            <?php endif; ?>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(!empty($creators)):
                                                foreach (array_reverse($creators) as $creator): ?>
                                                    <tr>
                                                        <td>
                                                            <div class="flex-row-around">
                                                                <div class="flex-col-start flex-align-center">
                                                                    <div class="position-relative">
                                                                        <img src="<?=resolveImportUrl($creator["profile_picture"])?>" class="noSelect square-50 border-radius-50" />
                                                                        <?php if((int)$creator["api"]): ?>
                                                                            <div style="position:absolute; top: -10px; right: -15px;">
                                                                                <i class="mdi mdi-check-decagram font-25 " style="color: #1c96df"></i>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <a href="<?=URL::addParam(HOST, ["page" => "creators", "creator" => $creator["id"]], true)?>"
                                                               class="link-prim text-center mt-2"><?=$creator["username"]?></a>
                                                        </td>
                                                        <td><?=number_format($creator["engagement_rate"], 2, ",", ".")?>%</td>
                                                        <td><?=$creator["followers_count"]?></td>
                                                        <td class="hideOnMobileTableCell"><?=date("M d, H:i", $creator["updated_at"])?></td>

                                                        <?php if($crud->isAdmin()): ?>
                                                            <td>
                                                                <p class="color-red hover-underline cursor-pointer" data-toggle-creator="<?=$creator["id"]?>">Disable tracking</p>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach;
                                            endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>






        <div class="row mt-5">
            <div class="col-12">
                <div class="card mt-2">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <p class="font-18 font-weight-bold color-primary-cta">Inactive creators</p>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="table-responsive">

                                    <table class="w-100 table-padding">
                                        <thead>
                                        <tr class="color-primary-dark bg-wrapper">
                                            <th class="font-weight-normal"></th>
                                            <th class="font-weight-normal">Username</th>
                                            <th class="font-weight-normal">Name</th>
                                            <th class="font-weight-normal">Followers</th>
                                            <th class="font-weight-normal hideOnMobileTableCell">Last update</th>
                                            <?php if($crud->isAdmin()): ?>
                                                <th class="font-weight-normal">Tracking</th>
                                            <?php endif; ?>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if(!empty($inactiveCreators)):
                                            foreach (array_reverse($inactiveCreators) as $creator): ?>
                                                <tr>
                                                    <td>
                                                        <div class="flex-row-around">
                                                            <div class="flex-col-start flex-align-center">
                                                                <div class="position-relative">
                                                                    <img src="<?=resolveImportUrl($creator["profile_picture"])?>" class="noSelect square-50 border-radius-50" />
                                                                    <?php if((int)$inactiveCreators["api"]): ?>
                                                                    <div style="position:absolute; top: -10px; right: -15px;">
                                                                        <i class="mdi mdi-check-decagram font-25 " style="color: #1c96df"></i>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><?=$creator["username"]?></td>
                                                    <td><?=ucfirst($creator["full_name"])?></td>
                                                    <td><?=$creator["followers_count"]?></td>
                                                    <td><?=date("M d, H:i", $creator["updated_at"])?></td>
                                                    <?php if($crud->isAdmin()): ?>
                                                        <td>
                                                            <p class="color-green hover-underline cursor-pointer" data-toggle-creator="<?=$creator["id"]?>">Enable tracking</p>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach;
                                        endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php endif; ?>


    </div>

<?php
$crud->closeConnection();