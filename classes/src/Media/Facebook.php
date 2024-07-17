<?php

namespace classes\src\Media;

use classes\src\Enum\AppSettings;

class Facebook extends AbstractMedias {

    function __construct() { parent::__construct(); }




    /**
     * Pass in the long-lived access token
     * @param string|array $accessToken
     * @return array
     */
    public function getAccounts(string|array $accessToken): array {
        if(!is_string($accessToken)) return array("error" => "Access token must be of type: String");
        $url = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . AppSettings::AUTH_GRAPH_ENDPOINTS["user_accounts"];
        $query = array(
            "limit" => 50,
            "access_token" => $accessToken,
            "after" => ""
        );

        $hasNext = true;
        $pages = array();

        //Depending on how many pages a user has, we may need to get "next pages" as well.
        while ($hasNext) {
            $this->httpHandler->send($url . "?" . http_build_query($query));
            $graphResponse = $this->httpHandler->getResponse();
            file_put_contents(TESTLOGS . "specialLogs/fbacc.json", json_encode($graphResponse, JSON_PRETTY_PRINT));

            if(!is_array($graphResponse)) $graphResponse = json_decode($graphResponse, true);
            if(!is_array($graphResponse)) return array("error" => "Received a bad response from the graph");

            $data = self::$util::nestedArray($graphResponse, array("data"));
            if(empty($data)) break;

            $pages = array_merge($pages, $data);
            $hasNext = $this->resolveHasNextWithAfter($query, $graphResponse);
        }

        if(empty($pages)) return array();


        //Grabbing page-id, page access-token and name
        return array_map(function ($page) use ($accessToken) {
            return array("item_id" => $page["id"], "item_name" => $page["name"], "item_token" => $page["access_token"], "token_extra" => $accessToken);
        }, $pages);
    }



    /**
     * @param string $accessToken Facebook PAGE access-token. NOT user-token.
     * @param string|int $accountId Facebook Page ID
     * @param array $metrics List of metric names
     * @param string $period day|week|lifetime|days_28 - For Facebook insights, this param is OPTIONAL
     * @param array $timeInterval ["since" => "int", "until" => "int"] || ["date_preset" => "string"]
     * @return array[]
     *
     * View AppSettings file for full list of available metrics, date_presets and supported periods.
     *
     */
    public function getInsights(string $accessToken, string|int $accountId, array $metrics, string $period, array $timeInterval = array() ): array {
        if(!empty($timeInterval) &&
            (!(array_key_exists("since", $timeInterval) && array_key_exists("until", $timeInterval)) ||
                !array_key_exists("date_preset", $timeInterval)))
            return array("error" => "Time-interval must include either the keys 'since' and 'until' OR a date_preset");

        //To ensure that all requested metrics fit with the period and time-interval
        $allowedMetrics = AppSettings::FB_PAGE_METRICS;

        foreach ($metrics as $metric) {
            $metricIsOkay = false;

            foreach ($allowedMetrics as $category => $metricList) {
                if(!array_key_exists($metric, $metricList)) continue;
                if(!empty($period) && !in_array($period, $metricList[$metric]))
                    return array("error" => "The metric $metric does not support the period: $period");

                $metricIsOkay = true;
                break;
            }

            if(!$metricIsOkay) return array("error" => "Metric $metric is not a valid metric");
        }


        $baseUrl = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . AppSettings::INSIGHT_GRAPH_ENDPOINT;
        $baseUrl = str_replace("__ACCOUNT_ID__", $accountId, $baseUrl);

        //Example endpoint: 17841426011940894/insights?metric=post_clicks&since=1651017600&until=1651104000&period=day
        $query = array(
            "metric" => implode(",", $metrics),
            "access_token" => $accessToken,
            "after" => ""
        );
        if(!empty($period)) $query = array_merge($query, array("period" => $period));
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


    public function createPhotoPost(string|int $pageId, string $accessToken, string $caption, string $imageUrl): array {
        $query = array(
            "caption" => $caption,
            "url" => $imageUrl,
            "access_token" => $accessToken
        );

        $baseUrl = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . AppSettings::FB_POST_ENDPOINTS["photos"];
        $baseUrl = str_replace("__PAGE_ID__", $pageId, $baseUrl);

        $this->httpHandler->send($baseUrl . "?" . http_build_query($query), "POST");
        $graphResponse = self::$util::ensureValidObjectResponse($this->httpHandler->getResponse());

        if(empty($graphResponse)) return array("error" => "Received a bad response from the graph");
        if(!array_key_exists("id",$graphResponse)) return array("error" => "Received a bad response from the graph");

        return $graphResponse;
    }


    public function createLinkPost(string|int $pageId, string $accessToken, string $message, string $link, array $cta = array()): array {
//        $FORMAT = array(
//            "message" => "",
//            "link" => "",
//            "call_to_action" => array( "type" => "", "link" => "" ),
//        );

        $query = array(
            "message" => $message,
            "link" => $link,
            "access_token" => $accessToken,
        );

            if(!empty($cta)) {
                if(!(array_key_exists("type", $cta) && array_key_exists("link", $cta)))
                    return array("error" => "Invalid call_to_action format");

                $ctaType = $cta["type"];
                if(!in_array($ctaType, AppSettings::CTA_TYPES)) return array("error" => "$ctaType is not a valid CTA type");
                $query["call_to_action"] = $cta;
            }

        $baseUrl = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . AppSettings::FB_POST_ENDPOINTS["feed"];
        $baseUrl = str_replace("__PAGE_ID__", $pageId, $baseUrl);

        $this->httpHandler->send($baseUrl . "?" . http_build_query($query), "POST");
        $graphResponse = self::$util::ensureValidObjectResponse($this->httpHandler->getResponse());

        if(empty($graphResponse)) return array("error" => "Received a bad response from the graph");
        if(!array_key_exists("id",$graphResponse)) return array("error" => "Received a bad response from the graph");

        return $graphResponse;
    }


    public function createVideoPost(string|int $pageId, string $accessToken, string $title, string $description, string $videoUrl): array {
        $query = array(
            "title" => $title,
            "description" => $description,
            "file_url" => $videoUrl,
            "access_token" => $accessToken
        );

        $baseUrl = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . AppSettings::FB_POST_ENDPOINTS["videos"];
        $baseUrl = str_replace("__PAGE_ID__", $pageId, $baseUrl);

        $this->httpHandler->send($baseUrl . "?" . http_build_query($query), "POST");
        $graphResponse = self::$util::ensureValidObjectResponse($this->httpHandler->getResponse());

        if(empty($graphResponse)) return array("error" => "Received a bad response from the graph");
        if(!array_key_exists("id",$graphResponse)) return array("error" => "Received a bad response from the graph");

        return $graphResponse;
    }






}