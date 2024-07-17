<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
use classes\src\Object\transformer\URL;
use classes\src\Auth\Auth;
use classes\src\Object\objects\Integrations;
$crud = new AbstractCrudObject();

$pageTitle = "Campaigns";


$lookupList = $crud->lookupList();
$campaignHandler = $crud->campaigns();
$campaignRelations = $crud->campaignRelations();
$mediaHandler = $crud->mediaLookup();
$dataHandler = $crud->dataHandler();



$campaignId = isset($_GET["campaign"]) && !empty($_GET["campaign"]) ? (int)$_GET["campaign"] : 0;
$campaign = !empty($campaignId) ? $campaignHandler->get($campaignId) : [];

if(!empty($campaign)) {
    if($crud->isBrand() && $campaign["owned_by"] !== $_SESSION["uid"]) $campaign = [];
    elseif($crud->isCreator() && !isset($_SESSION["guest"]) && empty($campaignRelations->getByX(["campaign_id" => $campaign["id"], "creator_id" => $crud->creatorId()]))) $campaign = [];
    $shareLink = URL::addParam(HOST, ["page" => "campaigns", "campaign" => $campaignId, "share" => $campaign["share_token"]], true);
//    $shareLink = "#";
}





?>
    <script>
        var pageTitle = <?=json_encode($pageTitle)?>;
    </script>
    <div class="page-content position-relative" data-page="campaigns">

        <div class="flex-row-between flex-align-center flex-wrap copyContainer">
            <div class="flex-col-start mt-1">
                    <p class="font-22 font-weight-medium">Campaigns</p>


            </div>
            <?php if(!$crud->isCreator() && empty($campaign)): ?>
                <button class="btn-base btn-prim mt-1 noSelect" name="toggle_campaign_creation_view">Create new campaign</button>
            <?php endif; ?>
            <?php if(!empty($campaign) && !$crud->isCreator()): ?>
                <div class="btn-prim mnw-100px pl-3 pr-3 pt-2 pb-2 border-radius-20px color-white font-18 copyBtn text-center cursor-pointer">
                    Share
                    <div class="copyElement hidden"><?=$shareLink?></div>
                </div>
            <?php endif; ?>
        </div>



        <?php if(!empty($campaign)):
            $relationParam = ["campaign_id" => $campaignId];
            if($crud->isCreator()) $relationParam["creator_id"] = $crud->creatorId();
            $relations = $campaignRelations->getByX($relationParam, ["creator_id"]);

            $contentParam = ["campaign_id" => $campaignId];
            if($crud->isCreator()) $contentParam["lookup_id"] = $crud->creatorId();

            $posts = $mediaHandler->getByX(array_merge($contentParam, ["type" => "post"]));
            $stories = $mediaHandler->getByX(array_merge($contentParam, ["type" => "story"]));

            $normalPosts = array_values(array_filter($posts, function ($item) { return !in_array($item["media_type"], ["REELS", "VIDEO"]); }));
            $reelsPost = array_values(array_filter($posts, function ($item) { return in_array($item["media_type"], ["REELS", "VIDEO"]); }));

            $creatorCount = count($relations);
            $totalPosts = count($posts) + count($stories);
            $expectedPosts = (int)$campaign["ppc"] * $creatorCount;
            $postsRemaining = max($expectedPosts - $totalPosts, 0);
            $completion = $expectedPosts === 0 ? 0: min(round($totalPosts / $expectedPosts * 100,2), 100);
            $completionColor = "bg-primary-cta";
//            $completionColor = $completion < 50 ? "bg-danger" : ($completion < 75 ? "bg-warning" : "bg-primary-cta");
            $creators = $lookupList->getByX(["deactivated" => 0], ["id", "username", "followers_count"]);
            $campaignCreatorIds = array_map(function ($item) { return $item["creator_id"]; }, $relations);


            $totalFollowers = (int)array_reduce($relations, function ($initial, $item) use ($lookupList) {
                $creator = $lookupList->get($item["creator_id"]);
                return (!isset($initial) ? 0 : $initial) + (empty($creator) ? 0 : (int)$creator["followers_count"]);
            });

            $totalLikes = (int)array_reduce($posts, function ($initial, $item) {
                return (!isset($initial) ? 0 : $initial) + (int)$item["like_count"];
            });
            $commentsCount = (int)array_reduce($posts, function ($initial, $item) {
                return (!isset($initial) ? 0 : $initial) + (int)$item["comments_count"];
            });
            $totalInteractions = $totalLikes + $commentsCount;

            $totalViewCount = (int)array_reduce($posts, function ($initial, $item) {
                return (!isset($initial) ? 0 : $initial) + (!in_array($item["type"], ["REELS", "VIDEO", "STORY"]) ? 0 :
                        ((int)$item["play_count"] > 0 ? (int)$item["play_count"] : (int)$item["view_count"]));
            });


            $campaignStats = $dataHandler->fromMediaSetUserAverages(
                [
                    "media" => $posts,
                    "followers_count" => $totalFollowers
                ]
            );
            $campaignEngagement = $dataHandler->engagementRate($campaignStats);



        ?>



        <div class="row mt-2">
            <div class="col-12 mb-2" id="campaign_edit_container" style="display: none">
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <?php if(empty($creators)): ?>
                                <div class="col-12 mt-1">
                                    <p class="font-18">To update a campaign, you must first add active creators</p>
                                </div>
                            <?php else: ?>


                                <?php if(!$crud->isAdmin()): ?>
                                    <div class="col-12 col-md-4 col-xl-6 mt-1">
                                        <div class="flex-col-start">
                                            <p class="font-18">Campaign name</p>
                                            <input type="text" name="campaign_name_edit" placeholder="Eg. brandname etc." max="30" class="form-control"/>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="col-12 col-md-2 col-xl-3 mt-1">
                                        <div class="flex-col-start">
                                            <p class="font-18">Campaign name</p>
                                            <input type="text" name="campaign_name_edit" placeholder="Eg. brandname etc." max="30" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-2 col-xl-3 mt-1">
                                        <p class="font-18">Assign to</p>
                                        <select name="campaign_owner_edit" class="form-control" >
                                            <option value="" <?=(empty($campaign["owned_by"])) ? "selected" : ""?>>Not assigned</option>
                                            <?php foreach ($crud->user()->getByX(["access_level" => 2]) as $brand): ?>
                                                <option value="<?=$brand["uid"]?>" <?=($campaign["owned_by"] === $brand["uid"]) ? "selected" : ""?>>
                                                    <?=$brand["username"]?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>




                                <div class="col-12 col-md-8 col-xl-6 mt-1">
                                    <div class="flex-col-start">
                                        <p class="font-18">Dates</p>
                                        <input type="text" name="campaign_dates_edit" class="form-control DP_RANGE"/>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-3 mt-1">
                                    <div class="flex-col-start">
                                        <p class="font-18">Content types</p>
                                        <select name="post_types_edit" class="form-control">
                                            <option value="mixed">Mixed</option>
                                            <option value="reel">Reels</option>
                                            <option value="post">Posts</option>
                                            <option value="story">Stories</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-3 mt-1">
                                    <div class="flex-col-start">
                                        <p class="font-18">Content per creator</p>
                                        <input type="number" min="1" max="10" value="1" name="ppc_edit" class="form-control" />
                                    </div>
                                </div>

                                <div class="col-12 col-xl-6 mt-1">
                                    <div class="flex-col-start">
                                        <p class="font-18">Creators</p>
                                        <select name="campaign_creators_edit" class=" select2Multi" multiple="multiple"
                                                data-select2-attr='{"tags":false,"height":"20px","placeholder":"Select creators","allowClear":true}' >
                                            <?php foreach ($creators as $creator): ?>
                                                <option value="<?=$creator["id"]?>" <?=in_array($creator["id"], $campaignCreatorIds) ? "selected" : ""?>>
                                                    @<?=$creator["username"]?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-xl-3 mt-1">
                                    <div class="flex-col-start">
                                        <p class="font-18">Bulk add creators</p>
                                        <input type="text" placeholder="Comma separated list" name="creators_bulk_edit" class="form-control"
                                            title="Filling in this field will ignore newly added creators in the single Creators-field" />
                                    </div>
                                </div>


                                <div class="col-12 col-md-6 col-xl-3 mt-1">
                                    <div class="flex-col-start">
                                        <p class="font-18">Tracking</p>
                                        <select name="tracking_edit" class=" form-control">
                                            <option value="mention" <?=((int)$campaign["tracking"] === 0) ? "selected" : ""?>>Mention</option>
                                            <option value="hashtag" <?=((int)$campaign["tracking"] === 1) ? "selected" : ""?>>Hashtag (Not applicable to stories)</option>
                                            <option value="both" <?=((int)$campaign["tracking"] === 2) ? "selected" : ""?>>Hashtag & Mentions</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-2 mt-1">
                                    <div class="flex-col-start">
                                        <p class="font-18">Tracking hashtag</p>
                                        <input type="text" name="tracking_hashtag_edit" value="<?=$campaign["hashtag"]?>" class="form-control" placeholder="Tracking hashtag" />
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-4 mt-1 d-flex justify-content-end align-items-end">
                                    <div class="flex-row-end flex-align-end">
                                        <button class="btn-base btn-red border-transparent" name="edit_campaign_btn">Close</button>
                                        <button class="btn-base btn-green-white border-transparent ml-2" name="update_campaign_btn">Save & update</button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>



                    </div>
                </div>
            </div>





            <div class="col-12 col-xl-5 mt-2">
                <div class="card mt-1">
                    <div class="card-body">
                        <div class="flex-row-between">
                            <div class="flex-col-start " style="justify-content: space-between">
                                <div class="flex-col-start">
                                    <p class="font-18 ">Campaign: <?=$campaign["name"]?></p>
                                    <div class="flex-row-start flex-align-center">
                                        <?php if($crud->isAdmin() || $crud->isBrand(0, false)): ?>
                                            <p class="color-primary-cta hover-underline cursor-pointer font-italic font-16" id="edit_campaign_btn">
                                                Edit campaign <i class="mdi mdi-pencil"></i>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="flex-col-start">
                                    <p class="font-14">Content: <span class="font-weight-bold"><?=$campaign["content_type"]?></span></p>
                                    <p class="font-14">Expected posts: <span class="font-weight-bold"><?=$expectedPosts?></span></p>
                                    <p class="font-14">Remaining posts: <span class="font-weight-bold"><?=$postsRemaining?></span></p>
                                    <p class="font-14 underline-it color-primary-cta cursor-pointer campaign-csv-export">Export to csv</p>
                                </div>
                            </div>

                            <div class="flex-col-start">
                                <div class=" w-80px border border-radius-10px">
                                    <div class="bg-primary-cta border-radius-tl-tr-10px">
                                        <p class="text-center color-white font-18 text-uppercase"><?=date("M", $campaign["start"])?></p>
                                    </div>

                                    <p class="text-center font-20 font-weight-bold mt-1"><?=date("d", $campaign["start"])?></p>
                                    <p class="text-center font-16 mt-1"><?=date("Y", $campaign["start"])?></p>
                                </div>

                                <div class="w-80px border border-radius-10px mt-2">
                                    <div class="bg-primary-cta border-radius-tl-tr-10px">
                                        <p class="text-center color-white font-18 text-uppercase"><?=date("M", $campaign["end"])?></p>
                                    </div>

                                    <p class="text-center font-20 font-weight-bold mt-1"><?=date("d", $campaign["end"])?></p>
                                    <p class="text-center font-16 mt-1"><?=date("Y", $campaign["end"])?></p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div class="col-12 col-xl-7 mt-2">
                <div class="row">
<!--                    <div class="col-6 col-xl-4 mt-1">-->
<!--                        <div class="card --><?//=$completionColor?><!--">-->
<!--                            <div class="card-body">-->
<!--                                <div class="flex-row-between color-white flex-wrap">-->
<!--                                    <p class="font-14">Completion</p>-->
<!--                                    <p class="font-18 font-weight-bold ml-1">--><?//=$completion?><!--%</p>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </div>-->
                    <div class="col-6 col-xl-6 mt-1">
                        <div class="card">
                            <div class="card-body">
                                <div class="flex-row-between flex-wrap">
                                    <p class="font-14">Engagement</p>
                                    <p class="font-18 font-weight-bold ml-1"><?=$campaignEngagement?>%</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-xl-6 mt-1">
                        <div class="card">
                            <div class="card-body">
                                <div class="flex-row-between flex-wrap">
                                    <p class="font-14">Total mentions</p>
                                    <p class="font-18 font-weight-bold ml-1"><?=$totalPosts?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-xl-6 mt-1">
                        <div class="card">
                            <div class="card-body">
                                <div class="flex-row-between flex-wrap">
                                    <p class="font-14">Video views</p>
                                    <p class="font-18 font-weight-bold ml-1"><?=$totalViewCount?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-6 mt-1">
                        <div class="card">
                            <div class="card-body">
                                <div class="flex-row-between  flex-wrap">
                                    <p class="font-14 hideOnMobileBlock">Potential audience</p>
                                    <p class="font-14 mobileOnlyBlock">Pot. audience</p>
                                    <p class="font-18 font-weight-bold ml-1"><?=number_format($totalFollowers, 0, ",", ".")?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-6 mt-1">
                        <div class="card">
                            <div class="card-body">
                                <div class="flex-row-between flex-wrap">
                                    <p class="font-14">Creators</p>
                                    <p class="font-18 font-weight-bold ml-1"><?=$creatorCount?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-xl-6 mt-1">
                        <div class="card">
                            <div class="card-body">
                                <div class="flex-row-between flex-wrap">
                                    <p class="font-14">Interactions</p>
                                    <p class="font-18 font-weight-bold ml-1"><?=number_format($totalInteractions, 0 , ",", ".")?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row mt-5">
            <div class="col-12" data-switchParent data-switch-id="campaign-content"
                 data-active-btn-class="color-primary-dark border-bottom-thick-primary-dark font-weight-bold"
                 data-inactive-btn-class="color-primary-dark font-weight-normal">
                <div class="flex-row-start flex-align-center mb-3 border-bottom-gray w-100" >
                    <div class="switchViewBtn font-20 px-3 py-1 color-primary-dark border-bottom-thick-primary-dark font-weight-bold"
                         data-toggle-switch-object="content" data-switch-id="campaign-content">Content</div>
                    <?php if(!$crud->isCreator()): ?>
                        <div class="switchViewBtn font-20 px-3 py-1 color-primary-dark font-weight-normal"
                             data-toggle-switch-object="creators" data-switch-id="campaign-content">Creators</div>
                    <?php endif; ?>
                </div>

                <div class="switchViewObject" data-switch-id="campaign-content" data-switch-object-name="content" data-is-shown="true">
                    <div class="row">
                        <div class="col-12" data-switchParent data-switch-id="content-type"
                             data-active-btn-class="bg-primary-dark"
                             data-inactive-btn-class="bg-gray">
                            <div class="flex-row-start flex-align-center mb-3" >
                                <div class="switchViewBtn font-16 px-3 py-2 bg-primary-dark color-white border-radius-20px font-weight-bold mx-1"
                                     data-toggle-switch-object="posts" data-switch-id="content-type">
                                    Posts
                                    <span class="ml-2 bg-blue border-radius-10px px-2 font-14"><?=count($normalPosts)?></span>
                                </div>
                                <div class="switchViewBtn font-16 px-3 py-2 bg-gray color-white border-radius-20px font-weight-bold mx-1"
                                     data-toggle-switch-object="stories" data-switch-id="content-type">
                                    Stories
                                    <span class="ml-2 bg-blue border-radius-10px px-2 font-14"><?=count($stories)?></span>
                                </div>
                                <div class="switchViewBtn font-16 px-3 py-2 bg-gray color-white border-radius-20px font-weight-bold mx-1"
                                     data-toggle-switch-object="reels" data-switch-id="content-type">
                                    Reels
                                    <span class="ml-2 bg-blue border-radius-10px px-2 font-14"><?=count($reelsPost)?></span>
                                </div>
                            </div>

                            <div class="switchViewObject" data-switch-id="content-type" data-switch-object-name="posts" data-is-shown="true">
                                <div class="row mt-5">
                                    <div class="col-12">
                                        <div class="row">
                                            <?php if(!empty($normalPosts)):
                                                $crud->sortByKey($normalPosts, "timestamp");
                                                foreach (array_reverse($normalPosts) as $mediaItem):
                                                    $mediaItem = $mediaHandler->keyJsonEncoding($mediaItem, false);
                                                    $mediaType = $mediaItem["media_type"] === "VIDEO" ? "Reel" :
                                                        (!empty($mediaItem["carousel"]) ? "Carousel" : "Image");
                                                    $creator = $lookupList->get($mediaItem["lookup_id"], ["profile_picture", "username"])

                                                    ?>
                                                    <div class="col-12 col-md-6 col-xl-3 pb-2 pt-2 pl-2 pr-2">
                                                        <div class="flex-col-start p-0 bg-white">
                                                            <div class="w-100 h-300px overflow-hidden bg-primary-cta flex-col-around">
                                                                <img src="<?=resolveImportUrl($mediaItem["display_url"])?>" class="w-100" />
                                                            </div>
                                                            <div class="pl-3 pr-3 pb-3 pt-2 border-bottom h-150px overflow-hidden">
                                                                <div class="flex-row-start flex-align-center">
                                                                    <img src="<?=resolveImportUrl($creator["profile_picture"])?>" class="noSelect square-30 border-radius-50" />

                                                                    <div class="flex-col-start w-100 ml-2">
                                                                        <div class="font-16 font-weight-bold mb-0">
                                                                            <a href="https://instagram.com/<?=$creator["username"]?>" target="_blank" class="link-prim hover-underline">
                                                                                <?=$creator["username"]?>
                                                                            </a>
                                                                        </div>
                                                                        <div class="color-dark font-14  flex-row-between flex-align-center flex-wrap w-100">
                                                                            <a href="<?=$mediaItem["permalink"]?>" target="_blank" class="hover-underline mr-2">
                                                                                <?=date("F d, H:i", $mediaItem["timestamp"])?>
                                                                            </a>
                                                                            <p class="font-italic"><?=$mediaType?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <p class="font-14 mt-1"><?=\classes\src\Object\transformer\Titles::truncateStr($mediaItem["caption"],80)?></p>
                                                            </div>
                                                            <div class="p-3 ">
                                                                <div class="flex-row-between flex-align-center">
                                                                    <div class="flex-row-start flex-align-center">
                                                                        <i class="mdi mdi-heart color-mdi-icons font-16"></i>
                                                                        <p class="font-16 ml-1"><?=$mediaItem["like_count"]?></p>
                                                                    </div>
                                                                    <div class="flex-row-start flex-align-center">
                                                                        <i class="mdi mdi-comment color-mdi-icons font-16"></i>
                                                                        <p class="font-16 ml-1"><?=$mediaItem["comments_count"]?></p>
                                                                    </div>
                                                                    <div class="flex-row-start flex-align-center">
                                                                        <i class="mdi mdi-eye color-mdi-icons font-16"></i>
                                                                        <p class="font-16 ml-1"><?=$mediaItem["view_count"]?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach;
                                            endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="switchViewObject" data-switch-id="content-type" data-switch-object-name="stories" data-is-shown="false" style="display: none">
                                <div class="row mt-5">
                                    <div class="col-12">
                                        <div class="row">
                                            <?php if(!empty($stories)):
                                                $integrationHandler = $crud->integrations();
                                                $crud->sortByKey($stories, "timestamp");
                                                foreach (array_reverse($stories) as $mediaItem):
                                                    $mediaType = ucfirst($mediaItem["type"]);
                                                    $creator = $lookupList->getByUsername($mediaItem["username"], 0, ["profile_picture", "username"]);
//                                                    $creator = $lookupList->get($mediaItem["lookup_id"], ["profile_picture", "username"]);

                                                    ?>
                                                    <div class="col-12 col-md-6 col-xl-3 pb-2 pt-2 pl-2 pr-2">
                                                        <div class="flex-col-start p-0 bg-white">
                                                            <div class="h-100 overflow-hidden bg-primary-cta flex-col-around">
                                                                <?php if($mediaItem["media_type"] === "VIDEO"): ?>
                                                                <video class="w-100" controls>
                                                                    <source src="<?=resolveImportUrl($mediaItem["display_url"])?>" type="video/mp4">
                                                                </video>
                                                                <?php else: ?>
                                                                    <img src="<?=resolveImportUrl($mediaItem["display_url"])?>" class="w-100" />
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="pl-3 pr-3 pb-3 pt-2 h-75px">
                                                                <div class="flex-row-start flex-align-center">
                                                                    <img src="<?=resolveImportUrl($creator["profile_picture"])?>" class="noSelect square-30 border-radius-50" />

                                                                    <div class="flex-col-start ml-2">
                                                                        <div class="font-16 font-weight-bold mb-0">
                                                                            <a href="https://instagram.com/<?=$creator["username"]?>" target="_blank" class="link-prim hover-underline">
                                                                                <?=$creator["username"]?>
                                                                            </a>
                                                                        </div>
                                                                        <div class="text-black font-14">
                                                                            <a href="<?=$mediaItem["video_url"]?>" target="_blank" class="hover-underline flex-row-start flex-align-center">
                                                                                <p><?=date("F d, H:i", $mediaItem["timestamp"])?></p>
                                                                                <p class="ml-2"><?=$mediaType?></p>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach;
                                            endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="switchViewObject" data-switch-id="content-type" data-switch-object-name="reels" data-is-shown="false" style="display: none">
                                <div class="row mt-5">
                                    <div class="col-12">
                                        <div class="row">
                                            <?php if(!empty($reelsPost)):
                                                $crud->sortByKey($reelsPost, "timestamp");
                                                foreach (array_reverse($reelsPost) as $mediaItem):
                                                    $mediaItem = $mediaHandler->keyJsonEncoding($mediaItem, false);
                                                    $mediaType = $mediaItem["media_type"] === "VIDEO" ? "Reel" :
                                                        (!empty($mediaItem["carousel"]) ? "Carousel" : "Image");
                                                    $creator = $lookupList->get($mediaItem["lookup_id"], ["profile_picture", "username"])

                                                    ?>
                                                    <div class="col-12 col-md-6 col-xl-3 pb-2 pt-2 pl-2 pr-2">
                                                        <div class="flex-col-start p-0 bg-white">
                                                            <div class="w-100 h-300px overflow-hidden bg-primary-cta flex-col-around">
                                                                <?php if($mediaItem["media_type"] === "VIDEO"): ?>
                                                                    <video class="w-100" controls>
                                                                        <source src="<?=resolveImportUrl($mediaItem["display_url"])?>" type="video/mp4">
                                                                    </video>
                                                                <?php else: ?>
                                                                    <img src="<?=resolveImportUrl($mediaItem["display_url"])?>" class="w-100" />
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="pl-3 pr-3 pb-3 pt-2 border-bottom h-150px overflow-hidden">
                                                                <div class="flex-row-start flex-align-center">
                                                                    <img src="<?=resolveImportUrl($creator["profile_picture"])?>" class="noSelect square-30 border-radius-50" />

                                                                    <div class="flex-col-start ml-2">
                                                                        <div class="font-16 font-weight-bold mb-0">
                                                                            <a href="https://instagram.com/<?=$creator["username"]?>" target="_blank" class="link-prim hover-underline">
                                                                                <?=$creator["username"]?>
                                                                            </a>
                                                                        </div>
                                                                        <div class="text-black font-14">
                                                                            <a href="<?=$mediaItem["permalink"]?>" target="_blank" class="hover-underline flex-row-start flex-align-center">
                                                                                <p><?=date("F d, H:i", $mediaItem["timestamp"])?></p>
                                                                                <p class="ml-2"><?=$mediaType?></p>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <p class="font-14 mt-1"><?=\classes\src\Object\transformer\Titles::truncateStr($mediaItem["caption"],80)?></p>
                                                            </div>
                                                            <div class="p-3 ">
                                                                <div class="flex-row-between flex-align-center">
                                                                    <div class="flex-row-start flex-align-center">
                                                                        <i class="mdi mdi-heart color-mdi-icons font-16"></i>
                                                                        <p class="font-16 ml-1"><?=$mediaItem["like_count"]?></p>
                                                                    </div>
                                                                    <div class="flex-row-start flex-align-center">
                                                                        <i class="mdi mdi-comment color-mdi-icons font-16"></i>
                                                                        <p class="font-16 ml-1"><?=$mediaItem["comments_count"]?></p>
                                                                    </div>
                                                                    <div class="flex-row-start flex-align-center">
                                                                        <i class="mdi mdi-eye color-mdi-icons font-16"></i>
                                                                        <p class="font-16 ml-1"><?=$mediaItem["view_count"]?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach;
                                            endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <?php if(!$crud->isCreator()): ?>
                    <div class="switchViewObject" data-switch-id="campaign-content" data-switch-object-name="creators" data-is-shown="false" style="display: none">
                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-12">


                                    <?php if(!empty($relations)):
                                        foreach ($relations as $n => $relation):
                                            $creator = $lookupList->getWithMergedData(
                                                $relation["creator_id"],
                                            );
                                            if(empty($creator)) continue;
                                            $rowColor = $n % 2 === 0 ? "" : "filter-row-fields";

                                            $impressions = 0;
                                            if($campaignHandler->isCampaignUpcoming($campaign)) { //Upcoming campaigns
                                                $engagementRate = $creator["engagement_rate"];
                                                $relatedPostCount = $creator["media_count"];
                                                $creatorTotalInteractions = $creatorTotalViewCount = 0;
                                            }
                                            else { //Active and previous
                                                $creatorCampaignRelatedPosts = $mediaHandler->getByX([
                                                    "lookup_id" => $creator["id"], "campaign_id" => $campaign["id"]
                                                ]);


                                                $creatorTotalLikes = (int)array_reduce($creatorCampaignRelatedPosts, function ($initial, $item) {
                                                    return (!isset($initial) ? 0 : $initial) + (int)$item["like_count"];
                                                });
                                                $creatorTotalComments = (int)array_reduce($creatorCampaignRelatedPosts, function ($initial, $item) {
                                                    return (!isset($initial) ? 0 : $initial) + (int)$item["comments_count"];
                                                });
                                                $creatorTotalInteractions = $creatorTotalLikes + $creatorTotalComments;


                                                $creatorTotalViewCount = (int)array_reduce($creatorCampaignRelatedPosts, function ($initial, $item) {
                                                    return (!isset($initial) ? 0 : $initial) + (!in_array($item["type"], ["REELS", "STORY"]) ? 0 :
                                                            ((int)$item["play_count"] > 0 ? (int)$item["play_count"] : (int)$item["view_count"]));
                                                });

                                                $stats = $dataHandler->fromMediaSetUserAverages(
                                                    [
                                                        "media" => $creatorCampaignRelatedPosts,
                                                        "followers_count" => (int)$creator["followers_count"]
                                                    ]
                                                );
                                                $engagementRate = $dataHandler->engagementRate($stats);
                                                $relatedPostCount = count($creatorCampaignRelatedPosts);
                                            }





                                        ?>

                                        <div class="card mt-3 mb-3">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-4">
                                                        <div class="flex-row-start flex-align-center flex-wrap">
                                                            <div class="position-relative">
                                                                <img src="<?=resolveImportUrl($creator["profile_picture"])?>" class="noSelect square-50 border-radius-50 mr-2 mt-1" />
                                                                <?php if((int)$creator["api"]): ?>
                                                                    <div style="position:absolute; top: -10px; right: -5px;">
                                                                        <i class="mdi mdi-check-decagram font-25 " style="color: #1c96df"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <a href="<?=URL::addParam(HOST, ["page" => "creators", "creator" => $creator["id"], "campaign" => $campaignId], true)?>"
                                                               class="link-prim text-center font-16 font-weight-bold mr-2 mt-1">
                                                                <?=$creator["username"]?>
                                                            </a>
                                                        </div>
                                                    </div>


                                                    <div class="col-8">
                                                        <div class="row">
                                                            <div class="table-responsive">
                                                                <table class="table">
                                                                    <tr>
                                                                        <td style="border-top: none;">
                                                                            <div class="font-16 flex-col-start">
                                                                                <p class="mb-0 font-weight-bold text-right"><?=$relatedPostCount?></p>
                                                                                <p class="mt-2 mb-0 text-right">Posts</p>
                                                                            </div>
                                                                        </td>
                                                                        <td style="border-top: none;">
                                                                            <div class="font-16 flex-col-start">
                                                                                <p class="mb-0 font-weight-bold text-right"><?=$creatorTotalInteractions?></p>
                                                                                <p class="mt-2 mb-0 text-right">Interactions</p>
                                                                            </div>
                                                                        </td>
                                                                        <td style="border-top: none;">
                                                                            <div class="font-16 flex-col-start">
                                                                                <p class="mb-0 font-weight-bold text-right"><?=$impressions?></p>
                                                                                <p class="mt-2 mb-0 text-right">Impressions</p>
                                                                            </div>
                                                                        </td>
                                                                        <td style="border-top: none;">
                                                                            <div class="font-16 flex-col-start">
                                                                                <p class="mb-0 font-weight-bold text-right"><?=$creatorTotalViewCount?></p>
                                                                                <p class="mt-2 mb-0 text-right">Video views</p>
                                                                            </div>
                                                                        </td>
                                                                        <td style="border-top: none;">
                                                                            <?php if((int)$_SESSION["access_level"] > 1): ?>
                                                                                <div class="font-16 flex-col-start">
                                                                                    <p class="color-red hover-underline cursor-pointer removeCreatorFromCampaign noSelect mb-0 text-right"
                                                                                       data-creator-id="<?=$creator["id"]?>" data-campaign-id="<?=$campaignId?>" data-username="<?=$creator["username"]?>">
                                                                                        Remove
                                                                                    </p>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                            <?php endforeach;
                                        endif; ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>















        <?php else:
            $creatorId = $crud->creatorId();
            $activeCampaigns = $campaignHandler->getActiveCampaigns([], 0, $creatorId);
            $upcomingCampaigns = $campaignHandler->getUpcomingCampaigns([], $creatorId);
            $pastCampaigns = $campaignHandler->getPastCampaigns([], $creatorId);
            $creators = $lookupList->getByX(["deactivated" => 0], ["id", "username"]);



        ?>

        <div class="row mt-3 slideContainer" id="campaign_creation_container" style="display: none">
            <div class="col-12">
                <divv class="card">
                    <div class="card-body">

                        <div class="row">
                            <?php if(empty($creators)): ?>
                            <div class="col-12 mt-1">
                                <p class="font-18">To create a campaign, you must first add active creators</p>
                            </div>
                            <?php else: ?>

                                <?php if(!$crud->isAdmin()): ?>
                                    <div class="col-12 col-md-4 col-xl-6 mt-1">
                                        <div class="flex-col-start">
                                            <p class="font-18">Campaign name</p>
                                            <input type="text" name="campaign_name" placeholder="Eg. brandname etc." max="30" class="form-control"/>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="col-12 col-md-2 col-xl-3 mt-1">
                                        <div class="flex-col-start">
                                            <p class="font-18">Campaign name</p>
                                            <input type="text" name="campaign_name" placeholder="Eg. brandname etc." max="30" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-2 col-xl-3 mt-1">
                                        <p class="font-18">Assign to</p>
                                        <select name="campaign_owner" class="form-control" >
                                            <option value="">Not assigned</option>
                                            <?php foreach ($crud->user()->getByX(["access_level" => 2]) as $brand): ?>
                                                <option value="<?=$brand["uid"]?>">
                                                    <?=$brand["username"]?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>


                            <div class="col-12 col-md-8 col-xl-6 mt-1">
                                <div class="flex-col-start">
                                    <p class="font-18">Dates</p>
                                    <input type="text" name="campaign_dates" class="form-control DP_RANGE"/>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3 mt-1">
                                <div class="flex-col-start">
                                    <p class="font-18">Content types</p>
                                    <select name="post_types" class="form-control">
                                        <option value="mixed">Mixed</option>
                                        <option value="reel">Reels</option>
                                        <option value="post">Posts</option>
                                        <option value="story">Stories</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3 mt-1">
                                <div class="flex-col-start">
                                    <p class="font-18">Content per creator</p>
                                    <input type="number" min="1" max="10" value="1" name="ppc" class="form-control" />
                                </div>
                            </div>
                            <div class="col-12 col-xl-6 mt-1">
                                <div class="flex-col-start">
                                    <p class="font-18">Creators</p>
                                    <select name="campaign_creators" class=" select2Multi" multiple="multiple"
                                        data-select2-attr='{"tags":false,"height":"20px","placeholder":"Select creators","allowClear":true}' >
                                        <?php foreach ($creators as $creator): ?>
                                        <option value="<?=$creator["id"]?>">@<?=$creator["username"]?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>


                            <div class="col-12 col-md-6 col-xl-3 mt-1">
                                <div class="flex-col-start">
                                    <p class="font-18">Tracking</p>
                                    <select name="tracking" class=" form-control">
                                            <option value="mention">Mention</option>
                                            <option value="hashtag">Hashtag (Not applicable to stories)</option>
                                            <option value="both">Hashtag & Mentions</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3 mt-1">
                                <div class="flex-col-start">
                                    <p class="font-18">Tracking hashtag</p>
                                    <input type="text" name="tracking_hashtag" class="form-control" placeholder="Tracking hashtag" />
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-6 mt-1 d-flex justify-content-end align-items-end"">
                                <div class="flex-row-end flex-align-center">
                                    <button class="btn-base btn-red border-transparent" name="toggle_campaign_creation_view">Close</button>
                                    <button class="btn-base btn-green-white border-transparent ml-2" name="create_campaign">Save & create</button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>



                    </div>
                </divv>
            </div>
        </div>





        <div class="row mt-3">
            <div class="col-12">
                <div class="card mt-2">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <p class="font-18 font-weight-bold color-primary-cta">Active campaigns</p>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="table-responsive">

                                    <table class="w-100 table-padding">
                                        <thead>
                                        <tr class="color-primary-dark bg-wrapper">
                                            <th class="font-weight-normal">Name</th>
                                            <th class="font-weight-normal hideOnMobileTableCell">Total posts</th>
                                            <th class="font-weight-normal hideOnMobileTableCell">Expected posts</th>
                                            <th class="font-weight-normal">Ends at</th>
                                            <th class="font-weight-normal">Completion</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if(!empty($activeCampaigns)):
                                            foreach ($activeCampaigns as $activeCampaign):
                                                $creatorCount = count($campaignRelations->getByX(["campaign_id" => $activeCampaign["id"]]));
                                                $expectedPosts = (int)$activeCampaign["ppc"] * $creatorCount;
                                                $totalPosts = count($mediaHandler->getByX(["campaign_id" => $activeCampaign["id"]]));
                                                $campaignLink = URL::addParam(HOST, ["page" => "campaigns", "campaign" => $activeCampaign["id"]], true);
                                                ?>
                                                <tr>
                                                    <td class="font-weight-bold">
                                                        <a href="<?=$campaignLink?>" class="link-prim" ><?=$activeCampaign["name"]?></a>
                                                    </td>
                                                    <td class="hideOnMobileTableCell"><?=$totalPosts?></td>
                                                    <td class="hideOnMobileTableCell"><?=$expectedPosts?></td>
                                                    <td><?=date("M d, H:i", $activeCampaign["end"])?></td>
                                                    <td><?=$expectedPosts === 0 ? 0 : round($totalPosts / $expectedPosts * 100,2)?>%</td>
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
                                <p class="font-18 font-weight-bold color-primary-cta">Upcoming campaigns</p>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="table-responsive">

                                    <table class="w-100 table-padding">
                                        <thead>
                                        <tr class="color-primary-dark bg-wrapper">
                                            <th class="font-weight-normal">Name</th>
                                            <th class="font-weight-normal hideOnMobileTableCell">Expected posts</th>
                                            <th class="font-weight-normal">Starts at</th>
                                            <th class="font-weight-normal">Ends at</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php  if(!empty($upcomingCampaigns)):
                                            foreach ($upcomingCampaigns as $upcomingCampaign):
                                                $creatorCount = count($campaignRelations->getByX(["campaign_id" => $upcomingCampaign["id"]]));
                                                $expectedPosts = (int)$upcomingCampaign["ppc"] * $creatorCount;
                                                $campaignLink = URL::addParam(HOST, ["page" => "campaigns", "campaign" => $upcomingCampaign["id"]], true);
                                            ?>
                                                <tr>
                                                    <td class="font-weight-bold">
                                                        <a href="<?=$campaignLink?>" class="link-prim" ><?=$upcomingCampaign["name"]?></a>
                                                    </td>
                                                    <td class="hideOnMobileTableCell"><?=$expectedPosts?></td>
                                                    <td><?=date("M d, H:i", $upcomingCampaign["start"])?></td>
                                                    <td><?=date("M d, H:i", $upcomingCampaign["end"])?></td>
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
                                <p class="font-18 font-weight-bold color-primary-cta">Previous campaigns</p>
                            </div>
                            <div class="col-12 mt-2">
                                <div class="table-responsive">

                                    <table class="w-100 table-padding">
                                        <thead>
                                        <tr class="color-primary-dark bg-wrapper">
                                            <th class="font-weight-normal">Name</th>
                                            <th class="font-weight-normal hideOnMobileTableCell">Total posts</th>
                                            <th class="font-weight-normal hideOnMobileTableCell">Expected posts</th>
                                            <th class="font-weight-normal">Ended at</th>
                                            <th class="font-weight-normal">Completion</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if(!empty($pastCampaigns)):
                                            foreach ($pastCampaigns as $pastCampaign):
                                                $creatorCount = count($campaignRelations->getByX(["campaign_id" => $pastCampaign["id"]]));
                                                $expectedPosts = (int)$pastCampaign["ppc"] * $creatorCount;
                                                $totalPosts = 0;
                                                $campaignLink = URL::addParam(HOST, ["page" => "campaigns", "campaign" => $pastCampaign["id"]], true);
                                                ?>
                                                <tr>
                                                    <td class="font-weight-bold">
                                                        <a href="<?=$campaignLink?>" class="link-prim" ><?=$pastCampaign["name"]?></a>
                                                    </td>
                                                    <td class="hideOnMobileTableCell"><?=$totalPosts?></td>
                                                    <td class="hideOnMobileTableCell"><?=$expectedPosts?></td>
                                                    <td><?=date("M d, H:i", $pastCampaign["end"])?></td>
                                                    <td><?=$expectedPosts === 0 ? 0 : round($totalPosts / $expectedPosts * 100,2)?>%</td>
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