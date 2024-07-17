<?php
/**
 * @var $creator
 * @var $crud
 * @var $mediaHandler
 * @var $lookupList
 * @var $creatorId
 */

$campaignCount = count($crud->campaignRelations()->getByX(["creator_id" => $creator["id"]]));
$contentParam = ["lookup_id" => $creator["id"]];
$posts = array_filter($creator["media"], function ($media) {
    return $media["type"] === "post";
});
$stories = array_filter($creator["media"], function ($media) {
    return $media["type"] === "story";
});
$crud->sortByKey($posts, "timestamp");
$crud->sortByKey($stories, "timestamp");


$normalPosts = array_values(array_filter($posts, function ($item) { return !in_array($item["media_type"], ["REELS", "VIDEO", "REEL", "VIDEOS"]); }));
$reelsPost = array_values(array_filter($posts, function ($item) { return in_array($item["media_type"], ["REELS", "VIDEO", "REEL", "VIDEOS"]); }));

$videoViews = count($reelsPost) === 0 ? 0 : ceil(
    array_reduce($reelsPost, function ($initial, $item) {
        return (!isset($initial) ? 0 : $initial) + ((int)$item["view_count"] > 0 ? (int)$item["view_count"] : (int)$item["play_count"]);
    })
    / count($reelsPost)
);
$estTotalReach = ($creator["engagement_rate"] / 100) * $creator["followers_count"];
$estEMV = $estTotalReach * .01;

$totalInteractions = round(
    array_reduce($posts, function ($initial, $item) {
        return (!isset($initial) ? 0 : $initial) + (int)$item["like_count"] + (int)$item["comments_count"] + (int)$item["play_count"] + (int)$item["view_count"];
    })
);


$analyticHandler = $crud->accountAnalytics();
$latestAnalytics = $analyticHandler->keyJsonEncoding($analyticHandler->getCreatorLatest($creatorId), false);
$latestAnalytics = $analyticHandler->formatChartData($latestAnalytics);


$postChunkSize = 8;
?>

<script>
    var creatorAnalytics = <?=json_encode($latestAnalytics)?>;
</script>

<div class="row flex-align-center">
    <div class="col-12 col-md-3 col-xl-5 mt-4">
        <div class="row  justify-content-xl-around">
            <div class="col-12 col-xl-6">
                <div class="border-radius-50 w-100">
                    <div class="position-relative">
                        <img src="<?=resolveImportUrl($creator["profile_picture"])?>" class="noSelect w-100 border-radius-50 imageCtaShadow" />
                        <?php if((int)$creator["api"]): ?>
                            <div style="position:absolute; top: 0; right: 0;">
                                <i class="mdi mdi-check-decagram font-40 " style="color: #1c96df"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-9 col-xl-7 mt-4">

        <div class="row">
            <div class="col-12 col-md-6 mt-1">
                <div class="card border-radius-10px">
                    <div class="card-body position-relative">
                        <div class="flex-row-between flex-wrap">
                            <p class="font-32 font-weight-bold"><?=number_format($creator["media_count"], 0, ",", ".")?></p>
                            <p class="font-16 mt-1">Posts</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-6 mt-1">
                <div class="card border-radius-10px">
                    <div class="card-body position-relative">
                        <div class="flex-row-between flex-wrap">
                            <p class="font-32 font-weight-bold"><?=number_format($creator["engagement_rate"], 0, ",", ".")?>%</p>
                            <p class="font-14 desktopOnlyBlock">Engagement</p>
                            <p class="font-14 hideOnDesktopBlock">Eng. Rate</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-6 mt-1">
                <div class="card border-radius-10px">
                    <div class="card-body position-relative">
                        <div class="flex-row-between flex-wrap">
                            <p class="font-32 font-weight-bold"><?=number_format($creator["followers_count"], 0, ",", ".")?></p>
                            <p class="font-16 mt-1">Followers</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-6 mt-1">
                <div class="card border-radius-10px">
                    <div class="card-body position-relative">
                        <div class="flex-row-between flex-wrap">
                            <p class="font-32 font-weight-bold"><?=number_format($campaignCount, 0, ",", ".")?></p>
                            <p class="font-16 mt-1">Campaigns</p>
                        </div>
                    </div>
                </div>
            </div>



            <div class="col-12 col-md-6 col-xl-6 mt-1">
                <div class="card border-radius-10px">
                    <div class="card-body position-relative">
                        <div class="flex-row-between flex-wrap">
                            <p class="font-32 font-weight-bold"><?=number_format($totalInteractions, 0, ",", ".")?></p>
                            <p class="font-16 mt-1">Interactions</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-6 mt-1">
                <div class="card border-radius-10px">
                    <div class="card-body position-relative">
                        <div class="flex-row-between flex-wrap">
                            <p class="font-32 font-weight-bold"><?=number_format($videoViews, 0, ",", ".")?></p>
                            <p class="font-14 desktopOnlyBlock">Avg. video views</p>
                            <p class="font-14 hideOnDesktopBlock">Views</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row mt-5">
    <div class="col-12" data-switchParent data-switch-id="creator-content"
         data-active-btn-class="color-primary-dark border-bottom-thick-primary-dark font-weight-bold"
         data-inactive-btn-class="color-primary-dark font-weight-normal">
        <div class="flex-row-start flex-align-center mb-3 border-bottom-gray w-100" >

            <div class="switchViewBtn font-20 px-3 py-1 color-primary-dark border-bottom-thick-primary-dark font-weight-bold"
                 data-toggle-switch-object="content" data-switch-id="creator-content">Recent posts</div>

            <div class="switchViewBtn font-20 px-3 py-1 color-primary-dark font-weight-normal"
                 data-toggle-switch-object="statistics" data-switch-id="creator-content">Statistics</div>
        </div>

        <div class="switchViewObject" data-switch-id="creator-content" data-switch-object-name="content" data-is-shown="true">
            <div class="row">
                <div class="col-12" data-switchParent data-switch-id="content-type"
                     data-active-btn-class="bg-primary-dark"
                     data-inactive-btn-class="bg-gray">
                    <div class="flex-row-start flex-align-center mb-3" >
                        <div class="switchViewBtn font-16 px-3 py-2 bg-primary-dark color-white border-radius-20px font-weight-bold mx-1"
                             data-toggle-switch-object="posts" data-switch-id="content-type">
                            Posts
                            <span class="ml-2 bg-blue border-radius-10px px-2 font-14"><?=min(count($normalPosts), $postChunkSize)?></span>
                        </div>
                        <div class="switchViewBtn font-16 px-3 py-2 bg-gray color-white border-radius-20px font-weight-bold mx-1"
                             data-toggle-switch-object="stories" data-switch-id="content-type">
                            Stories
                            <span class="ml-2 bg-blue border-radius-10px px-2 font-14"><?=min(count($stories), $postChunkSize)?></span>
                        </div>
                        <div class="switchViewBtn font-16 px-3 py-2 bg-gray color-white border-radius-20px font-weight-bold mx-1"
                             data-toggle-switch-object="reels" data-switch-id="content-type">
                            Reels
                            <span class="ml-2 bg-blue border-radius-10px px-2 font-14"><?=min(count($reelsPost), $postChunkSize)?></span>
                        </div>
                    </div>


                    <div class="switchViewObject" data-switch-id="content-type" data-switch-object-name="posts" data-is-shown="true">
                        <div class="col-12">
                            <div class="row">
                                <?php if(!empty($normalPosts)):
                                    $crud->sortByKey($normalPosts, "timestamp", false);
                                    $recentMedia = array_chunk($normalPosts, $postChunkSize)[0];
                                    foreach ($recentMedia as $mediaItem):
                                        $mediaItem = $mediaHandler->keyJsonEncoding($mediaItem, false);
                                        $mediaType = !empty($mediaItem["carousel"]) ? "Carousel" : "Image";

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
                                                <div class="pl-3 pr-3 pb-3 pt-2 border-bottom h-150px">
                                                    <div class="flex-row-start flex-align-center overflow-hidden">
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


                    <div class="switchViewObject" data-switch-id="content-type" data-switch-object-name="stories" data-is-shown="false" style="display: none;">
                        <div class="col-12">
                            <div class="row">
                                <?php if(!empty($stories)):
                                    $crud->sortByKey($stories, "timestamp");
                                    $recentMedia = array_chunk($stories, $postChunkSize)[0];
                                    foreach ($recentMedia as $mediaItem):
                                        $mediaItem = $mediaHandler->keyJsonEncoding($mediaItem, false);
                                        $mediaType = "Story";

                                        ?>
                                        <div class="col-12 col-md-6 col-xl-3 pb-2 pt-2 pl-2 pr-2">
                                            <div class="flex-col-start p-0 bg-white">
                                                <div class="w-100 h-100 overflow-hidden bg-primary-cta flex-col-around">
                                                    <?php if($mediaItem["media_type"] === "VIDEO"): ?>
                                                        <video class="w-100" controls>
                                                            <source src="<?=resolveImportUrl($mediaItem["display_url"])?>" type="video/mp4">
                                                        </video>
                                                    <?php else: ?>
                                                        <img src="<?=resolveImportUrl($mediaItem["display_url"])?>" class="w-100" />
                                                    <?php endif; ?>
                                                </div>
                                                <div class="pl-3 pr-3 pb-3 pt-2 border-bottom h-150px">
                                                    <div class="flex-row-start flex-align-center overflow-hidden">

                                                        <img src="<?=resolveImportUrl($creator["profile_picture"])?>" class="noSelect square-30 border-radius-50" />
                                                        <!---->
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


                    <div class="switchViewObject" data-switch-id="content-type" data-switch-object-name="reels" data-is-shown="false" style="display: none;">
                        <div class="col-12">
                            <div class="row">
                                <?php if(!empty($reelsPost)):
                                    $crud->sortByKey($reelsPost, "timestamp", true);
                                    $recentMedia = array_chunk($reelsPost, $postChunkSize)[0];
                                    foreach ($recentMedia as $mediaItem):
                                        $mediaItem = $mediaHandler->keyJsonEncoding($mediaItem, false);
                                        $mediaType = "Video";

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
                                                <div class="pl-3 pr-3 pb-3 pt-2 border-bottom h-150px">
                                                    <div class="flex-row-start flex-align-center overflow-hidden">
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


        <div class="switchViewObject" data-switch-id="creator-content" data-switch-object-name="statistics" data-is-shown="false" style="display: none">

            <div class="row">
                <div class="col-12">
                    <?php if(empty($latestAnalytics)): ?>
                    <p class="font-18 font-weight-bold">No statistics to display</p>
                    <?php else: ?>

                    <div class="row">
                        <div class="col-12 col-lg-4 mt-1">
                            <div class="card border-radius-10px">
                                <div class="card-body">
                                    <div class="flex-row-between flex-nowrap">
                                        <p class="font-32 font-weight-bold"><?=number_format($latestAnalytics["reach"], 0, ",", ".")?></p>
                                        <div class="flex-row-end flex-align-center flex-wrap">
                                            <p class="font-16 mt-1 mr-1">Reach</p>
                                            <p class="font-12"><sup>(Past 28 days)</sup></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4 mt-1">
                            <div class="card border-radius-10px">
                                <div class="card-body">
                                    <div class="flex-row-between flex-nowrap">
                                        <p class="font-32 font-weight-bold"><?=number_format($latestAnalytics["impressions"], 0, ",", ".")?></p>
                                        <div class="flex-row-end flex-align-center flex-wrap">
                                            <p class="font-16 mt-1 mr-1">Impressions</p>
                                            <p class="font-12"><sup>(Past 28 days)</sup></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4 mt-1">
                            <div class="card border-radius-10px">
                                <div class="card-body">
                                    <div class="flex-row-between flex-nowrap">
                                        <p class="font-32 font-weight-bold"><?=$latestAnalytics["online_followers"] === 0 ? "<100" :
                                            number_format($latestAnalytics["online_followers"], 0, ",", ".")
                                            ?></p>
                                        <div class="flex-row-end flex-align-center flex-wrap">
                                            <p class="font-16 mr-1">Active followers</p>
                                            <p class="font-12"><sup>(Past 28 days)</sup></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6 mt-4">
                            <div class="position-relative">
                                <div class="drawChart" data-multiple="false" data-chart-type="pie" id="cities"></div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6 mt-4">
                            <div class="position-relative">
                                <div class="drawChart" data-multiple="false" data-chart-type="pie" id="gender_count"></div>
                            </div>
                        </div>


                        <div class="col-12 mt-4">
                            <div class="position-relative">
                                <div class="drawChart" data-chart-type="bar" id="age_range"></div>
                            </div>
                        </div>


                        <style>
                            div.google-visualization-tooltip {
                                background: var(--dark);
                                color: var(--light) !important;
                                border-radius: 15px;
                                padding: .5rem .75rem;
                            }
                            div.google-visualization-tooltip * {
                                color: var(--primary-bg) !important;
                            }
                        </style>

                        <div class="col-12 mt-4">
                            <div class="position-relative">
                                <div class="drawChart" data-chart-type="countries-map" id="countries"></div>
                            </div>
                        </div>
                    </div>


                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

