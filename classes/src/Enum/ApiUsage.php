<?php

namespace classes\src\Enum;

class ApiUsage {

    public static function getUrlMediaMeta(string $accessToken, string|int $id): string { return self::getRequestUrl("media_meta", $accessToken, $id); }
    public static function getUrlProfileMeta(string $accessToken, string|int $id): string { return self::getRequestUrl("profile_meta", $accessToken, $id); }
    public static function getUrlMediaInsights(string $accessToken, string|int $id): string { return self::getRequestUrl("media_insight", $accessToken, $id); }
    public static function getUrlMediaDiscovery(string $accessToken, string|int $id): string { return self::getRequestUrl("media_discovery", $accessToken, $id); }
    public static function getUrlTagged(string $accessToken, string|int $id): string { return self::getRequestUrl("tagged", $accessToken, $id); }
    public static function getBaseUrlMediaDiscovery(string $accessToken, string|int $id): string { return self::getRequestUrl("media_discovery", $accessToken, $id, true); }
    public static function getBaseUrlTagged(string $accessToken, string|int $id): string { return self::getRequestUrl("tagged", $accessToken, $id, true); }
    public static function getQueryMediaDiscovery(string $accessToken, string|int $id): array { return self::getRequestUrl("media_discovery", $accessToken, $id,false, true); }
    public static function getQueryTagged(string $accessToken, string|int $id): array { return self::getRequestUrl("tagged", $accessToken, $id,false, true); }
    public static function getUrlAccountDemographicInsights(string $accessToken, string|int $id): string { return self::getRequestUrl("account_demographic_insight", $accessToken, $id); }
    public static function getUrlAccountReachInsights(string $accessToken, string|int $id): string { return self::getRequestUrl("account_reach_insight", $accessToken, $id); }


    private const IG_MEDIA_INSIGHT_METRICS = [
        "comments","likes","reach","saved","shares","total_interactions"
    ];
    private const IG_MEDIA_FIELDS = [
        "id","caption","media_product_type","media_url","thumbnail_url","permalink","timestamp", "shortcode"
    ];
    private const IG_PROFILE_FIELDS = [
        "followers_count","biography","follows_count","media_count","name","profile_picture_url","website", "ig_id", "username"
    ];
    private const TAGGED_PAGE_FIELDS = [
        "like_count","comments_count","caption","id","media_product_type","media_type","media_url","permalink","thumbnail_url","timestamp","username"
    ];
    private const IG_PROFILE_INSIGHT_LIFETIME_METRICS = [
        "audience_city","audience_country","audience_gender_age","online_followers"
    ];
    private const IG_PROFILE_INSIGHT_4_WEEKS_METRICS = [
        "impressions","reach"
    ];


    private static function getRequestUrl(string $type, string $accessToken, string|int $id, bool $excludeQuery = false, bool $queryOnly = false): string|array {
        $baseUrl = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/";
        $endpoint = match ($type) {
            default => "",
            "media_meta", "profile_meta" => $id,
            "profile_insight", "media_insight", "account_demographic_insight", "account_reach_insight" => $id . "/insights",
            "media_discovery" => $id . "/media",
            "tagged" => $id . "/tags",
        };

        if($excludeQuery) return $baseUrl . $endpoint;
        if($queryOnly) return self::getQuery($type, $accessToken);
        return  $baseUrl . $endpoint . "?" . http_build_query(self::getQuery($type, $accessToken));
    }

    private static function getQuery(string $type, string $accessToken): array {
        return array_merge(
            match ($type) {
                default => [],
                "media_insight" => [
                    "metric" => implode(",", self::IG_MEDIA_INSIGHT_METRICS),
                    "period" => "lifetime"
                ],
                "media_meta" => [
                    "fields" => implode(",", self::IG_MEDIA_FIELDS)
                ],
                "profile_meta" => [
                    "fields" => implode(",", self::IG_PROFILE_FIELDS)
                ],
                "media_discovery" => [
                    "fields" => implode(",", self::IG_MEDIA_FIELDS) .
                        ",insights.metric(" . implode(",", self::IG_MEDIA_INSIGHT_METRICS) . ")",
                    "after" => ""
                ],
                "tagged" => [
                    "fields" => implode(",", self::TAGGED_PAGE_FIELDS),
                    "after" => ""
                ],
                "account_demographic_insight" => [
                    "metric" => implode(",", self::IG_PROFILE_INSIGHT_LIFETIME_METRICS),
                    "period" => "lifetime"
                ],
                "account_reach_insight" => [
                    "metric" => implode(",", self::IG_PROFILE_INSIGHT_4_WEEKS_METRICS),
                    "period" => "days_28"
                ],
            },
            [
                "access_token" => $accessToken
            ]
        );
    }

}