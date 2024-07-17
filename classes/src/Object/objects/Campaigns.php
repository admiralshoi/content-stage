<?php

namespace classes\src\Object\objects;

use classes\src\AbstractCrudObject;
use classes\src\Enum\EmailTypes;
use classes\src\Enum\HandlerErrors;
use classes\src\Object\CronWorker;
use classes\src\Object\transformer\URL;

class Campaigns {

    private AbstractCrudObject $crud;
    private int $requestingUsersAccessLevel = 0;
    private int $requestingUsersId = 0;
    private bool $disabledDepthCheck = false;
    public bool $isError = true;

    public const TRACKING_MENTION = 0;
    public const TRACKING_HASHTAG = 1;
    public const TRACKING_BOTH = 2;
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
        return $this->crud->hasAccess("node","campaigns",$actionType, $this->requestingUsersAccessLevel);
    }


    public function disableDepthCheck(): static { $this->disabledDepthCheck = true; return $this; }
    public function enableDepthCheck(): static { $this->disabledDepthCheck = false; return $this; }

    /*
     * Core CRUD features END
     */

    public function getByX(array $params = array(), array $fields = array(), string $customSql = ""): array {
        if(!$this->access(READ_ACTION)) return array();
        return $this->crud->retrieve("campaigns",$params, $fields,$customSql);
    }
    private function create(array $params): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        if(!array_key_exists("created_at", $params)) $params["created_at"] = time();
        return $this->crud->create("campaigns", array_keys($params), $params);
    }
    public function update(array $params, array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        return $this->crud->update("campaigns", array_keys($params), $params, $identifier);
    }
    private function delete(array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        return $this->crud->delete("campaigns", $identifier);
    }
    public function get(string|int $id, array $fields = array()): array {
        $item = $this->getByX(array("id" => $id), $fields);
        return array_key_exists(0, $item) ? $item[0] : $item;
    }

    public function canModify(): bool { return $this->access(MODIFY_ACTION); }

    public function getActiveCampaignsArbitraryEndTime(int $timeStop): array {
        $currentTime = time();
        return $this->getByX([],[], "SELECT * FROM campaigns WHERE start <= $currentTime AND end > $timeStop");
    }


    public function isCampaignActive(array $campaign, int $time = 0): bool {
        foreach (["start", "end"] as $key) if(!array_key_exists($key, $campaign)) return false;
        if($time === 0) $time = time();
        return (int)$campaign["start"] <= $time && (int)$campaign["end"] > $time;
    }

    public function isCampaignUpcoming(array $campaign, int $time = 0): bool {
        foreach (["start", "end"] as $key) if(!array_key_exists($key, $campaign)) return false;
        if($time === 0) $time = time();
        return (int)$campaign["start"] > $time;
    }

    public function isCampaignPrevious(array $campaign, int $time = 0): bool {
        foreach (["start", "end"] as $key) if(!array_key_exists($key, $campaign)) return false;
        if($time === 0) $time = time();
        return (int)$campaign["end"] <= $time;
    }


    private function creatorCampaignIds(array $campaignIds = [], string|int $creatorId = 0): array {
        $relations = $this->crud->campaignRelations()->disableDepthCheck()->getByX(["creator_id" => $creatorId]);
        return array_merge(
            $campaignIds,
            array_map(function ($item) { return $item["campaign_id"]; }, $relations)
        );
    }
    private function getTimeCampaignLogic(string $logicSql, array $campaignIds = [], string|int $creatorId = 0): array {
        if($this->crud->isCreator()) $campaignIds = $this->creatorCampaignIds($campaignIds, $creatorId);
        if($this->crud->isCreator() && empty($campaignIds)) return [];
        if(!empty($campaignIds)) $logicSql .= " AND id IN (" . implode(",", $campaignIds) . ")";
        if($this->crud->isBrand()) $logicSql .= " AND owned_by = $this->requestingUsersId";
        return $this->getByX([], [], $logicSql );
    }

    public function getActiveCampaigns(array $campaignIds = [], int $time = 0, string|int $creatorId = 0): array {
        if($time === 0) $time = time();
        return $this->getTimeCampaignLogic("SELECT * FROM campaigns WHERE start <= $time AND end > $time", $campaignIds, $creatorId);
    }

    public function getUpcomingCampaigns(array $campaignIds = [], string|int $creatorId = 0): array {
        $time = time();
        return $this->getTimeCampaignLogic("SELECT * FROM campaigns WHERE start > $time", $campaignIds, $creatorId);
    }

    public function getPastCampaigns(array $campaignIds = [], string|int $creatorId = 0): array {
        $time = time();
        return $this->getTimeCampaignLogic("SELECT * FROM campaigns WHERE end <= $time", $campaignIds, $creatorId);
    }



    public function creatorActiveCampaign(string|int $creatorId = 0, string $username = ""): array {
        $lookupHandler = $this->crud->lookupList()->disableDepthCheck();
        $campaignRelations = $this->crud->campaignRelations()->disableDepthCheck();
        if(empty($creatorId)) {
            $creator = $lookupHandler->getByUsername($username);
            if(empty($creator)) return [];
            $creatorId = (int)$creator["id"];
        }
        else {
            $creator = $lookupHandler->get($creatorId);
            if(empty($creator)) return [];
        }

        $relations = $campaignRelations->getByX(["creator_id" => $creatorId], ["campaign_id"]);
        if(empty($relations)) return [];
        $campaignIds = array_map(function ($item) { return (int)$item["campaign_id"]; }, $relations);

        $activeCampaigns = $this->getActiveCampaigns($campaignIds);
        return array_key_exists(0, $activeCampaigns) ? $activeCampaigns[0] : $activeCampaigns;
    }



    public function createCampaign(array $args): static {
        if(!$this->access(MODIFY_ACTION)) {
            $this->responseError = HandlerErrors::INSUFFICIENT_PERMISSIONS;
            return $this;
        }

        if(!array_key_exists("data", $args)) return $this;
        foreach (
            ["name", "date_range", "ppc", "content_type", "creators"] as $key
        ) if(!array_key_exists($key, $args["data"]) || empty($args["data"][$key])) {
            $this->responseError = HandlerErrors::NO_INPUT;
            return $this;
        }


        $data = $args["data"];
        $name = trim($data["name"]);
        $contentType = trim($data["content_type"]);
        $dateRange = $data["date_range"];
        $ppc = (int)$data["ppc"];
        $creators = $data["creators"];
        $existingCampaignId = array_key_exists("campaign_id", $data) ? $data["campaign_id"] : 0;
        $trackingHashtag = array_key_exists("tracking_hashtag", $data) ? $data["tracking_hashtag"] : null;
        $creatorsBulk = array_key_exists("creators_bulk", $data) ? $data["creators_bulk"] : "";
        $owner = array_key_exists("owner", $data) ? $data["owner"] : null;
        $tracking = match (($data["tracking"] ?? "none")) {
            default => self::TRACKING_MENTION,
            "hashtag" => self::TRACKING_HASHTAG,
            "both" => self::TRACKING_BOTH,
        };

        if($this->crud->isBrand()) $owner = $this->requestingUsersId;

        if(!empty($owner) && !empty($creators) && !$this->crud->isAdmin($this->crud->user()->accessLevel($owner))) {
            $creatorRelations = $this->crud->creatorRelations()->getByUserId($owner, ["lookup_id"]);
            $insufficientAccess = false;
            if(!empty($creatorRelations)) {
                $ids = array_map(function ($row) { return $row["lookup_id"]; }, $creatorRelations);
                foreach ($creators as $i => $creatorId) {
                    if(!in_array($creatorId, $ids)) {
                        if($existingCampaignId) {
                            $insufficientAccess = true;
                            break;
                        }
                        else unset($creators[$i]);
                    }
                }

            }
            else $insufficientAccess = true;

            if($insufficientAccess) {
                $this->responseError = HandlerErrors::INSUFFICIENT_CREATOR_RELATIONS;
                return $this;
            }
        }
        if(empty($creators)) {
            $this->responseError = HandlerErrors::NO_AVAILABLE_CREATORS;
            return $this;
        }







        foreach (["start", "end"] as $key) if(!array_key_exists($key, $dateRange)) {
            $this->responseError = HandlerErrors::NO_INPUT;
            return $this;
        }

        $start = (int)$dateRange["start"];
        $end = (int)$dateRange["end"];

        if($start >= $end || $end <= strtotime("tomorrow")) {
            $this->responseError = HandlerErrors::CAMPAIGN_CREATION_TIME_RANGE_ERROR;
            return $this;
        }

        $lookupList = $this->crud->lookupList();
        $campaignRelations = $this->crud->campaignRelations();

        $params = [
            "name" => $name,
            "start" =>  $start,
            "end" => $end,
            "content_type" => $contentType,
            "ppc" => $ppc,
            "owned_by" => $owner,
            "tracking" => $tracking,
            "hashtag" => $trackingHashtag,
            "discovery_active" => 1,
//            "discovery_active" => in_array($tracking, [self::TRACKING_BOTH, self::TRACKING_HASHTAG]) ? 1 : 0,
        ];


        if(!empty($existingCampaignId)) {
            $row = $this->get($existingCampaignId);
            if(empty($row)) {
                $this->responseError = HandlerErrors::CAMPAIGN_CREATION_ERROR;
                return $this;
            }

            $this->update($params, ["id" => $existingCampaignId]);
            $campaignId = $existingCampaignId;
        }
        else {
            $creationStatus = $this->create($params);
            if(!$creationStatus) {
                $this->responseError = HandlerErrors::CAMPAIGN_CREATION_ERROR;
                return $this;
            }

            usleep(1000);
            $row = $this->getByX($params);
            if(empty($row)) {
                $this->responseError = HandlerErrors::CAMPAIGN_CREATION_ERROR;
                return $this;
            }

            $row = $row[0];
            $campaignId = (int)$row["id"];
            while(true) {
                $shareLink = $this->crud->passwordHashing(rand(round(time() * .3), time() * 93));
                if(empty($this->getByX(["share_token" => $shareLink]))) break;
            }
            $this->update(["share_token" => $shareLink], ["id" => $campaignId]);
        }

        if(empty($creatorsBulk)) {
            if(!empty($existingCampaignId)) {
                $relations = $campaignRelations->getByX(["campaign_id" => $campaignId]);
                if(!empty($relations)) {
                    foreach ($relations as $relation)
                        if (!in_array($relation["creator_id"], $creators)) $campaignRelations->delete(["campaign_id" => $campaignId, "creator_id" => $relation["creator_id"]]);
                }
            }

            foreach ($creators as $creatorId) {
                if(empty($lookupList->get($creatorId))) continue;
                $relationParam = [
                    "creator_id" => $creatorId,
                    "campaign_id" => $campaignId,
                ];

                if(empty($campaignRelations->getByX($relationParam))) $campaignRelations->create($relationParam);
            }
        }
        else {
            $usernames = explode(",", $creatorsBulk);
            if(empty($usernames)) {
                $this->responseError = HandlerErrors::CAMPAIGN_CREATION_ERROR;
                return $this;
            }

            foreach ($usernames as $username) {
                $creator = $lookupList->getByUsername(trim($username));
                if(empty($creator)) continue;

                $creatorId = (int)$creator["id"];
                $relationParam = [
                    "creator_id" => $creatorId,
                    "campaign_id" => $campaignId,
                ];

                if(empty($campaignRelations->getByX($relationParam))) $campaignRelations->create($relationParam);
            }
        }


        $this->isError = false;
        return $this;
    }


    public function campaignRemoveCreator(array $args): static {
        if(!array_key_exists("data", $args)) return $this;
        foreach (
            ["campaign_id", "creator_id"] as $key
        ) if(!array_key_exists($key, $args["data"]) || empty($args["data"][$key])) {
            $this->responseError = HandlerErrors::NO_INPUT;
            return $this;
        }

        $data = $args["data"];
        $creatorId = $data["creator_id"];
        $campaignId = $data["campaign_id"];

        $relationHandler = $this->crud->campaignRelations();
        $relationParam = [
            "creator_id" => $creatorId,
            "campaign_id" => $campaignId,
        ];
        if(!empty($relationHandler->getByX($relationParam))) $relationHandler->delete($relationParam);

        $this->isError = false;
        $this->responseSuccess["data"] = "Successfully removed the creator from the campaign";
        return $this;
    }




    public function getCampaignDetails(array $args): array {
        if(!array_key_exists("campaign_id", $args)) return [];
        $campaignId = $args["campaign_id"];

        $campaign = $this->get($campaignId);
        if(empty($campaign)) return [];

        $relationHandler = $this->crud->campaignRelations();
        $lookupHandler = $this->crud->lookupList();
        $relations = $relationHandler->getByX(["campaign_id" => $campaignId], ["creator_id"]);

        $creators = empty($relations) ? [] : array_map(function ($relation) use ($lookupHandler) {
            return $lookupHandler->get($relation["creator_id"], ["username", "id"]);
        }, $relations);

        return array_merge(
            $campaign,
            [
                "creators" => $creators
            ]
        );
    }


    public function getCampaignCreators(string|int $campaignId, array $creatorFields = []): array {
        $campaignRelations = $this->crud->campaignRelations();
        $lookupList = $this->crud->lookupList();
        $items = $campaignRelations->getByX(["campaign_id" => $campaignId], ["creator_id"]);
        if(empty($items)) return [];

        $collector = [];
        foreach ($items as $item) {
            $creator = $lookupList->get($item["creator_id"], $creatorFields);
            if(!empty($creator)) $collector[] = $creator;
        }
        return $collector;
    }


    public function findHashtagsToTrack(?CronWorker $worker = null): array {
        $minTrackingTimeBetween = time() - (3600);
        $sql = "SELECT * FROM campaigns WHERE (tracking = 1 OR tracking = 2) AND discovery_active = 1 AND last_discovery <= $minTrackingTimeBetween AND start <= " . time();
        $campaigns = $this->getByX([], [], $sql);
        $worker?->log("Initial campaign count fitting criteria: " . count($campaigns));
        if(empty($campaigns)) return [];

        $trackingList = [];
        foreach ($campaigns as $campaign) {
            $hashtag = trim(str_replace("#", "", $campaign["hashtag"]));
            if(empty($hashtag)) {
                $worker?->log("Hashtag is empty from campaign " . $campaign["id"]);
                continue;
            }
            if(strlen($hashtag) > 25) {
                $worker?->log("String length of the hashtag may not be longer than 25. Change it for campaign " . $campaign["id"]);
                continue;
            }

            $item = $campaign;
            $item["creators"] = $this->getCampaignCreators($campaign["id"], ["id", "username"]);
            if(empty($item["creators"])) continue;

            if(!array_key_exists($hashtag, $trackingList)) $trackingList[$hashtag] = [];
            $trackingList[$hashtag][] = $item;
        }
        return array_chunk($trackingList, 5)[0];
    }



    public function hashtagTracking(array $trackingList, ?CronWorker $worker = null): void {
        if(empty($trackingList)) return;

        $mediaHandler = $this->crud->mediaLookup();
        $handler = $this->crud->handler();
        $scraper = $this->crud->scraper();
        $scraper->setCookieManager($this->crud)->cookieSetDefault()->cookieAddToHeader();
        $campaignUpdateParams = [];
        $counter = 0;


        foreach ($trackingList as $hashtag => $campaigns) {
            foreach ($campaigns as $campaign) {
                $campaignId = $campaign["id"];
                $campaignUpdateParams[$campaignId] = ["last_discovery" => time()];
                if(!$this->isCampaignActive($campaign)) {
                    $campaignUpdateParams[$campaignId]["discovery_active"] = 0;
                    $worker?->log("As the campaign $campaignId has ended, this is the last tracking for this campaign");
                }
            }


            $result = $handler->hashtagExplore($hashtag, $scraper);
            if(empty($result) || !array_key_exists("media", $result)) {
                $worker?->log("Found no data for hashtag $hashtag");
                continue;
            }

            $medias = $result["media"];
            $mediaUsernames = array_map(function ($media) { return $media["username"]; }, $medias);
            foreach ($campaigns as $campaign) {
                $campaignId = $campaign["id"];

                foreach ($campaign["creators"] as $creator) {
                    $username = $creator["username"];
                    $lookupId = $creator["id"];
                    if(!in_array($username, $mediaUsernames)) continue;
                    $worker?->log("Found a post from username $username with hashtag $hashtag");

                    $relevantMedias = array_values(array_filter($medias, function ($media) use ($username, $campaign) {
                        return $media["username"] === $username && $this->isCampaignActive($campaign, (int)$media["timestamp"]);
                    }));
                    if(empty($relevantMedias)) {
                        $worker?->log("After filtering, we found no relevant posts for username $username on campaign $campaignId");
                        continue;
                    }

                    $relevantMedias = array_map(function ($media) use ($campaignId) {
                        return array_merge($media, [
                            "type" => "post",
                            "origin" => "hashtag_scrape",
                            "campaign_id" => $campaignId
                        ]);
                    }, $relevantMedias);

                    $counter += count($relevantMedias);
                    $worker?->log("Proceeding to store and insert " . count($relevantMedias) . " new posts for user $username on campaign $campaignId");
                    $relevantMedias = $mediaHandler->processMedia($relevantMedias);
                    $mediaHandler->insertNewMedia($relevantMedias, $lookupId);
                }
            }
        }

        foreach ($campaignUpdateParams as $campaignId => $params) $this->update($params,["id" => $campaignId]);
        $worker?->log("Finished hashtag tracking. In total we found $counter new posts");
    }




    public function campaignResultToCsv(array $args): array {
        if(!array_key_exists("campaign_id", $args)) return ["status" => "error", "error" => ["message" => "Missing campaign id"]];
        $campaignId = $args["campaign_id"];
        $isCreator = $this->crud->isCreator();

        $lookupList = $this->crud->lookupList();
        $campaignRelations = $this->crud->campaignRelations();
        $mediaHandler = $this->crud->mediaLookup();

        $campaign = $this->get($campaignId);
        if(empty($campaign)) return ["status" => "error", "error" => ["message" => "Invalid campaign id"]];
        if($this->crud->isBrand() && $campaign["owned_by"] !== $_SESSION["uid"]) return ["status" => "error", "error" => ["message" => "Invalid campaign id"]];
        if(
            $this->crud->isCreator() &&
            !isset($_SESSION["guest"]) &&
            empty($campaignRelations->getByX(["campaign_id" => $campaign["id"], "creator_id" => $this->crud->creatorId()]))
        ) return ["status" => "error", "error" => ["message" => "Invalid campaign id"]];

        $relationParam = ["campaign_id" => $campaignId];
        if($isCreator) $relationParam["creator_id"] = $this->crud->creatorId();
        $relations = $campaignRelations->getByX($relationParam, ["creator_id"]);

        if(empty($relations)) return ["status" => "error", "error" => ["message" => "Invalid campaign"]];
        $relationCreatorIds = array_map(function ($relation) { return $relation["creator_id"]; }, $relations);

        $contentParam = ["campaign_id" => $campaignId];
        if($isCreator) $contentParam["lookup_id"] = $this->crud->creatorId();

        $posts = $mediaHandler->getByX(array_merge($contentParam, ["type" => "post"]), ["play_count", "view_count", "type", "comments_count", "like_count"]);
        $stories = $mediaHandler->getByX(array_merge($contentParam, ["type" => "story"]), ["play_count", "view_count", "type", "comments_count", "like_count"]);



        $creatorCount = count($relations);
        $totalPosts = count($posts) + count($stories);
        $expectedPosts = (int)$campaign["ppc"] * ($isCreator ? 1 : $creatorCount);
        $completion = $expectedPosts === 0 ? 0: min(round($totalPosts / $expectedPosts * 100,2), 100);
        $creators = $lookupList->getByX(["deactivated" => 0, "id" => $relationCreatorIds], ["id", "username", "followers_count"]);

        $totalFollowers = (int)array_reduce($creators, function ($initial, $creator) use ($lookupList) {
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
            return (!isset($initial) ? 0 : $initial) + (!in_array($item["type"], ["VIDEO", "REELS", "STORY"]) ? 0 :
                    ((int)$item["play_count"] > 0 ? (int)$item["play_count"] : (int)$item["view_count"]));
        });



        $campaignName = str_replace(" ", "-", strtolower($campaign["name"]));

        $filename = $campaignName . "_" . rand(10,10000) . "_" . date("M", $campaign["start"]) . "_" . date("M", $campaign["end"]) . ".csv";
        $dir = "objects/content/";
        $totals = [
            "name" => "total",
            "completion" => $completion,
            "potential_audience" => $totalFollowers,
            "mentions" => $totalPosts,
            "creators" => count($creators),
            "interactions" => $totalInteractions,
            "likes" => $totalLikes,
            "comments" => $commentsCount,
            "views" => $totalViewCount,
        ];
        $body = [];


        foreach ($creators as $creator) {
            $creatorPosts = $mediaHandler->getByX(
                array_merge($contentParam, ["type" => ["post", "story"], "lookup_id" => $creator["id"]]),
                ["play_count", "view_count", "type", "comments_count", "like_count"]
            );
            $creatorPostCount = count($creatorPosts);


            $creatorTotalLikes = (int)array_reduce($creatorPosts, function ($initial, $item) {
                return (!isset($initial) ? 0 : $initial) + (int)$item["like_count"];
            });
            $creatorCommentsCount = (int)array_reduce($creatorPosts, function ($initial, $item) {
                return (!isset($initial) ? 0 : $initial) + (int)$item["comments_count"];
            });
            $creatorViewCount = (int)array_reduce($creatorPosts, function ($initial, $item) {
                return (!isset($initial) ? 0 : $initial) + (!in_array($item["type"], ["REELS", "STORY"]) ? 0 :
                        ((int)$item["play_count"] > 0 ? (int)$item["play_count"] : (int)$item["view_count"]));
            });
            $creatorTotalInteractions = $creatorTotalLikes + $creatorCommentsCount;

            $body[] = [
                $creator["username"],
                min(round($creatorPostCount / (int)$campaign["ppc"] * 100,2), 100),
                $creator["followers_count"],
                $creatorPostCount,
                1,
                $creatorTotalInteractions,
                $creatorTotalLikes,
                $creatorCommentsCount,
                $creatorViewCount,
            ];
        }

        $keys = array_keys($totals);
        $body = array_merge([array_values($totals)], $body);
        $this->crud->csvCreator($dir . $filename, $body, $keys);

        return ["status" => "success", "message" => "Csv Export Created", "data" => ["link" => HOST . $dir . $filename]];
    }





    public function ownerIntegration(array $campaign): array {
        if(!array_key_exists("owned_by", $campaign) || empty($campaign["owned_by"])) return [];
        $ownerId = $campaign["owned_by"];
        $user = $this->crud->user()->disableDepthCheck()->get($ownerId);
        if(empty($user)) return [];
        return $this->crud->integrations()->disableDepthCheck()->getMyIntegration($ownerId);
    }











}