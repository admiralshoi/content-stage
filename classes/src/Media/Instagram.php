<?php

namespace classes\src\Media;

use classes\src\AbstractCrudObject;
use classes\src\Enum\AppSettings;
use classes\src\Enum\ApiUsage;
use classes\src\Media\Utilities\Util;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class Instagram extends AbstractMedias {
    function __construct() { parent::__construct();  }



    /**
     * Pass in a single page-object as it was returned by Facebook -> getFbPages
     * @param array|string $page
     * @return array
     */
    public function getAccounts(array|string $page): array {
        if(!is_array($page)) return array("error" => "Page must be of type: Array");
        foreach (array("token_extra", "item_id") as $field)
            if(!array_key_exists($field, $page) || empty($page[$field]))
                return array("error" => "Given fields are not sufficient for this request. Expected $field");

        $baseUrl = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . AppSettings::AUTH_GRAPH_ENDPOINTS["ig_accounts"];
        $query = array(
            "fields" => "instagram_business_account{id,username,profile_picture_url}", //You can even download this profile picture if you want
            "limit" => 50,
            "access_token" => $page["token_extra"],
            "after" => ""
        );

        $startTime = time();
        $baseUrl = str_replace("__PAGE_ID__",$page["item_id"],$baseUrl);

        $this->httpHandler->send($baseUrl . "?" . http_build_query($query));
        $graphResponse = self::$util::ensureValidObjectResponse($this->httpHandler->getResponse());

        if(empty($graphResponse)) return array("error" => "Received a bad response from the graph");
        $data = self::$util::nestedArray($graphResponse,array("instagram_business_account"));

        return empty($data) ? [] : [
            "item_id" => $data["id"],
            "item_name" => $data["username"],
            "created_by" => "some user unique id?",
            "created_at" => $startTime,
            "item_token" => $page["token_extra"],
        ];
    }


    /**
     * You can call this by passing the instagram picture Url. By default, it's in: $data['profile_picture_url'], where $data is defined on line 46
     * @param string $imagePath
     * @param string $destinationDirectory
     * @return string
     */
    public function downloadAccountPicture(string $imagePath, string $destinationDirectory): string {
        $accountPicture = $this->filenameInfo($imagePath);
        $pictureFilename = "profilePicture." . $accountPicture["ext"];

        if(!str_ends_with($destinationDirectory, "/")) $destinationDirectory .= "/";
        $pictureFilename = $this->downloadMedia($imagePath,$destinationDirectory,null,$pictureFilename);

        return !$pictureFilename ? "" : $destinationDirectory . $pictureFilename;
    }



    #[ArrayShape(["followers_count" => "string", "biography" => "string", "follows_count" => "string",
        "media_count" => "string", "name" => "string", "profile_picture_url" => "string", "website" => "string", "ig_id" => "string"])]
    private function accountStatMetrics(): array {
        return [
            "followers_count" => "followers_count",
            "biography" => "biography",
            "follows_count" => "follows_count",
            "media_count" => "media_count",
            "name" => "full_name",
            "profile_picture_url" => "profile_picture",
            "website" => "external_url",
            "ig_id" => "ig_id",
            "username" => "username",
        ];
    }



    private function mediaStatMetrics(): array {
        return [
            "comments" => "comments_count",
            "likes" => "like_count",
            "plays" => "play_count",
            "reach" => "reach_count",
            "impressions" => "impressions",
            "saved" => "saves_count",
            "shares" => "shares_count",
            "total_interactions" => "total_interactions",
            "audience_city" => "audience_city",
            "audience_country" => "audience_country",
            "audience_gender_age" => "audience_gender_age",
            "online_followers" => "online_followers",
        ];
    }


    public function accountInsight(string|int $id, string $accessToken): array {
        $this->httpHandler->send(ApiUsage::getUrlProfileMeta($accessToken, $id));
        $profileStatsRaw = $this->httpHandler->getResponse();
        $profileStats = [];

        foreach ($profileStatsRaw as $key => $value)
            if(array_key_exists($key, $this->accountStatMetrics())) $profileStats[($this->accountStatMetrics()[$key])] = $value;

        return $profileStats;
    }


    public function accountReachInsight(string|int $id, string $accessToken): array {
        $this->httpHandler->send(ApiUsage::getUrlAccountReachInsights($accessToken, $id));
        $insights = Util::nestedArray($this->httpHandler->getResponse(), ["data"]);
        file_put_contents(TESTLOGS . "reachins.json", json_encode($insights, JSON_PRETTY_PRINT));

        if(empty($insights)) return [
            "status" => "error", "error" => ["message" => "Failed to get account reach insights", "code" => 83217]
        ];

        return $this->orderAccountReachInsights($insights);
    }

    public function accountDemographicInsight(string|int $id, string $accessToken): array {
        $this->httpHandler->send(ApiUsage::getUrlAccountDemographicInsights($accessToken, $id));
        $insights = Util::nestedArray($this->httpHandler->getResponse(), ["data"]);
        file_put_contents(TESTLOGS . "demoins.json", json_encode($insights, JSON_PRETTY_PRINT));

        if(empty($insights)) return [
            "status" => "error", "error" => ["message" => "Failed to get account reach insights", "code" => 83361]
        ];

        return $this->orderDemographicInsight($insights);
    }



    #[Pure] private function orderInsights(array $insights): array {
        $insightOrdered = [];
        foreach ($insights as $key => $value)
            if(array_key_exists($key, $this->mediaStatMetrics())) $insightOrdered[($this->mediaStatMetrics()[$key])] = $value;

        return $insightOrdered;
    }


    public function orderDemographicInsight(array $data): array {
        if(empty($data)) return [];
        $crud = (new AbstractCrudObject());
        $insights = [];
        foreach ($data as $item) {
            $name = $item["name"];
            $insights[ $name ] = $crud->nestedArray($item, ["values", 0, "value"], $name === "online_followers" ? 0 : null);
            if(is_array($insights[$name])) arsort($insights[$name]);
        }

        return $this->orderInsights($insights);
    }

    public function orderAccountReachInsights(array $data): array {
        if(empty($data)) return [];
        $crud = (new AbstractCrudObject());
        $insights = [];
        foreach ($data as $item) {
            $values = $item["values"];
            if(count($values) > 1 && array_key_exists("end_time", $values[0])) {
                $values = array_map(function ($valueItem) { return array_merge($valueItem, ["timestamp" => strtotime($valueItem["end_time"])]); }, $values);
                $crud->sortByKey($values, "timestamp");
                $insights[ $item["name"] ] = $crud->nestedArray(array_shift($values), ["value"]);
            }
            else $insights[ $item["name"] ] = $crud->nestedArray($values, [0, "value"], 0);
        }

        return $this->orderInsights($insights);
    }

    #[Pure] public function orderMediaInsights(array $data): array {
        if(empty($data)) return [];
        $insights = [];
        foreach ($data as $item) {
            $insights[ $item["name"] ] = $item["values"][0]["value"];
        }

        return $this->orderInsights($insights);
    }

    public function reelsMediaInsight(string|int $id, string $accessToken): array {
        $crud = (new AbstractCrudObject());
        $this->httpHandler->send(ApiUsage::getUrlMediaInsights($accessToken, $id));
        $response = $this->httpHandler->getResponse();
        file_put_contents(TESTLOGS . "okokkkkkkkkkkkkkkkk.json", json_encode([
            $accessToken, $id, ApiUsage::getUrlMediaInsights($accessToken, $id), $response
        ], JSON_PRETTY_PRINT));
        $mediaInsights = $crud->nestedArray($this->httpHandler->getResponse(), ["data"]);

        if(empty($mediaInsights)) return [
            "status" => "error", "error" => ["message" => "Failed to get media insights", "code" => 83227]
        ];

        return $this->orderMediaInsights($mediaInsights);
    }


    public function mediaMetaData(string|int $id, string $accessToken): array {
        $crud = (new AbstractCrudObject());
        $this->httpHandler->send(ApiUsage::getUrlMediaMeta($accessToken, $id));
        $insights = $this->httpHandler->getResponse();

        if(empty($insights) || empty($crud->nestedArray($insights, ["id"]))) return [
            "status" => "error", "error" => ["message" => "Failed to get media meta data", "code" => 83222]
        ];

        return $insights;
    }


    public function mediaDiscovery(string|int $id, string $accessToken, int $mediaCapId = 0): array {
        $crud = (new AbstractCrudObject());
        $crud->multiArrayLog([$id, $accessToken], "stuff");
        $maxMedias = 12;

        $requestUrl = ApiUsage::getBaseUrlMediaDiscovery($accessToken, $id);
        $query = ApiUsage::getQueryMediaDiscovery($accessToken, $id);
        $hasNext = true;

        $medias = [];
        while ($hasNext) {
            $this->httpHandler->send($requestUrl . "?" . http_build_query($query));
            $graphResponse = self::$util::ensureValidObjectResponse($this->httpHandler->getResponse());
            $crud->multiArrayLog($graphResponse, "graphres");

            if(empty($graphResponse)) return array("error" => "Received a bad response from the graph");
            $data = self::$util::nestedArray($graphResponse,array("data"));
            if(empty($data)) break;

            $data = array_map(function ($media) {
                if(array_key_exists("timestamp", $media)) $media["timestamp"] = strtotime($media["timestamp"]);
                else $media["timestamp"] = 0;
                return $media;
            }, $data);
            $crud->sortByKey($data, "timestamp");
            file_put_contents(TESTLOGS . "mediadiscovery.json", json_encode($data, JSON_PRETTY_PRINT));
            foreach ($data as $media) {
                if($mediaCapId !== 0 && (int)$media["id"] === $mediaCapId) break 2;
                if(count($medias) < $maxMedias) {
                    $insights = $this->orderMediaInsights($media["insights"]["data"]);
                    $medias[] = array_merge($media, $insights,["type" => "post"]);
                }
                else break 2;
            }

            $hasNext = $this->resolveHasNextWithAfter($query, $graphResponse);
        }

        file_put_contents(TESTLOGS . "mediadiscovery-2.json", json_encode($medias, JSON_PRETTY_PRINT));
        return $medias;
    }





    public function taggedPage(string|int $id, string $accessToken, int $timeCap = 0): array {
        $crud = (new AbstractCrudObject());
        $maxMedias = 30;

        $requestUrl = ApiUsage::getBaseUrlTagged($accessToken, $id);
        $query = ApiUsage::getQueryTagged($accessToken, $id);
        $hasNext = true;

        $medias = [];
        while ($hasNext) {
            $this->httpHandler->send($requestUrl . "?" . http_build_query($query));
            $graphResponse = self::$util::ensureValidObjectResponse($this->httpHandler->getResponse());

            if(empty($graphResponse)) return array("error" => "Received a bad response from the graph");
            $data = self::$util::nestedArray($graphResponse,array("data"));
            if(empty($data)) break;

            $data = array_map(function ($media) {
                if(array_key_exists("timestamp", $media)) $media["timestamp"] = strtotime($media["timestamp"]);
                else $media["timestamp"] = 0;
                return $media;
            }, $data);
            $crud->sortByKey($data, "timestamp");
            file_put_contents(TESTLOGS . "taggedpageApi.json", json_encode($data, JSON_PRETTY_PRINT));

            foreach ($data as $media) {
                if($timeCap !== 0 && $media["timestamp"] < $timeCap) break 2;
                if(count($medias) >= $maxMedias) break 2;
                $medias[] = array_merge($media, $media, ["type" => "post"]);
            }
            if(count($medias) >= $maxMedias) break;
            $hasNext = $this->resolveHasNextWithAfter($query, $graphResponse);
        }

        file_put_contents(TESTLOGS . "tagged-2.json", json_encode($medias, JSON_PRETTY_PRINT));
        return $medias;
    }











    /**
     * @param string $accessToken USER access-token
     * @param string|int $accountId Instagram account id
     * @param array $metrics List of metric names
     * @param string $period day|week|lifetime|days_28
     * @param array $timeInterval ["since" => "int", "until" => "int"]
     * @return array
     *
     * The since and until parameters are inclusive, so
     * if your range includes a day that hasn't ended (i.e, today), subsequent queries throughout the
     * day may return increased values. If you do not include the since and until parameters, the API will
     * default to a 2 day range: yesterday through today.
     */
    public function getInsights(string $accessToken, string|int $accountId, array $metrics, string $period, array $timeInterval = array() ): array {
        if(!empty($timeInterval) && !(array_key_exists("since", $timeInterval) && array_key_exists("until", $timeInterval)))
            return array("error" => "Time-interval must include keys 'since' and 'until'");

        //To ensure that all requested metrics fit with the period and time-interval
        $allowedMetrics = AppSettings::IG_INSIGHT_METRICS_AND_COMPATIBLE_PERIODS;
        foreach ($metrics as $metric) {
            if(!array_key_exists($metric, $allowedMetrics)) return array("error" => "Metric $metric is not a valid metric");

            if(!in_array($period, $allowedMetrics[$metric]))
                return array("error" => "The metric $metric does not support the period: $period");

            if(!empty($timeInterval) && in_array($metric, AppSettings::IG_INSIGHT_METRICS_NO_SUPPORT_CUSTOM_TIME_INTERVAL))
                return array("error" => "The metric $metric does not support custom time-intervals");
        }


        $baseUrl = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . AppSettings::INSIGHT_GRAPH_ENDPOINT;
        $baseUrl = str_replace("__ACCOUNT_ID__", $accountId, $baseUrl);

        //Example endpoint: 17841426011940894/insights?metric=impressions,reach&since=1651017600&until=1651104000&period=day
        $query = array(
            "period" => $period,
            "metric" => implode(",", $metrics),
            "access_token" => $accessToken,
            "after" => ""
        );
        if(!empty($timeInterval)) $query = array_merge($timeInterval, $query);

        $insights = array();
        $hasNext = true;

        $queryUrl = $baseUrl . "?" . http_build_query($query);
        while ($hasNext) {
            $this->httpHandler->send($queryUrl);
            $graphResponse = self::$util::ensureValidObjectResponse($this->httpHandler->getResponse());

            if(empty($graphResponse)) return array("error" => "Received a bad response from the graph");
            $data = self::$util::nestedArray($graphResponse, array("data"));

            if(empty($data)) break;
            $insights = array_merge($insights, $data);
            $hasNext = $this->resolveHasNextNoAfter($queryUrl, $graphResponse);
        }

        return $insights;
    }

    /*
     * For post creation, you can refer to this page for official documentation:
     * https://developers.facebook.com/docs/instagram-api/guides/content-publishing/
     */

    /**
     * @param string|int $accountId of instagram account
     * @param string $accessToken USER access token
     * @param string $mediaUrl link to media url
     * @param string $caption caption of post. Can be empty for children of carousel cards
     * @param bool $isImage whether the media is an image or video (true / false)
     * @return array
     *
     * Call this function to create a post on IG with a single media in it
     */
    public function createPost(string|int $accountId, string $accessToken, string $mediaUrl, string $caption, bool $isImage = true): array {
        $container = $this->createPostContainer($accountId, $accessToken, $mediaUrl, $caption, $isImage, false);
        if(!array_key_exists("id", $container)) return $container;

        return $this->publishContainer($accountId, $accessToken, $container["id"]);
    }


    /**
     * @param string|int $accountId of instagram account
     * @param string $accessToken USER access token
     * @param array $mediaUrls list object of format: [{"media_url" => "https...", "is_image" => "true"}]
     * @param string $caption Caption of the carousel
     * @return array
     *
     * Call this function to create a post on IG with multiple medias in it
     */
    public function createCarouselPost(string|int $accountId, string $accessToken, array $mediaUrls, string $caption): array {
        $childIds = array();
        if(count($mediaUrls) > 10) return array("error" => "Carousels are limited to a maximum of 10 cards");

        foreach ($mediaUrls as $child) {
            if(!array_key_exists("media_url", $child) || !array_key_exists("is_image", $child))
                return array("error" => "You need to specify fields: caption, media_url, is_video");

            $response = $this->createPostContainer($accountId, $accessToken, $child["media_url"], "", $child["is_image"], true);
            if(!array_key_exists("id", $response)) return $response;

            $childIds[] = $response["id"];
        }

        $query = array(
            "access_token" => $accessToken,
            "caption" => $caption,
            "media_type" => "CAROUSEL",
            "children" => $childIds,
        );

        $baseUrl = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . AppSettings::IG_POST_ENDPOINTS["container"];
        $baseUrl = str_replace("__ACCOUNT_ID__", $accountId, $baseUrl);

        $this->httpHandler->send($baseUrl . "?" . http_build_query($query), "POST");
        $graphResponse = self::$util::ensureValidObjectResponse($this->httpHandler->getResponse());

        if(empty($graphResponse)) return array("error" => "Received a bad response from the graph");
        if(!array_key_exists("id",$graphResponse)) return array("error" => "Received a bad response from the graph");

        $carouselContainerId = $graphResponse["id"];
        return $this->publishContainer($accountId, $accessToken, $carouselContainerId);
    }


    /**
     * @param string|int $accountId of instagram account
     * @param string $accessToken USER access token
     * @param string $mediaUrl link to media url
     * @param string $caption caption of post. Can be empty for children of carousel cards
     * @param bool $isImage
     * @param bool $isCarousel
     * @return array
     */
    public function createPostContainer
    (string|int $accountId, string $accessToken, string $mediaUrl, string $caption, bool $isImage = true, bool $isCarousel = false): array {
        $caption = str_replace("#", "%23", $caption); //Hashtags must be URL-encoded (%23)
        $query = array(
            "access_token" => $accessToken,
            "is_carousel_item " => $isCarousel,
        );

        if(!$isCarousel) $query["image_url"] = $caption;
        if($isImage) $query["image_url"] = $mediaUrl; //video or image. Beware that there might be minimum dimension requirements for this
        else {
            $query["media_type"] = "VIDEO";
            $query["video_url"] = $mediaUrl;
        }

        $baseUrl = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . AppSettings::IG_POST_ENDPOINTS["container"];
        $baseUrl = str_replace("__ACCOUNT_ID__", $accountId, $baseUrl);

        $this->httpHandler->send($baseUrl . "?" . http_build_query($query), "POST");
        $graphResponse = self::$util::ensureValidObjectResponse($this->httpHandler->getResponse());

        if(empty($graphResponse)) return array("error" => "Received a bad response from the graph");
        if(!array_key_exists("id",$graphResponse)) return array("error" => "Received a bad response from the graph");

        return $graphResponse;
    }


    /**
     * @param string|int $accountId of instagram account
     * @param string $accessToken USER access token
     * @param string|int $containerId id of the container returned by "createPostContainer()"
     * @return array
     */
    public function publishContainer(string|int $accountId, string $accessToken, string|int $containerId): array {
        $query = array(
            "access_token" => $accessToken,
            "creation_id" => $containerId,
        );

        $baseUrl = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . AppSettings::IG_POST_ENDPOINTS["publish_container"];
        $baseUrl = str_replace("__ACCOUNT_ID__", $accountId, $baseUrl);

        $this->httpHandler->send($baseUrl . "?" . http_build_query($query), "POST");
        $graphResponse = self::$util::ensureValidObjectResponse($this->httpHandler->getResponse());

        if(empty($graphResponse)) return array("error" => "Received a bad response from the graph");
        if(!array_key_exists("id",$graphResponse)) return array("error" => "Received a bad response from the graph");

        return $graphResponse;
    }



    public function businessDiscovery(string $username): ?array {
        $integrationHandler = (new AbstractCrudObject())->integrations();
        $randomIntegration = $integrationHandler->getRandomIntegration();
        if(empty($randomIntegration)) return null;

        $query = array(
            "fields" => "business_discovery.username($username){" . implode(",", AppSettings::BUSINESS_DISCOVERY_FIELDS) . "}",
            "access_token" => $integrationHandler->extractIgToken($randomIntegration),
        );


        $baseUrl = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . AppSettings::IG_BUSINESS_DISCOVERY_ENDPOINT;
        $baseUrl = str_replace("__IG_ID__", $integrationHandler->extractIgId($randomIntegration), $baseUrl);


//        echo $baseUrl . "?" . http_build_query($query) . "<br><br>";
        $this->httpHandler->send($baseUrl . "?" . http_build_query($query));
        $graphResponse = self::$util::ensureValidObjectResponse($this->httpHandler->getResponse());

        file_put_contents(ROOT . "testLogs/api-data.json", json_encode($graphResponse, JSON_PRETTY_PRINT));

        if(empty($graphResponse)) return array("error" => "Received a bad response from the graph");
        return $graphResponse;
    }





    public function queryMention(array $params, string $type): array {
        $url = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . $params["account_id"];
        switch ($type) {
            default: return [];
            case "post":
                $query = [
                    "fields" => implode(",", [
                        "mentioned_media.media_id(" . $params["media_id"] . "){" . implode(",", AppSettings::MENTIONS_QUERY["post"]) . "}",
                        "username"
                    ])
                ];
                break;
            case "story":
                $query = [
                    "fields" => "messages{from,story},link",
                    "platform" => "instagram",
                    "user_id" => $params["ig_sid"]
                ];
                $url .= "/conversations";
                break;
        }

        $query["access_token"] = $params["access_token"];
        file_put_contents(TESTLOGS . "specialLogs/mention-query-params-".time().".json", json_encode($query, JSON_PRETTY_PRINT));

        $this->httpHandler->send($url . "?" . http_build_query($query));
        return $this->httpHandler->getResponse();
    }


    public function queryMessage(string $pageToken, string|int $igId): array {
        $url = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/$igId";
        $query = [
            "fields" => "name,username,profile_pic",
            "access_token" => $pageToken
        ];

        file_put_contents(TESTLOGS . "specialLogs/message-query-params-".time().".json", json_encode($query, JSON_PRETTY_PRINT));

        $this->httpHandler->send($url . "?" . http_build_query($query));
        return $this->httpHandler->getResponse();
    }


    public function sendMessage(string $pageToken, string|int $igId, string $textMessage): array {
        $url = AppSettings::FB_GRAPH_BASE_URL . AppSettings::SEND_MESSAGE_ENDPOINT;
        $query = [
            "recipient" => [
                "id" => $igId
            ],
            "message" => [
                "text" => $textMessage
            ],
            "access_token" => $pageToken
        ];

        file_put_contents(TESTLOGS . "specialLogs/message-send-params-".time().".json", json_encode($query, JSON_PRETTY_PRINT));

        $this->httpHandler->send($url . "?" . http_build_query($query), "POST");
        return $this->httpHandler->getResponse();
    }




}