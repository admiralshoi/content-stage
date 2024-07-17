<?php

namespace classes\src\Object\objects;
use classes\src\AbstractCrudObject;
use classes\src\Enum\HandlerErrors;
use classes\src\Enum\ScraperNestedLists;
use classes\src\Media\Medias;
use classes\src\Object\CronWorker;
use classes\src\Object\Scraper;
use classes\src\Object\transformer\URL;
use JetBrains\PhpStorm\ArrayShape;

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

class LookupMedia {
    private AbstractCrudObject $crud;
    private int $requestingUsersAccessLevel = 0;
    private int $requestingUsersId = 0;
    private bool $disabledDepthCheck = false;
    public bool $isError = true;
    private array $responseError = array(
        "status" => "error",
        "error" => array(
            "message" => "",
            "code" => 101
        )
    );
    private array $responseSuccess = array(
        "status" => "success",
        "data" => array()
    );


    function __construct(AbstractCrudObject $crud) {
        $this->crud = $crud;
        if(isset($_SESSION["access_level"])) $this->requestingUsersAccessLevel = $_SESSION["access_level"];
        if(isset($_SESSION["uid"])) $this->requestingUsersId = $_SESSION["uid"];
    }


    public function getResponse(): array {
        return $this->isError ? $this->responseError : $this->responseSuccess;
    }




    private function access(int $actionType): bool {
        if($this->disabledDepthCheck) return true;
        return $this->crud->hasAccess("node","lookup_media",$actionType, $this->requestingUsersAccessLevel);
    }
    public function disableDepthCheck(): static { $this->disabledDepthCheck = true; return $this; }
    public function enableDepthCheck(): static { $this->disabledDepthCheck = false; return $this; }


    /*
     * Core CRUD features END
     */

    public function getByX(array $params = array(), array $fields = array(), string $customSql = ""): array {
        if(!$this->access(READ_ACTION)) return array();
        return $this->crud->retrieve("media",$params, $fields,$customSql);
    }
    public function create(array $params): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        if(!array_key_exists("created_at", $params)) $params["created_at"] = time();
        return $this->crud->create("media", array_keys($params), $params);
    }
    public function update(array $params, array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        return $this->crud->update("media", array_keys($params), $params, $identifier);
    }
    public function delete(array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        return $this->crud->delete("media", $identifier);
    }
    public function get(string|int $id, array $fields = array()): array {
        $item = $this->getByX(array("id" => $id), $fields);
        return array_key_exists(0, $item) ? $item[0] : $item;
    }
    public function getByMid(string|int $mid, array $fields = array()): array {
        $item = $this->getByX(array("mid" => $mid), $fields);
        return array_key_exists(0, $item) ? $item[0] : $item;
    }


    public function getAccountCampaignMedia(string|int $lookupId, string|int $campaignId, array $fields = []): array {
        return $this->getByX(["lookup_id" => $lookupId, "campaign_id" => $campaignId], $fields);
    }
    public function getLatestAccountMedia(string|int $lookupId): array {
        $rows = $this->getByX([], [],
            "SELECT * FROM lookup_media WHERE lookup_id = $lookupId ORDER BY timestamp DESC LIMIT 1");
        return array_key_exists(0, $rows) ? $rows[0] : $rows;
    }

    public function getLatestAccountMediaId(string|int $lookupId): int {
        $row = $this->getLatestAccountMedia($lookupId);
        return array_key_exists("mid", $row) ? (int)$row["mid"] : 0;
    }


    public function getMediasToBeQueried(): array {
        $rows = $this->getByX(["is_finished" => 0]);
        if(empty($rows)) return [];

        $queryInterval = $this->crud->appMeta()->get("analytics_interval");
        $timeCap = strtotime("-$queryInterval hour");

        return array_values(array_filter($rows, function ($row) use ($timeCap) {
            return (int)$row["updated_at"] === (int)$row["created_at"] || (int)$row["updated_at"] <= $timeCap;
        }));
    }


    public function getMediaWithCampaign(): array {
        return $this->getByX(
            [], [],
            "SELECT * FROM lookup_media WHERE campaign_id != 0"
        );
    }


    public function getMediaWithCampaignToday(): array {
        $timeStart = strtotime("today");
        $timeEnd = strtotime("tomorrow");
        return $this->getByX(
            [], [],
            "SELECT * FROM lookup_media WHERE campaign_id != 0 AND timestamp >= $timeStart AND timestamp < $timeEnd"
        );
    }




    public function mentionLiveTracking(array $args): array {
        if($this->crud->isCreator()) return [];
        foreach (["offset", "page_size"] as $key) if(!array_key_exists($key, $args)) return [];

        $offset = (int)$args["offset"];
        $pageSize = (int)$args["page_size"];
        $sql = "SELECT * FROM lookup_media WHERE origin != 'profile_scrape' AND id > $offset";

        if($this->crud->isBrand()) {
            $myCampaigns = $this->crud->campaigns()->getByX(["owned_by" => $this->requestingUsersId], ["id"]);
            if(empty($myCampaigns)) return  [];

            $myCampaignIds = array_map(function ($row) { return $row["id"]; }, $myCampaigns);
            $myCampaignRelations = $this->crud->campaignRelations()->getByX(["campaign_id" => $myCampaignIds]);
            if(empty($myCampaignRelations)) return [];

            $myRelationsCreatorIds = array_map(function ($row) { return $row["creator_id"]; }, $myCampaignRelations);
            $sql .= " AND lookup_id IN (" . implode(",", $myRelationsCreatorIds) . ")";
        }

        $sql .= " LIMIT $pageSize";
        $rows = $this->getByX([], [], $sql);


        if(empty($rows)) return [];
        $lookupHandler = $this->crud->lookupList();
        $collector = [];

        foreach ($rows as $row) {
            $creator = $lookupHandler->getWithMergedData($row["lookup_id"]);
            if(empty($creator)) continue;

            $collector[] = array_merge(
                $row,
                [
                    "username" => $creator["username"],
                    "creator_id" => $creator["id"],
                    "followers_count" => $creator["followers_count"],
                    "media_count" => $creator["media_count"],
                    "engagement_rate" => $creator["engagement_rate"],
                    "display_date" => date("M d, H:i", $row["timestamp"]),
                    "campaign_link" => URL::addParam(HOST, ["page" => "campaigns", "campaign" => $row["campaign_id"]], true),
                    "creator_link" => URL::addParam(HOST, ["page" => "creators", "creator" => $creator["id"]], true),
                    "permalink" => $row["permalink"],
                ]
            );
        }
        return $collector;
    }







    public function processMedia(array $medias): array {
        if(empty($medias)) return [];
        if(!array_key_exists(0, $medias)) $medias = [$medias];
        $dataHandler = $this->crud->dataHandler();

        $collector = [];
        foreach ($medias as $media) {
            $item = $media;
            if(array_key_exists("id", $item)) {
                $item["mid"] = $item["id"];
                unset($item["id"]);
            }
            if(array_key_exists("media_url", $item)) {
                $item["display_url"] = $item["media_url"];
                unset($item["media_url"]);
            }
            if(!array_key_exists("pinned", $item)) $item["pinned"] = 0;
            if(!array_key_exists("permalink", $item) && array_key_exists("shortcode", $item))
                $item["permalink"] = "https://instagram.com/p/" . $item["shortcode"];
            elseif(array_key_exists("permalink", $item) && !array_key_exists("shortcode", $item))
                $item["shortcode"] = $dataHandler->instagramUrlShortCode($item["permalink"]);

            if(array_key_exists("carousel", $item) && is_array($item["carousel"])) $item["carousel"] = (int)!empty($item["carousel"]);

            if($this->crud->settings->download_media) $item = $dataHandler->downloadMediasAndUpdateUrl($item)[0];
            $collector[] = $item;
        }

        return $collector;
    }




    public function keyJsonEncoding(array $mediaData, bool $encode = true): array {
        $keys = array("hashtags", "location");
        foreach ($keys as $key) {
            if(array_key_exists($key, $mediaData) && $encode && is_array($mediaData[$key])) $mediaData[$key] = json_encode($mediaData[$key]);
            if(array_key_exists($key, $mediaData) && !$encode && !is_array($mediaData[$key]) && !empty($mediaData[$key])) $mediaData[$key] = json_decode($mediaData[$key], true);
        }
        return $mediaData;
    }

    public function insertNewMedia(array $medias, string|int $lookupId = 0): void {
        if(empty($medias)) return;
        if(!array_key_exists(0, $medias)) $medias = [$medias];

        file_put_contents(TESTLOGS . "mediasBeforeInsert.json", json_encode($medias, JSON_PRETTY_PRINT));

        foreach ($medias as $media) {
            $item = $media;
            if(array_key_exists("id", $item)) {
                $item["mid"] = $item["id"];
                unset($item["id"]);
            }
            if(array_key_exists("media_url", $item)) {
                $item["display_url"] = $item["media_url"];
                unset($item["media_url"]);
            }
            if(array_key_exists("media_product_type", $item)) {
                if(!array_key_exists("media_type", $item)) $item["media_type"] = $item["media_product_type"];
                unset($item["media_product_type"]);
            }
            if(array_key_exists("insights", $item)) unset($item["insights"]);
            if(!array_key_exists("pinned", $item)) $item["pinned"] = 0;
            if(array_key_exists("media_type", $item)) {
                if($item["media_type"] === "REELS") $item["media_type"] = "VIDEO";
                if($item["media_type"] === "FEED") $item["media_type"] = "IMAGE";
            }

            if(!array_key_exists("lookup_id", $item)) $item["lookup_id"] = $lookupId;

            foreach (["mid", "shortcode"] as $key) {
                if(array_key_exists($key, $item) && !empty($this->getByX([$key => $item[$key]]))) {
                    $value = $item[$key];
                    if(array_key_exists("display_url", $item)) unset($item["display_url"]);
                    if(array_key_exists("video_url", $item)) unset($item["video_url"]);
                    if(array_key_exists("permalink", $item)) unset($item["permalink"]);
                    if(array_key_exists("shortcode", $item)) unset($item["shortcode"]);
                    if(array_key_exists("origin", $item)) unset($item["origin"]);
                    if(array_key_exists("type", $item)) unset($item["type"]);
                    if(array_key_exists("media_type", $item)) unset($item["media_type"]);
                    if(array_key_exists("mid", $item)) unset($item["mid"]);
                    if(array_key_exists("timestamp", $item)) unset($item["timestamp"]);
                    if(array_key_exists("campaign_id", $item)) unset($item["campaign_id"]);

                    file_put_contents(TESTLOGS ."clooseone.json", json_encode([
                        [$key, $value],$item, $this->keyJsonEncoding($item)
                    ], JSON_PRETTY_PRINT));
                    $this->update($this->keyJsonEncoding($item), [$key => $value]);
                    continue 2;
                }
            }

            file_put_contents(TESTLOGS . "mediasBeforeInsertTrim.json", json_encode([$item, $this->keyJsonEncoding($item)], JSON_PRETTY_PRINT));
            $this->create($this->keyJsonEncoding($item));
        }
    }







    public function queryTaggedMediaLookup(?CronWorker $worker = null): void {
        $lastQueryTime = time() - (3600 * .5);
        $lookupHandler = $this->crud->lookupList();
        $integrationHandler = $this->crud->integrations();
        $campaignHandler = $this->crud->campaigns();
        $handler = $this->crud->handler();
        $dataHandler = $this->crud->dataHandler();


        $integrations = $integrationHandler->getByX(
            [], [],
            "SELECT id,ig_scraper_id,ig_name FROM integrations WHERE last_queried <= $lastQueryTime ORDER BY last_queried DESC LIMIT 5"
        );
        $worker?->log("Found " . count($integrations) . " integrations to pull tagged-data from");
        if(empty($integrations)) return;

        $scraper = new Scraper($this->crud);
        $scraper->setCookieManager($this->crud)->cookieSetDefault()->cookieAddToHeader();

        $mediaCollector = [];
        foreach ($integrations as $integration) {
            if(!$scraper->isUnusedCookies()) break;
            $mediaData = $handler->userTaggedPage($integration["ig_scraper_id"], $scraper, $integration["ig_name"]);

            $worker?->log("Found " . count($mediaData) . " tagged media from integration: " . $integration["ig_name"]);
            $integrationHandler->update(["last_queried" => time()], ["id" => $integration["id"]]);
            if(empty($mediaData)) continue;

            foreach ($mediaData as $media) {
                $creator = $lookupHandler->getByUsername($media["username"], 0, ["id", "username"]);
                if(empty($creator)) {
                    $worker?->log("The media is from an unknown creator: " . $media["username"] . ". Skipping...");
                    continue;
                }

                $creatorId = (int)$creator["id"];
                $creatorActiveCampaign = $campaignHandler->creatorActiveCampaign($creatorId);
                $item = $media;

                $worker?->log("Found a media from known creator: " . $creator["username"]);

                if($campaignHandler->isCampaignActive($creatorActiveCampaign, (int)$media["timestamp"])) {
                    if($creatorActiveCampaign["content_type"] === "mixed" || $media["type"] === $creatorActiveCampaign["content_type"])
                        $item["campaign_id"] = (int)$creatorActiveCampaign["id"];
                    elseif($creatorActiveCampaign["content_type"] === "reel" && $this->crud->nestedArray($item, ["media_type"]) === "VIDEO")
                        $item["campaign_id"] = (int)$creatorActiveCampaign["id"];
                }
                $item["origin"] = "tagged_page";

                if(array_key_exists("campaign_id", $item)) $worker?->log("The media is a campaign-relevant media" . $item["campaign_id"]);

                if(empty($this->getByX(["mid" => $item["mid"]]))) {
                    $worker?->log("The media is not previously known");
                    $item = $dataHandler->downloadMediasAndUpdateUrl($item)[0];
                }
                else $worker?->log("The media is already known");
                if(!array_key_exists($creatorId, $mediaCollector)) $mediaCollector[$creatorId] = [];
                $mediaCollector[$creatorId][] = $item;
            }
        }


        if(empty($mediaCollector)) return;
        foreach ($mediaCollector as $lookupId => $medias) $this->insertNewMedia($medias, $lookupId);
    }








}