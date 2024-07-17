<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
use classes\src\Object\transformer\URL;
use classes\src\Auth\Auth;
use classes\src\Object\objects\Integrations;
$crud = new AbstractCrudObject();

$pageTitle = "My Mentions";


$lookupList = $crud->lookupList();
$campaignHandler = $crud->campaigns();
$campaignRelations = $crud->campaignRelations();
$mediaHandler = $crud->mediaLookup();
$dataHandler = $crud->dataHandler();

$integration = $crud->integrations()->getMyIntegration(0, ["id"]);
$mentionedMedia = empty($integration) ? [] : $mediaHandler->getByX(["lookup_id" => $integration["id"], "origin" => "special_mention"]);



?>
    <script>
        var pageTitle = <?=json_encode($pageTitle)?>;
    </script>
    <div class="page-content position-relative" data-page="my_mentions">

        <div class="flex-row-between flex-align-center flex-wrap">
            <div class="flex-col-start mt-1">
                    <p class="font-22 font-weight-medium">My mentions</p>
            </div>
        </div>


        <?php if(empty($mentionedMedia)):  ?>

        <p class="font-18 mt-3">You do not have any recorded media mentions at this moment</p>

        <?php else:
            $posts = array_values(array_filter($mentionedMedia, function ($item) { return $item["type"] === "post"; }));
            $stories = array_values(array_filter($mentionedMedia, function ($item) { return $item["type"] === "story"; }));
            $normalPosts = array_values(array_filter($posts, function ($item) { return !in_array($item["media_type"], ["REELS", "VIDEO"]); }));
            $reelsPost = array_values(array_filter($posts, function ($item) { return in_array($item["media_type"], ["REELS", "VIDEO"]); }));
            $totalPosts = count($posts) + count($stories);


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




        ?>


        <div class="row mt-5">
            <div class="col-12" data-switchParent data-switch-id="campaign-content"
                 data-active-btn-class="color-primary-dark border-bottom-thick-primary-dark font-weight-bold"
                 data-inactive-btn-class="color-primary-dark font-weight-normal">
                <div class="flex-row-start flex-align-center mb-3 border-bottom-gray w-100" >
                    <div class="switchViewBtn font-20 px-3 py-1 color-primary-dark border-bottom-thick-primary-dark font-weight-bold"
                         data-toggle-switch-object="content" data-switch-id="campaign-content">Content</div>
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

                                                    ?>
                                                    <div class="col-12 col-md-6 col-xl-3 pb-2 pt-2 pl-2 pr-2">
                                                        <div class="flex-col-start p-0 bg-white">
                                                            <div class="w-100 h-300px overflow-hidden bg-primary-cta">
                                                                <img src="<?=resolveImportUrl($mediaItem["display_url"])?>" class="w-100" />
                                                            </div>
                                                            <div class="pl-3 pr-3 pb-3 pt-2 border-bottom h-150px overflow-hidden">
                                                                <div class="flex-row-start flex-align-center">
                                                                    <div class="border-radius-50 flex-row-around flex-align-center bg-dark color-white font-16" style="width: 30px !important; height: 30px !important;">
                                                                        <?=substr($mediaItem["username"], 0 , 1)?>
                                                                    </div>

                                                                    <div class="flex-col-start w-100 ml-2">
                                                                        <div class="font-16 font-weight-bold mb-0">
                                                                            <a href="https://instagram.com/<?=$mediaItem["username"]?>" target="_blank" class="link-prim hover-underline">
                                                                                <?=$mediaItem["username"]?>
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
                                                $crud->sortByKey($stories, "timestamp");
                                                foreach (array_reverse($stories) as $mediaItem):
                                                    $mediaType = ucfirst($mediaItem["type"]);
                                                    ?>
                                                    <div class="col-12 col-md-6 col-xl-3 pb-2 pt-2 pl-2 pr-2">
                                                        <div class="flex-col-start p-0 bg-white">
                                                            <div class="h-100 overflow-hidden bg-primary-cta">
                                                                <img src="<?=resolveImportUrl($mediaItem["display_url"])?>" class="w-100" />
                                                            </div>
                                                            <div class="pl-3 pr-3 pb-3 pt-2 h-75px">
                                                                <div class="flex-row-start flex-align-center">
                                                                    <div class="border-radius-50 flex-row-around flex-align-center bg-dark color-white font-16" style="width: 30px !important; height: 30px !important;">
                                                                        <?=substr($mediaItem["username"], 0 , 1)?>
                                                                    </div>

                                                                    <div class="flex-col-start ml-2">
                                                                        <div class="font-16 font-weight-bold mb-0">
                                                                            <a href="https://instagram.com/<?=$mediaItem["username"]?>" target="_blank" class="link-prim hover-underline">
                                                                                <?=$mediaItem["username"]?>
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

                                                    ?>
                                                    <div class="col-12 col-md-6 col-xl-3 pb-2 pt-2 pl-2 pr-2">
                                                        <div class="flex-col-start p-0 bg-white">
                                                            <div class="w-100 h-300px overflow-hidden bg-primary-cta">
                                                                <img src="<?=resolveImportUrl($mediaItem["display_url"])?>" class="w-100" />
                                                            </div>
                                                            <div class="pl-3 pr-3 pb-3 pt-2 border-bottom h-150px overflow-hidden">
                                                                <div class="flex-row-start flex-align-center">
                                                                    <div class="border-radius-50 flex-row-around flex-align-center bg-dark color-white font-16" style="width: 30px !important; height: 30px !important;">
                                                                        <?=substr($mediaItem["username"], 0 , 1)?>
                                                                    </div>



                                                                    <div class="flex-col-start ml-2">
                                                                        <div class="font-16 font-weight-bold mb-0">
                                                                            <a href="https://instagram.com/<?=$mediaItem["username"]?>" target="_blank" class="link-prim hover-underline">
                                                                                <?=$mediaItem["username"]?>
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

            </div>
        </div>

        <?php endif; ?>



    </div>

<?php
$crud->closeConnection();