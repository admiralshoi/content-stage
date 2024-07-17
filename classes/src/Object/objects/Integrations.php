<?php

namespace classes\src\Object\objects;
use classes\src\AbstractCrudObject;
use classes\src\Auth\Auth;
use classes\src\Enum\AppSettings;
use classes\src\Media\Medias;
use classes\src\Object\CronWorker;
use classes\src\Object\transformer\URL;

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

class Integrations {

    private int $requestingUsersAccessLevel = 0;
    private int $requestingUsersId = 0;
    private bool $disabledDepthCheck = false;
    private AbstractCrudObject $crud;


    function __construct(AbstractCrudObject $crud) {
        $this->crud = $crud;

        if(isset($_SESSION["access_level"])) $this->requestingUsersAccessLevel = $_SESSION["access_level"];
        if(isset($_SESSION["uid"])) $this->requestingUsersId = $_SESSION["uid"];
    }


    private function access(int $actionType): bool {
        if($this->disabledDepthCheck) return true;
        return $this->crud->hasAccess("node","integration",$actionType, $this->requestingUsersAccessLevel);
    }

    public function disableDepthCheck(): static { $this->disabledDepthCheck = true; return $this; }
    public function enableDepthCheck(): static { $this->disabledDepthCheck = false; return $this; }

    /*
     * Core CRUD features START
     */

    public function getByX(array $params = array(), array $fields = array(), string $customSql = ""): array {
        if(!$this->access(READ_ACTION)) return array();
        return $this->crud->retrieve("integration",$params, $fields,$customSql);
    }
    public function getByIgId(string|int $idId, array $fields = array()): array {
        $integrations = $this->getByX(array("item_id" => $idId, "provider" => "instagram"), $fields);
        return array_key_exists(0, $integrations) ? $integrations[0] : $integrations;
    }
    public function getByUid(string|int $uid, string $provider, array $fields = array()): array {
        $integrations = $this->getByX(array("user_id" => $uid, "provider" => $provider), $fields);
        return array_key_exists(0, $integrations) ? $integrations[0] : $integrations;
    }
    public function getItemNameByUid(string|int $uid, string $provider = "instagram"): string {
        $integrations = $this->getByX(array("user_id" => $uid, "provider" => $provider), ["item_name"]);
        $integration = array_key_exists(0, $integrations) ? $integrations[0] : $integrations;
        return array_key_exists("item_name", $integration) ? $integration["item_name"] : "";
    }
    public function getByItemId(string|int $idId, array $fields = array()): array {
        $integrations = $this->getByX(array("item_id" => $idId), $fields);
        return array_key_exists(0, $integrations) ? $integrations[0] : $integrations;
    }

    public function getRelatedIntegration(string|int $itemId, array $fields = array()): array {
        $integrations = $this->getByX(array("relation_id" => $itemId), $fields);
        return array_key_exists(0, $integrations) ? $integrations[0] : $integrations;
    }

    public function get(string|int $integrationId, array $fields = array()): array {
        $integrations = $this->getByX(array("id" => $integrationId), $fields);
        return array_key_exists(0, $integrations) ? $integrations[0] : $integrations;
    }
    private function create(array $params): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        $params["updated_at"] = time();
        $params["created_at"] = time();
        return $this->crud->create("integration", array_keys($params), $params);
    }
    public function update(array $params, array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        return $this->crud->update("integration", array_keys($params), $params, $identifier);
    }
    private function delete(array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        return $this->crud->delete("integration", $identifier);
    }

    public function getMyIntegration(string|int $uid = 0, array $fields = array(), string $provider = "instagram"): array {
        if($uid === 0) $uid =$this->requestingUsersId;
        $integrations = $this->getByX(array("user_id" => $uid, "provider" => $provider, "active" => 1), $fields);
        return array_key_exists(0, $integrations) ? $integrations[0] : $integrations;
    }

    public function getByUsername(string|int $username, array $fields = array()): array {
        $integrations = $this->getByX(array("item_name" => $username, "provider" => "instagram"), $fields);
        return array_key_exists(0, $integrations) ? $integrations[0] : $integrations;
    }
    /*
     * Core CRUD features END
     */



    /*
     * Integration START
     */

    public function integrate(array $requestData = array(), ?Auth $auth = null, ?string $authType = "facebook"): array {
        if(!$this->access(MODIFY_ACTION)) return array("status" => false, "message" => "Insufficient permissions");
        if(is_null($auth)) $auth = $this->crud->auth();
        if(!$auth->isInit($authType) && is_null($authType)) return array("status" => false, "message" => "Authentication not initiated");
        if(!$auth->init($authType)) return array("status" => false, "message" => "Failed to initiate authentication");

        /* Access token START */
        $code = $auth->getAccessCode($requestData);
        if(empty($code)) return array("status" => null, "message" => "No code found");

        $longLivedToken = $auth->exchangeToLongLivedToken($code);
        file_put_contents(TESTLOGS . "specialLogs/token.json", json_encode($longLivedToken, JSON_PRETTY_PRINT));


        if(empty($longLivedToken)) return array("status" => false, "message" => "Failed to exchange code to a token");
        if(array_key_exists("error", $longLivedToken)) return array("status" => false, "message" => $longLivedToken["error"]["message"]);

        $accessToken = $longLivedToken["access_token"];

        /* Access token END */
        /* Pages and instagram accounts START */

        $media = new Medias();

        if(!$media->isInit($authType) && is_null($authType)) return array("status" => false, "message" => "Authentication not initiated");
        if(!$media->init($authType)) return array("status" => false, "message" => "Failed to initiate authentication");

        /* Pages and instagram accounts END */
        $accounts = $this->getAccounts($media, $authType, $accessToken);
        if(!$accounts["status"]) return $accounts;
        if(empty($accounts["data"])) return array("status" => false, "message" => "No data found");

        $collection = [];
        $isCreator = $this->crud->isCreator();
        foreach ($accounts["data"] as $account) {
            $collection[] = [
                'user_id' => $this->requestingUsersId,
                'provider' => $account["provider"],
                'item_id' => $account["item_id"],
                'item_name' => $account["item_name"],
                'item_token' => $account["item_token"],
                'token_extra' => array_key_exists("token_extra", $account) ? $account["token_extra"] : null,
                "relation_id" => $account["relation_id"],
                "is_creator" => (int)$isCreator
            ];
        }
        file_put_contents(TESTLOGS . "iscrea1111.json", json_encode([
            $isCreator,
            $_SESSION,
            $collection
        ], JSON_PRETTY_PRINT));

        return [
            "count" => count($collection),
            "data" => $collection,
            "status" => "success"
        ];
    }


    public function storeSelectedIntegrations(array $args): array {
        if(!$this->access(MODIFY_ACTION)) return ["status" => "error", "error" => ["message" => "Unauthorized"]];
        if(!array_key_exists("data", $args)) $data = $args;
        else $data = $args["data"];
        foreach (["raw", "selection"] as $key) if(!array_key_exists($key, $data)) return ["status" => "error", "error" => ["message" => "Missing key: $key"]];

        $raw = $this->crud->nestedArray($data, ["raw", "data"]);
        $selection = $data["selection"];
        if(empty($raw) || !is_array($raw)) return ["status" => "error", "error" => ["message" => "Invalid Raw data"]];
        if(empty($selection) || !is_array($selection)) return ["status" => "error", "error" => ["message" => "Invalid selection data"]];

        file_put_contents(TESTLOGS . "iscrea.json", json_encode($args, JSON_PRETTY_PRINT));
        $collection = [];
        foreach ($selection as $provider => $itemId) {
            $relatedRaw = array_values(array_filter($raw, function ($item) use ($provider, $itemId) {
                return $item["provider"] === $provider && $item["item_id"] == $itemId;
            }));
            if(empty($relatedRaw)) return ["status" => "error", "error" => ["message" => "Selection had no related raw integration", "raw" => $raw, "selection" => [$provider, $itemId]]];
            $collection[] = $relatedRaw[0];
        }

        $successes = 0;
        $itemCount = count($collection);
        foreach ($collection as $item) {
            $provider = $item["provider"];
            $media = new Medias();
            $media->init($provider);
            if($provider === "instagram") $media->subscribeWebhook($item["token_extra"]); //Subscribing with page token. Should be done if provider is instagram.
            $item["active"] = 1;

            if(empty($this->getByItemId($item["item_id"]))) $creation = $this->create($item);
            else $creation = $this->update($item, array("item_id" => $item["item_id"], "uid" => $this->requestingUsersId));

            if($creation) $successes++;
        }
        if($successes === 0) return ["status" => "error", "error" => ["message" => "Failed to store any of the selected integrations. Try again later."]];

        if($this->crud->isCreator()) {
            $requestHandler = $this->crud->cronRequestHandler();
            $integrations = $requestHandler->findCreatorsToCompleteIntegration(null, ["user_id" => $this->requestingUsersId]);
            $this->crud->multiArrayLog($integrations, "integrations");
            if(!empty($integrations)) {
                $requestHandler->finishCreatorIntegration($integrations);

                $creatorItems = $requestHandler->findCreatorsToQueryAccountAnalytics(null, ["user_id" => $this->requestingUsersId]);
                $this->crud->multiArrayLog($creatorItems, "creator-items");
                if(!empty($creatorItems)) $requestHandler->queryAccountAnalytics($creatorItems);
            }
        }

        return ["status" => "success", "message" => "$successes / $itemCount integrations have been stored."];
    }


    public function getAccounts(Medias $media, ?string $authType, string $accessToken, array $pageAccounts = array()): array {
        if(!$media->isInit($authType) && is_null($authType)) return array("status" => false, "message" => "Authentication not initiated");
        if(!$media->init($authType)) return array("status" => false, "message" => "Failed to initiate authentication");


        /* Pages and instagram accounts START */
        $accounts = $media->getAccounts($authType === "instagram" && !empty($pageAccounts) ? $pageAccounts : $accessToken);
        file_put_contents(TESTLOGS . "specialLogs/$authType.json", json_encode($accounts, JSON_PRETTY_PRINT));
        if(empty($accounts)) return array("status" => false, "message" => "Failed to find any $authType pages");

        //If we get pages, we will attempt to fetch instagram accounts too
        $collector = [];
        if($authType === "facebook") {
            foreach ($accounts as $account) {
                $igAccount = $this->getAccounts($media, "instagram", $accessToken, $account);

                if(!$igAccount["status"] || empty($igAccount["data"])) continue;

                $collector[] = array_merge(["provider" => $authType, "relation_id" => $igAccount["data"]["item_id"]], $account);
                $collector[] = array_merge(["provider" => "instagram", "relation_id" => $account["item_id"], "token_extra" => $account['item_token']], $igAccount["data"]);

            }
        }
        else $collector = $accounts;

        /* Pages and instagram accounts END */

        return array("status" => true, "data" => $collector);
    }

    public function getRandomIntegration(): array {
        $integrations = $this->getByX();
        if(empty($integrations)) return $integrations;

        $key = rand(0, (count($integrations) -1));
        return $integrations[$key];
    }
    public function extractIgToken($integrationObj): string { return array_key_exists("user_token", $integrationObj) ? $integrationObj["user_token"] : ""; }
    public function extractIgId($integrationObj): string|int { return array_key_exists("ig_id", $integrationObj) ? $integrationObj["ig_id"] : ""; }




    public function getAccountsToBeQueried(): array {
        $rows = $this->getByX(["is_creator" => 1]);
        if(empty($rows)) return [];

        $queryInterval = $this->crud->appMeta()->get("analytics_interval");
        $timeCap = strtotime("-$queryInterval hour");

        return array_values(array_filter($rows, function ($row) use ($timeCap) {
            return (int)$row["last_queried"] <= $timeCap;
        }));
    }





    public function toggleDefaultIntegration(array $args): array {
        if(!array_key_exists("id", $args)) return ["status" => "error", "error" => ["message" => "Missing key: id"]];
        $id = $args["id"];

        $row = $this->get($id);
        if(empty($row)) return ["status" => "error", "error" => ["message" => "Invalid id"]];

        $uid = $row["user_id"];
        if(!$this->crud->isAdmin() && (int)$uid !== (int)$_SESSION["uid"]) return ["status" => "error", "error" => ["message" => "Invalid id, or missing permissions"]];

        $setDefault = !(int)$row["active"];

        $provider = $row["provider"];
        $params = ["provider" => $provider, "user_id" => $uid, "active" => 1];
        $rows = $this->getByX($params);

        if(count($rows) > 0) {
            foreach ($rows as $item) $this->update(["active" => 0], ["id" => $item["id"]]);
        }
        if($setDefault) $this->update(["active" => 1], ["id" => $id]);
        return ["status" => "success", "message" => "Successfully " . ($setDefault ? "enabled" : "disabled") . " the integration"];
    }


    public function removeIntegration(array $args): array {
        if(!array_key_exists("id", $args)) return ["status" => "error", "error" => ["message" => "Missing key: id"]];
        $id = $args["id"];

        $row = $this->get($id);
        if(empty($row)) return ["status" => "error", "error" => ["message" => "Invalid id"]];

        $uid = $row["user_id"];
        if(!($this->crud->isAdmin() || (int)$uid === (int)$_SESSION["uid"])) return ["status" => "error", "error" => ["message" => "Invalid id, or missing permissions"]];

        if($row["provider"] === "instagram") {
            $username = $row["item_name"];
            $lookupHandler = $this->crud->lookupList();
            $lookupRow = $lookupHandler->getByUsername($username);
            if(!empty($lookupRow)) {
                $lookupId = $lookupRow["id"];
                $this->crud->mediaLookup()->delete(["lookup_id" => $lookupId]);
                $this->crud->accountAnalytics()->delete(["lookup_id" => $lookupId]);
                $lookupHandler->delete(["id" => $lookupId]);
            }
        }


        $this->delete(["id" => $id]);
        $integrations = $this->getMyIntegration($uid);
        if(empty($integrations)) $this->crud->user()->update(["registration_complete" => 0], ["uid" => $uid]);
        return ["status" => "success", "message" => "Successfully removed the integration"];
    }




    public function mediaDiscovery(?CronWorker $worker = null): void {
        $rows = $this->getByX(["is_creator" => 1, "provider" => "instagram"]);
        if(empty($rows)) return;

        $timeCap = strtotime("-1 hour");

        $rows = array_values(array_filter($rows, function ($row) use ($timeCap) {
            return (int)$row["last_discovery"] <= $timeCap;
        }));
        $worker?->log("Found " . count($rows) . " integrations to discover from");
        if(empty($rows)) return;

        $mediaHandler = $this->crud->mediaLookup();
        $lookupHandler = $this->crud->lookupList();
        $dataHandler = $this->crud->dataHandler();
        $api = new Medias();
        if(!$api->init("instagram")) return;


        foreach ($rows as $row) {
            $lookupData = $lookupHandler->getByUsername($row["item_name"], 0, ["id", "followers_count"]);
            $rowId = $lookupData["id"];
            $accountId = $row["item_id"];
            $accessToken = $row["item_token"];

            $latestMediaId = $mediaHandler->getLatestAccountMediaId($rowId);
            $newMedias = $api->mediaDiscovery($accountId, $accessToken, $latestMediaId);
            if(empty($newMedias)) continue;


            foreach ($newMedias as $media) {
                if(empty($media) || !is_array($media)) {
                    $worker?->log("Media was empty, " . $media);
                    continue;
                }


                //------------------------------------------------------------------------------------------------------------------------------------
//                if($media["media_product_type"] !== "REELS") {
//                    $worker?->log("Media is not a REEL, but " . $media["media_product_type"]);
//                    continue;
//                }



                $media["lookup_id"] = $rowId;
                $media["username"] = $row["ig_name"];
                $media["mid"] = $media["id"];
                $media["media_type"] = $media["media_product_type"];
                $media["display_url"] = array_key_exists("thumbnail_url", $media) ? $media["thumbnail_url"] : $media["media_url"];
                if(array_key_exists("thumbnail_url", $media)) $media["video_url"] = $media["media_url"];
                $media["type"] = "post";
                $media["origin"] = "api_pull";
                unset($media["id"]);
                unset($media["media_product_type"]);
                unset($media["media_url"]);
                if(array_key_exists("thumbnail_url", $media)) unset($media["thumbnail_url"]);


                if(!empty($mediaHandler->getByX(["mid" => $media["mid"]]))) {
                    $worker?->log("Stored media: " . $media["mid"]);
                    continue;
                }

                $mediaInsight = $api->orderMediaInsights($this->crud->nestedArray($media, ["insights", "data"], []));
                $data = array_merge($media, $mediaInsight);
                if(array_key_exists("insights", $data)) unset($data["insights"]);
                $data["engagement_rate"] = $dataHandler->engagementRate(array_merge($data, ["followers_count" => $lookupData["followers_count"]]));

                $directory = "images/content/posts/";
                $data["display_url"] = $directory . $this->crud->downloadMedia($data["display_url"], ROOT . $directory);
                if(array_key_exists("video_url", $data))
                    $data["video_url"] = $directory . $this->crud->downloadMedia($data["video_url"], ROOT . $directory);


                $mediaHandler->create($data);
                $worker?->log("Stored media: " . $media["mid"]);
            }

            $this->update(["last_discovery" => time()], ["id" => $row["id"]]);
        }
    }















}