<?php

namespace classes\src\Object\objects;
use classes\src\AbstractCrudObject;
use classes\src\Enum\HandlerErrors;
use classes\src\Object\Scraper;
use classes\src\Enum\ExternalItems;

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

class LookupList {
    private AbstractCrudObject $crud;
    private int $requestingUsersAccessLevel = 0;
    private int $requestingUsersId = 0;
    private bool $disabledDepthCheck = false;
    protected array $meta = [];
    public bool $isError = true;
    private array $responseError = [];
    private array $responseSuccess = [];
    private bool $relationsCheck = true;


    function __construct(AbstractCrudObject $crud) {
        $this->crud = $crud;
        if(isset($_SESSION["access_level"])) $this->requestingUsersAccessLevel = $_SESSION["access_level"];
        if(isset($_SESSION["uid"])) $this->requestingUsersId = $_SESSION["uid"];
        $this->setDefaultResponse();
    }

    private function setDefaultResponse():  void {
        $this->isError = true;
        $this->responseError = array(
            "status" => "error",
            "error" => array(
                "message" => "",
                "code" => 101
            )
        );
        $this->responseSuccess = array(
            "status" => "success",
            "data" => array()
        );
    }



    public function getResponse(): array {
        return $this->isError ? $this->responseError : $this->responseSuccess;
    }
    public function clearResponse(): array {
        return $this->isError ? $this->responseError : $this->responseSuccess;
    }



    private function access(int $actionType): bool {
        if($this->disabledDepthCheck) return true;
        return $this->crud->hasAccess("node","lookup_list",$actionType, $this->requestingUsersAccessLevel);
    }

    public function disableDepthCheck(): static {
        $this->disabledDepthCheck = true;
        $this->relationsCheck = false;
        return $this;
    }
    public function enableDepthCheck(): static {
        $this->disabledDepthCheck = false;
        $this->relationsCheck = true;
        return $this;
    }

    public function disableRelationCheck(): static { $this->relationsCheck = false; return $this; }
    public function enableRelationCheck(): static { $this->relationsCheck = true; return $this; }
    /*
     * Core CRUD features START
     */

    public function getByX(array $params = array(), array $fields = array(), string $customSql = ""): array {
        if(!$this->access(READ_ACTION)) return array();
        if($this->relationsCheck && !$this->crud->isAdmin()) {
            if($this->crud->isBrand(0, false)) {
                file_put_contents(TESTLOGS . "abcdrand.json", json_encode("hihh", JSON_PRETTY_PRINT));
                $relations = $this->crud->creatorRelations()->getByUserId(0, ["lookup_id"]);
                if(empty($relations)) return [];
                $ids = array_map(function ($row) { return $row["lookup_id"]; }, $relations);
                if(!array_key_exists("id", $params)) $params["id"] = $ids;
                else {
                    if(!is_array($params["id"])) $params["id"] = [$params["id"]];
                    $params["id"] = array_values(array_filter($params["id"], function ($id) use ($ids)  { return in_array($id, $ids); }));
                    if(empty($params["id"])) return [];
                }
            }
            elseif($this->crud->isCreator()) {
                $username = $this->crud->nestedArray($this->crud->integrations()->getMyIntegration(), ["item_name"]);
                if(empty($username)) return [];
                $params["username"] = $username;
            }
            elseif($this->crud->isGuest()) {

            }
            else return [];
        }

        return $this->crud->retrieve("lookup_list",$params, $fields,$customSql);
    }
    public function create(array $params): bool {
        file_put_contents(ROOT . "testLogs/beforereating.json", json_encode($params, JSON_PRETTY_PRINT));
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        if(!array_key_exists("created_at", $params)) $params["created_at"] = time();
        return $this->crud->create("lookup_list", array_keys($params), $params);
    }
    public function update(array $params, array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        return $this->crud->update("lookup_list", array_keys($params), $params, $identifier);
    }
    public function delete(array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        return $this->crud->delete("lookup_list", $identifier);
    }

    public function get(string|int $id, array $fields = array()): array {
        $item = $this->getByX(array("id" => $id), $fields);
        return array_key_exists(0, $item) ? $item[0] : $item;
    }

    /*
     * Core CRUD features END
     */


    public function getByUsername(string|int $username, int $deactivated = 0, array $fields = array()): array {
        $item = $this->getByX(array("username" => $username, "deactivated" => $deactivated), $fields);
        return array_key_exists(0, $item) ? $item[0] : $item;
    }

    public function creatorId(string $username): string|int {
        $field = "id";
        $row = $this->getByUsername($username, 0, array($field));
        return !array_key_exists($field, $row) ? 0 : (int)$row[$field];
    }

    public function getUserCreatorId(string|int $uid): string|int {
        $myCreatorUsername = $this->crud->integrations()->getItemNameByUid($uid);
        return empty($myCreatorUsername) ? 0 : $this->creatorId($myCreatorUsername);
    }

    public function getWithMergedData(string|int $id, string|int $campaignId = 0): array {
        return $this->mergeUserAndChanges($this->get($id), true, false, $campaignId);
    }
    public function getByXWithMergedData(array $params = array(), array $fields = array(), string $customSql = ""): array {
        $data = $this->getByX($params, $fields, $customSql);
        return empty($data) ? $data : array_map(function ($row) { return $this->mergeUserAndChanges($row, true); }, $data);
    }

    public function toggleCreatorTracking(array $args): void {
        if(!array_key_exists("creator_id", $args)) return;
        $creator = $this->get($args["creator_id"]);
        if(empty($creator)) return;

        $deactivated = (int)(!((int)$creator["deactivated"]));
        $this->update(["deactivated" => $deactivated], ["id" => $creator["id"]]);
    }

    public function belongsToApi(string|int $lookupId = 0, string $username = ""): bool {
        if(empty($username)) {
            $row = $this->get($lookupId);
            if(empty($row)) return false;
            $username = $row["username"];
        }
        return !empty($this->crud->integrations()->getByUsername($username, ["id"]));
    }

    public function getRelatedIntegration(string|int $lookupId): array {
        $row = $this->get($lookupId);
        if(empty($row)) return [];
        return $this->crud->integrations()->getByUsername($row["username"]);
    }



    public function getUser(string $username, array $args, ?Scraper &$scraper = null): static {
        if(empty($username) && array_key_exists("username", $args)) $username = trim($args["username"]);
        if(str_contains($username, "@")) $username = str_replace("@", "", $username);

//        $this->isError = false;
//        $this->responseSuccess["data"] = json_decode(file_get_contents(TESTLOGS . "user-last.json"), true);
//        return $this;


        if(!empty($username)) $this->lookupUser($username, $scraper);

        $this->responseError["error"]["message"] = "Failed to find user. Try again later";
        $this->responseError["error"]["code"] = 108;
        return $this;
    }

    public function mergeUserAndChanges(array $user = array(), bool $returnResponse = false, bool $dbCompatible = false, string|int $campaignId = 0): ?array {
        if(empty($user)) {
            $this->responseError["error"]["message"] = "Failed to find user.";
            $this->responseError["error"]["code"] = 109;
            return [];
        }

        if(!$dbCompatible) {
            $mediaParam = ["lookup_id" => $user["id"]];
            if(!empty($campaignId)) $mediaParam["campaign_id"] = $campaignId;

            $dataHandler = $this->crud->dataHandler();
            $user["media"] = $this->crud->mediaLookup()->getByX($mediaParam);
            $user = $dataHandler->fromMediaSetUserAverages($user);
            $user["engagement_rate"] = $dataHandler->engagementRate($user);
            $user["latest_posts"] = $dataHandler->sortAndTrim($user["media"], "timestamp", false, 3);
            $user["top_liked_posts"] = $dataHandler->sortAndTrim($user["media"], "like_count", false, 3);
            $user["top_commented_posts"] = $dataHandler->sortAndTrim($user["media"], "comments_count", false, 3);
        }

        file_put_contents(TESTLOGS . "userfindtestMerge.json", json_encode($user, JSON_PRETTY_PRINT));

        if($returnResponse) return $user;
        $this->isError = false;
        $this->responseSuccess["data"] = $user;
        return [];
    }


    private function bulkUserCreation(array $usernames, ?Scraper &$scraper = null): void {
        $usernameSuccess = $usernameError = [];
        foreach ($usernames as $username) {
            if(str_contains($username, "@")) $username = str_replace("@", "", $username);
            $username = trim($username);
            $this->lookupUser($username, $scraper);
            if($this->isError) {
                $usernameError[] = $username;
                continue;
            }

            $data = $this->getResponse();
            $this->setDefaultResponse();
            $this->storeNewCreator($data);

            if($this->isError) {
                $usernameError[] = $username;
                continue;
            }
            $usernameSuccess[] = $username;
        }

        if(empty($usernameSuccess)) return;

        $this->isError = false;
        $this->responseSuccess["data"] = [
            "bulk" => true,
            "message" => "Stored " . count($usernameSuccess) . " out of " . count($usernames) . " usernames",
            "successes" => $usernameSuccess,
            "errors" => $usernameError,
        ];
    }



    public function lookupUser(string $username, ?Scraper &$scraper = null): void {
        if(is_null($scraper)) {
            $scraper = $this->crud->scraper();
            $scraper->setCookieManager($this->crud)->cookieSetDefault()->cookieAddToHeader();
        }

        if(str_contains($username, ",")) {
            $this->bulkUserCreation(explode(",", $username), $scraper);
            return;
        }

        $userData = $this->getByUsername($username);
        file_put_contents(TESTLOGS . "userfindtest.json", json_encode($userData, JSON_PRETTY_PRINT));
        if(empty($userData)) {
            $handler = $this->crud->handler();
            $userData = $handler->instagramLookupUser($username, $scraper);
            file_put_contents(ROOT . "testLogs/lookedup.json", json_encode($userData, JSON_PRETTY_PRINT));

            if(empty($userData)) {
                $this->responseError["error"]["message"] = "Failed to fetch user. Try again later";
                $this->responseError["error"]["code"] = 106;
                return;
            }
            if(array_key_exists("status", $userData) && $userData["status"] === "error") {
                $this->responseError["error"]["message"] = $userData["error"];
                return;
            }

            $userData["media"] = $this->crud->mediaLookup()->processMedia(
                $handler->getRemainingMedia(
                    $userData["media"],
                    $userData["followers_count"],
                    $userData["media_count"],
                    $userData["media_cursor"],
                    $userData["ig_id"],
                    0,
                    false,
                    $scraper
                )
            );

            if(array_key_exists("media_cursor", $userData)) unset($userData["media_cursor"]);
            if(array_key_exists("engagement_rate", $userData)) unset($userData["engagement_rate"]);
            if(array_key_exists("reels_media", $userData)) unset($userData["reels_media"]);

            file_put_contents(ROOT . "testLogs/user-last.json", json_encode($userData, JSON_PRETTY_PRINT));
        }

        $this->mergeUserAndChanges($userData, false, true);
    }




    public function storeNewCreator(array $args): static {
        $this->relationsCheck = false;
        if(!array_key_exists("data", $args) || empty($args["data"])) {
            $this->responseError["error"]["message"] = HandlerErrors::EMPTY_INPUT;
            $this->responseError["error"]["code"] = 38932;
            $this->relationsCheck = true;
            return $this;
        }
        file_put_contents(TESTLOGS . "storeNewCreator.json", json_encode($args, JSON_PRETTY_PRINT));


        $this->setMeta([
            "data_level" => 1,
            "init_type" => ExternalItems::INITIALIZED_DIRECT,
            "init_by" => $this->requestingUsersId,
            "init_origin" => ExternalItems::ORIGIN_INTERNAL,
        ]);

        $this->setUserAndMedia($args["data"]);
        $this->isError = false;
        $this->relationsCheck = true;
        return $this;
    }




    public function setMeta(array $meta): void { $this->meta = empty($this->meta) ? $meta : $this->meta; }
    private function trimLookupData(array &$data): array {
        $mediaData = array_key_exists("media", $data) ? $data["media"] : [];
        if(array_key_exists("changes", $data)) unset($data["changes"]);
        if(array_key_exists("media", $data)) unset($data["media"]);
        if(array_key_exists("media_cursor", $data)) unset($data["media_cursor"]);
        if(array_key_exists("latest_posts", $data)) unset($data["latest_posts"]);
        if(array_key_exists("top_liked_posts", $data)) unset($data["top_liked_posts"]);
        if(array_key_exists("top_commented_posts", $data)) unset($data["top_commented_posts"]);
        if(array_key_exists("engagement_rate", $data)) unset($data["engagement_rate"]);
        if(array_key_exists("reels_media", $data)) unset($data["reels_media"]);
        if(array_key_exists("insights", $data)) unset($data["insights"]);
        return $mediaData;
    }

    private function addMetaData(array &$data, array $meta = []): void {
        if(!empty($meta)) foreach ($meta as $key => $value) $data[$key] = $value;
        $data["version"] = md5(rand() . time());
    }

    public function serializeItems(array $userData, bool $serialize = true): array {
        $keys = array("media", "top_mentions", "top_hashtags", "latest_posts", "top_liked_posts", "top_commented_posts");
        foreach ($keys as $key) {
            if(array_key_exists($key, $userData) && $serialize && is_array($userData[$key])) $userData[$key] = base64_encode(json_encode($userData[$key]));
            if(array_key_exists($key, $userData) && !$serialize && !is_array($userData[$key])) $userData[$key] = json_decode(base64_decode($userData[$key]), true);

        }
        return $userData;
    }

    private function updateTopMentionsAndTags(array &$userData, bool $updateOnlyIfEmpty = true): void {
        if(empty($userData)) return;
        $username = $userData["username"];

        $params = [];
        if(array_key_exists("top_mentions", $userData) && (!$updateOnlyIfEmpty || !empty($userData["top_mentions"]))) $params["top_mentions"] = $userData["top_mentions"];
        if(array_key_exists("top_hashtags", $userData) && (!$updateOnlyIfEmpty || !empty($userData["top_hashtags"]))) $params["top_hashtags"] = $userData["top_hashtags"];
        if(array_key_exists("top_mentions", $userData)) unset($userData["top_mentions"]);
        if(array_key_exists("top_hashtags", $userData)) unset($userData["top_hashtags"]);

        if(!empty($params)) $this->update($params, array("username" => $username));
    }




    public function setUserAndMedia(array $user): void {
        $mediaData = $this->trimLookupData($user);
        $row = $this->getByUsername($user["username"]);

        if(!empty($row)) {
            if(array_key_exists("data_level", $this->meta) && (int)$row["data_level"] !== (int)$this->meta["data_level"])
                $rowMeta = $this->meta;
            else $rowMeta = [];
        }
        else $rowMeta = $this->meta;
        $this->addMetaData($user, $rowMeta);

        if(empty($row)) {
            $this->create($this->serializeItems($user));
            usleep(1000);
            $row = $this->getByUsername($user["username"]);
        }
        else {
            $this->updateTopMentionsAndTags($user);
            $this->update($user, array("id" => $row["id"]));
        }

        $lookupId = $row["id"];
        $creatorRelations = $this->crud->creatorRelations();
        if(!$this->crud->isAdmin() && !$creatorRelations->exists($lookupId)) {
            $creatorRelations->create([
                "user_id" => $this->requestingUsersId,
                "lookup_id" => $lookupId
            ]);
        }

        $mediaData = $this->crud->mediaLookup()->processMedia($mediaData);
        if(!empty($mediaData)) $this->crud->mediaLookup()->insertNewMedia($mediaData, $lookupId);
    }

















    public function userStories(string $username, string|int $igId = 0, ?Scraper &$scraper = null): void {
        if(is_null($scraper)) {
            $scraper = $this->crud->scraper();
            $scraper->setCookieManager($this->crud)->cookieSetDefault()->cookieAddToHeader();
        }

        $handler = $this->crud->handler();
        $storyData = $handler->userStorySearch($username, $igId, $scraper);

        if(empty($storyData)) {
            $this->responseError["error"]["message"] = "Failed to fetch story data. Try again later";
            $this->responseError["error"]["code"] = 106;
            return;
        }
        if(array_key_exists("status", $storyData) && $storyData["status"] === "error") {
            $this->responseError["error"]["message"] = $storyData["error"];
            return;
        }


        $this->isError = false;
        $this->responseSuccess["data"] = $storyData;
    }



    public function exploreHashtag(string $hashtag, ?Scraper &$scraper = null): void {
        if(is_null($scraper)) {
            $scraper = $this->crud->scraper();
            $scraper->setCookieManager($this->crud)->cookieSetDefault()->cookieAddToHeader();
        }

        $handler = $this->crud->handler();
        $tagData = $handler->hashtagExplore($hashtag, $scraper);

        if(empty($tagData)) {
            $this->responseError["error"]["message"] = "Failed to fetch hashtag. Try again later";
            $this->responseError["error"]["code"] = 106;
            return;
        }
        if(array_key_exists("status", $tagData) && $tagData["status"] === "error") {
            $this->responseError["error"]["message"] = $tagData["error"];
            return;
        }


        $this->isError = false;
        $this->responseSuccess["data"] = $tagData;
    }







}