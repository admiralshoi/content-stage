<?php
namespace classes\src\Object;
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

use classes\src\AbstractCrudObject;
use classes\src\Enum\ExternalItems;
use classes\src\Enum\ScraperNestedLists;
use classes\src\Media\Medias;
use classes\src\Object\objects\Campaigns;
use classes\src\Object\objects\DataHandler;
use classes\src\Enum\QuerySelector;


class CronRequestHandler {
    private AbstractCrudObject $crud;

    function __construct(AbstractCrudObject $crud) {
        $this->crud = $crud;
    }



    public function findCreatorsToQueryAccountAnalytics(?CronWorker $worker = null, array $extraParams = []): array {
        $minTrackingTimeBetween = time() - (3600 * 13);
        $integrationHandler = $this->crud->integrations();
        $lookupHandler = $this->crud->lookupList();
        $analyticsHandler = $this->crud->accountAnalytics();
        $creatorIntegrations = $integrationHandler->getByX(array_merge(["is_creator" => 1, "provider" => "instagram"], $extraParams));
        $worker?->log("A total of " . count($creatorIntegrations) . " creator integrations were pulled");
        if(empty($creatorIntegrations)) return [];

        $collector = [];
        foreach ($creatorIntegrations as $integration) {
            $creator = $lookupHandler->getByUsername($integration["item_name"], 0, ["id"]);
            if(empty($creator)) continue;
//            if(empty($creator) || !$lookupHandler->belongsToApi($creator["id"])) continue;
            $latestPull = $analyticsHandler->getCreatorLatest($creator["id"], ["created_at"]);

            if(!empty($latestPull) && (((int)$latestPull["created_at"] + $minTrackingTimeBetween))) continue;

            $collector[] = [
                "lookup_id" => $creator["id"],
                "integration_id" => $integration["id"],
                "access_token" => $integration["item_token"],
                "account_id" => $integration["item_id"],
            ];
        }
        $worker?->log("Returning " . count($creatorIntegrations) . " creators whose analytics must be updated");
        return empty($collector) ? [] : array_chunk($collector, 5)[0];
    }





    public function queryAccountAnalytics(array $creatorItems, ?CronWorker $worker = null): void {
        $lookupHandler = $this->crud->lookupList();
        $dataHandler = $this->crud->dataHandler();
        $analyticsHandler = $this->crud->accountAnalytics();

        $api = new Medias();
        if(!$api->init("instagram")) return;

        $worker?->log("Looping " . count($creatorItems) . " creators whose analytics must be updated");
        foreach ($creatorItems as $item) {
            $lookupId = $item["lookup_id"];
            $accountId = $item["account_id"];
            $accessToken = $item["access_token"];

            $creatorData = $api->accountInsight($accountId, $accessToken);
            if(empty($creatorData) || array_key_exists("error", $creatorData)) {
                $worker?->log("Empty or error thrown: " . json_encode($creatorData));
                continue;
            }
            file_put_contents(TESTLOGS . "creatordata.json", json_encode($creatorData, JSON_PRETTY_PRINT));
            $worker?->log("Pulled account basic insights");

            $creatorData = $dataHandler->downloadMediasAndUpdateUrl($creatorData, false, "profile-picture", "username")[0];
            $lookupHandler->update($creatorData, ["id" => $lookupId]);
            $worker?->log("Account basic insights updated");

            $analyticInsights = $api->accountReachInsight($accountId, $accessToken);
            $demoInsights = $api->accountDemographicInsight($accountId, $accessToken);
            if(!array_key_exists("error", $demoInsights)) $analyticInsights = array_merge($analyticInsights, $demoInsights);
            $worker?->log("Pulled account demographic -and reach -insights");
            if(empty($analyticInsights) || array_key_exists("error", $analyticInsights)) continue;

            $analyticInsights["lookup_id"] = $lookupId;
            if(array_key_exists("online_followers", $analyticInsights) && $analyticInsights["online_followers"] === []) $analyticInsights["online_followers"] = 0;
            file_put_contents(TESTLOGS . "insightsAccount.json", json_encode($analyticInsights, JSON_PRETTY_PRINT));

            $analyticsHandler->create($analyticInsights);
            $worker?->log("Advanced account insights created");
        }
    }









    public function findCreatorsToCompleteIntegration(?CronWorker $worker = null, array $extraParams = []): array {
        $integrationHandler = $this->crud->integrations();
        $lookupHandler = $this->crud->lookupList();
        $creatorIntegrations = $integrationHandler->getByX(array_merge(["is_creator" => 1, "provider" => "instagram"], $extraParams));
        $worker?->log("A total of " . count($creatorIntegrations) . " creator integrations were pulled");
        if(empty($creatorIntegrations)) return [];

        $collector = [];
        foreach ($creatorIntegrations as $integration) {
            file_put_contents(TESTLOGS . "int-proc.log", json_encode($integration) . PHP_EOL . PHP_EOL, 8);
            if(!empty($lookupHandler->getByUsername($integration["item_name"], 0, ["id"]))) continue;
            $collector[] = $integration;
        }
        $worker?->log("Returning " . count($creatorIntegrations) . " integrations to complete");
        return empty($collector) ? [] : array_chunk($collector, 5)[0];
    }


    public function finishCreatorIntegration(array $integrations, ?CronWorker $worker = null): void {
        $worker?->log("Looping " . count($integrations) . " integrations to complete");
        $lookupHandler = $this->crud->lookupList()->disableRelationCheck();
        $handler = $this->crud->handler();

        foreach ($integrations as $i => $integration) {
            $data = $handler->instagramUserLookupApi($integration, "", $worker);
            $this->crud->multiArrayLog($data, "api-data-$i");
            if(empty($data)) continue;

            $lookupHandler->setMeta([
                "data_level" => 1,
                "init_type" => ExternalItems::INITIALIZED_DIRECT,
                "init_by" => $integration["user_id"],
                "init_origin" => ExternalItems::ORIGIN_AUTOMATED,
                "api" => 1
            ]);
            $lookupHandler->setUserAndMedia($data);
        }
    }






    public function getCreatorMediaToUpdate(?CronWorker $worker = null): array {
        $minTrackingTimeBetween = time() - $this->crud->settings->campaign_media_update_frequency;
        $campaignHandler = $this->crud->campaigns();
        $mediaHandler = $this->crud->mediaLookup();
        $creatorHandler = $this->crud->lookupList();
        $cronLastRun = !is_null($worker) ? $worker->finishedAt() : 0;
        $campaigns = $cronLastRun === 0 ?
            $campaignHandler->getActiveCampaigns() :
            $campaignHandler->getActiveCampaignsArbitraryEndTime($cronLastRun);
        if(empty($campaigns)) return [];


        $collector = [];
        $worker?->log("Looping " . count($campaigns) . " potential campaigns");
        foreach ($campaigns as $campaign) {
            $campaignId = $campaign["id"];
            $creators = $campaignHandler->getCampaignCreators($campaignId, ["id", "username", "followers_count"]);
            if(empty($creators)) continue;

            foreach ($creators as $creator) {
                $creatorId = $creator["id"];
                $creatorCampaignMedia = $mediaHandler->getAccountCampaignMedia($creatorId, $campaignId, ["id", "mid", "shortcode", "lookup_id", "type", "updated_at", "timestamp"]);
                if(empty($creatorCampaignMedia)) continue;

                foreach ($creatorCampaignMedia as $media) {
                    $api = $creatorHandler->belongsToApi($creatorId);
                    if(!$api && $media["type"] === "story") continue;
                    if($media["type"] === "story") continue;
                    if(
                        (int)$media["updated_at"] > $minTrackingTimeBetween &&
                        !($media["type"] === "story" && (((int)$media["timestamp"] + (23 * 3600)) <= time())) //Story expiring in less than 1 hour
                    ) continue;

                    $worker?->log("Added creator $creatorId on campaign $campaignId for post " . $media["id"] . " on shortcode " . $media["shortcode"]);
                    $collector[] = [
                        "campaign_id" => $campaignId,
                        "lookup_id" => $creatorId,
                        "mid" => $media["mid"],
                        "shortcode" => $media["shortcode"],
                        "row_id" => $media["id"],
                        "type" => $media["type"],
                        "api" => $api,
                        "followers_count" => (int)$creator["followers_count"]
                    ];
                }
            }
        }

        return empty($collector) ? [] : array_chunk($collector, 5)[0];
    }







    public function queryCampaignMedias(array $mediaItems, ?CronWorker $worker = null): void {
        $scraper = new Scraper($this->crud);
        $scraper->setCookieManager($this->crud)->cookieSetDefault()->cookieAddToHeader();
        $mediaHandler = $this->crud->mediaLookup();
        $worker?->log("Attempting to update " . count($mediaItems));
        if(empty($mediaItems)) return;


        $mediaUpdates = $this->mediaLookupHandler($mediaItems, $worker);


        if(!empty($mediaUpdates)) { //Ensure they get their "updated_at" updated so they aren't fetched again instantly
            foreach ($mediaUpdates as $rowId => $params) $mediaHandler->update($params, ["id" => $rowId]);
        }

    }


    public function mediaLookupHandler(array $mediaItems, ?CronWorker $worker = null): array {
        $scraper = new Scraper($this->crud);
        $scraper->setCookieManager($this->crud)->cookieSetDefault()->cookieAddToHeader();
        $lookupHandler = $this->crud->lookupList();
        $mediaHandler = $this->crud->mediaLookup();
        $dataHandler = $this->crud->dataHandler();
        $api = new Medias();
        $api->init("instagram");

        $mediaUpdates = $collector = [];
        foreach ($mediaItems as $media) {
            $lookupId = $media["lookup_id"];
            $shortCode = $media["shortcode"];
            $mid = $media["mid"];
            $rowId = $media["row_id"];
            $useApi = $media["api"];
            $followersCount = $media["followers_count"];

            if(!array_key_exists($rowId, $mediaUpdates)) $mediaUpdates[$rowId] = ["updated_at" => time()];
            if($useApi) { //Query through the api
                if(!$api->isInit("instagram")) continue;
                $integration = $lookupHandler->getRelatedIntegration($lookupId);
                $worker?->log("Media " . $media["mid"] . " belongs to the API. Querying from api");

                $accessToken = $integration["item_token"];
                $insights = $api->reelsMediaInsight($mid, $accessToken);
                file_put_contents(TESTLOGS . "mediainsight.json", json_encode($insights, JSON_PRETTY_PRINT));
                if(empty($insights)) {
                    $worker?->log("Insights turned out to be empty...");
                    continue;
                }

                $insights["engagement_rate"] = $dataHandler->engagementRate(array_merge($insights, ["followers_count" => $followersCount]));
                $mediaHandler->update($insights, ["id" => $rowId]);
                $worker?->log("Updated media: " . $media["mid"] . " through the API");
                unset($mediaUpdates[$rowId]); //Already updated
                $collector[] = ["media" => $media, "insights" => $insights];
            }



            else { //Query through scraper
                $mediaInfo = is_string($mid) && str_contains($mid, "_") ? //Post found by hashtag scrape
                    null : $scraper->mediaInfo($mid);

                file_put_contents(ROOT . "testLogs/mediainfo-1.json", json_encode($mediaInfo, JSON_PRETTY_PRINT));

                $currentCookie = $scraper->getCurrentCookie();
                $newMediaId = null;
                if(empty($mediaInfo)) {
                    $i = 0;
                    while ($i < 1) {
                        $newMediaId = $scraper->getPostPageMediaId($shortCode);
                        if(is_numeric($newMediaId)) break;
                        $scraper->cookieUsageIncrement(false);
                        if(!$scraper->isUnusedCookies()) break;
                        $scraper->cookieSet()->cookieAddToHeader(); //Set new random cookie
                        $i++;
                    }

                    if(empty($newMediaId)) {
                        $worker?->log("Failed to scrape media id off of shortcode " . $media["shortcode"] . " as media failed initially by row-id: " . $media["row_id"] . ". Trying next time");
                        continue;
                    }
                    $mid = $newMediaId;
                    $scraper->setDefaultHeaders();
                    $scraper->cookieManualAddToHeader($currentCookie);
                    $mediaHandler->update(["mid" => $mid], ["id" => $media["row_id"]]); //Converting api mid to scraper mid
                }

                $mediaInfo = $scraper->mediaInfo($mid);
                file_put_contents(ROOT . "testLogs/mediainfo-2.json", json_encode($mediaInfo, JSON_PRETTY_PRINT));

                if(empty($mediaInfo)) {
                    $scraper->cookieUsageIncrement(false);
                    $worker?->log("Failed to scrape media with row-id: ".$media["row_id"].". Trying next time");
                    continue;
                }
                $scraper->cookieUsageIncrement();


                file_put_contents(ROOT . "testLogs/mediainfo.json", json_encode($mediaInfo, JSON_PRETTY_PRINT));
                $generalEdgeData = $dataHandler->getEdgeData($mediaInfo, ScraperNestedLists::POST_PAGE_MEDIA_INFO);
                file_put_contents(ROOT . "testLogs/generaledge.json", json_encode($mediaInfo, JSON_PRETTY_PRINT));

                switch ($generalEdgeData["media_type"]) {
                    default: $generalEdgeData["media_type"] = "IMAGE"; break;
                    case 2: $generalEdgeData["media_type"] = "VIDEO"; break;
//            case 8: $generalEdgeData["media_type"] = "CAROUSEL"; break;
                }

                $mediaEdge = [$dataHandler->getEdgeData($generalEdgeData, ScraperNestedLists::POST_PAGE_MEDIA_TO_MEDIA)];
                file_put_contents(ROOT . "testLogs/mediaEdge.json", json_encode($mediaEdge, JSON_PRETTY_PRINT));

                $mediaEdge = $dataHandler->exchangeMediaFields($mediaEdge, [], $this->crud->settings->download_media)[0];
                file_put_contents(ROOT . "testLogs/media_exchanged.json", json_encode($mediaEdge, JSON_PRETTY_PRINT));


                $mediaEdge["permalink"] = "https://instagram.com/p/" . $mediaEdge["shortcode"];
                $mediaEdge = array_merge(
                    $mediaEdge,
                    array(
                        "engagement_rate" => $dataHandler->engagementRate(
                            array_merge(
                                $mediaEdge,
                                array("followers_count" => $this->crud->nestedArray($lookupHandler->get($lookupId, ["followers_count"]), ["followers_count"], 0))
                            )
                        )
                    )
                );

                file_put_contents(ROOT . "testLogs/media_extracted.json", json_encode($mediaEdge, JSON_PRETTY_PRINT));


                $worker?->log("Inserting / updating media: " . $media["mid"]);
                $mediaEdge = $mediaHandler->processMedia($mediaEdge);
                $mediaHandler->insertNewMedia($mediaEdge, $lookupId);
                unset($mediaUpdates[$rowId]);
                $collector[] = ["media" => $media, "insights" => $mediaEdge];
            }
        }

        return $mediaUpdates;
    }







    public function tagMentionFlow(?CronWorker $worker = null): void {
        $campaignHandler = $this->crud->campaigns()->disableDepthCheck();
        $scraper = new Scraper($this->crud);
        $scraper->setCookieManager($this->crud)->cookieSetDefault()->cookieAddToHeader();
        if(!$this->crud->settings->use_scraper) {
            $worker?->log("Scraper is not enabled.");
            return;
        }

        $items = $this->tagMentionFlowFindPosts($worker, $campaignHandler);
        $worker?->log("Found " . count($items) . " uniquely owned campaign(s). ");
        if(empty($items)) return;

        $idsToUpdate = $this->runTagMentionQueries($items, $worker);
        $worker?->log("Finished processing media queries. " . count($idsToUpdate) . " campaign's will be updated.");
        if(empty($idsToUpdate)) return;
        foreach ($idsToUpdate as $id) $campaignHandler->update(["last_tag_discovery" => time()], ["id" => $id]);
        $worker?->log("Finished running, updated " . count($idsToUpdate) . " campaign's last_tag_discovery.");
    }


    public function runTagMentionQueries(array $items, ?CronWorker $worker = null): array {
        if(empty($items)) return [];
        $api = new Medias();
        $api->init("instagram");
        $campaignsToUpdate = [];

        foreach ($items as $item) {
            $integration = $item["owner_integration"];
            $campaigns = $item["items"];
            $worker?->log("The brand (" . $item["username"] . ") has " . count($campaigns) . " active campaigns to check for.");
            $earliestTimestamp = $this->tagMentionComparableTimestamp($campaigns);
            $worker?->log("Running media time comparing $earliestTimestamp (" . date("Y-m-d H:i:s", $earliestTimestamp) . ").");
            $taggedMedia = $api->taggedPage($integration["item_id"], $integration["item_token"], $earliestTimestamp);
            $worker?->log("The tagged media query yielded " . count($taggedMedia) . " posts of interest.");
            $this->tagMentionMediaHandle($taggedMedia, $campaigns, $worker);
            $campaignsToUpdate = array_merge($campaignsToUpdate, array_map(function ($item) { return $item["campaign"]["id"]; }, $campaigns));
        }

        return $campaignsToUpdate;
    }


    private function tagMentionMediaHandle(array $taggedMedia, array $campaignItems, ?CronWorker $worker = null): void {
        if(empty($taggedMedia) || empty($campaignItems)) return;
        $dataHandler = $this->crud->dataHandler();
        $mediaHandler = $this->crud->mediaLookup();
        $collection = [];

        file_put_contents(TESTLOGS ."medaicreaotooo.json", json_encode($campaignItems, JSON_PRETTY_PRINT));
        foreach ($campaignItems as $campaignItem) {
            $campaign = $campaignItem["campaign"];
            $creators = $campaignItem["creators"]; //list of id, username, followers_count
            $worker?->log("Running media handle for campaign " . $campaign["id"]);


            foreach ($taggedMedia as $i => $media) {
                $n = $i + 1;
                $ownerUsername = $media["username"];
                $timestamp = $media["timestamp"];
                $worker?->log("Media ($n) owner: $ownerUsername, and time: $timestamp, (" . date("Y-m-d H:i:s", $timestamp) . ").");
                if((int)$campaign["end"] < $timestamp) continue;

                foreach ($creators as $creator) {
                    if($creator["username"] !== $ownerUsername) continue;
                    $worker?->log("Creator ($ownerUsername) owns the current media.");
                    $data = $dataHandler->extractMediaData($media, true, true, $creator["followers_count"])[0];
                    $data["campaign_id"] = $campaign["id"];
                    $data["lookup_id"] = $creator["id"];
                    $collection[] = $data;
                    break;
                }
            }
        }

        file_put_contents(TESTLOGS ."mediarunnin.json", json_encode($collection, JSON_PRETTY_PRINT));

        $worker?->log(count($collection) . " new or previously fetched media was found.");
        if(empty($collection)) return;
        $worker?->log("Processing media...");
        $collection = $mediaHandler->processMedia($collection);
        $worker?->log("Inserting or updating media...");
        file_put_contents(TESTLOGS ."mediaprocseddeds.json", json_encode($collection, JSON_PRETTY_PRINT));
        $mediaHandler->insertNewMedia($collection);
        $worker->log("Finished media handle.");
    }



    private function tagMentionComparableTimestamp(array $campaignItems): int {
        $collection = [];
        foreach ($campaignItems as $campaignItem) {
            $campaign = $campaignItem["campaign"];
            $collection[] = max((int)$campaign["last_tag_discovery"], (int)$campaign["start"]);
        }
        sort($collection);
        return $collection[0];
    }



    public function tagMentionFlowFindPosts(?CronWorker $worker = null, ?Campaigns $campaignHandler = null): array {
        if($campaignHandler === null) $campaignHandler = $this->crud->campaigns()->disableDepthCheck();
        $minTrackingTimeBetween = time() - (3600);
        $campaigns = $campaignHandler->getByX([
            "tracking" => [0,2],
            "discovery_active" => 1,
            "last_tag_discovery" => QuerySelector::set(["last_tag_discovery", "<=", $minTrackingTimeBetween]),
            "start" => QuerySelector::set(["start", "<=", time()]),
            "somekey" => QuerySelector::set(["last_tag_discovery", "<", "end", "SQL"]),
        ]);

        $worker?->log("Initial campaign-count fitting query-criteria: " . count($campaigns));
        if(empty($campaigns)) return [];

        $collector = [];
        foreach ($campaigns as $campaign) {
            $creators = $campaignHandler->getCampaignCreators($campaign["id"], ["id", "username", "followers_count"]);
            $ownerIntegration = $campaignHandler->ownerIntegration($campaign);
            if(empty($ownerIntegration) || empty($creators)) {
                $campaignHandler->update(["last_tag_discovery" => time()], ["id" => $campaign["id"]]);
                $worker?->log((empty($ownerIntegration) ? "Owner integration was not found" : "The campaign has no creators"));
                continue;
            }
            if(!array_key_exists($ownerIntegration["id"], $collector))  $collector[$ownerIntegration["id"]] = [
                "username" => $ownerIntegration["item_name"],
                "owner_integration" => $ownerIntegration,
                "items" => []
            ];
            $collector[$ownerIntegration["id"]]["items"][] = [
                "creators" => $creators,
                "campaign" => $campaign
            ];
        }

        $worker?->log("Found " . count($collector) . " integrations to Tag-track");
        return $collector;
    }





}


















