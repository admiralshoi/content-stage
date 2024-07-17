<?php

namespace classes\src\Object\objects;

use classes\src\AbstractCrudObject;
use classes\src\Enum\ScraperNestedLists;
use classes\src\Object\Handler;
use classes\src\Object\Scraper;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class DataHandler {
    protected int $requestingUsersAccessLevel = 0;
    protected int $requestingUsersId = 0;
    protected AbstractCrudObject $crud;


    function __construct(AbstractCrudObject $crud) {
        $this->crud = $crud;

        if(isset($_SESSION["access_level"])) $this->requestingUsersAccessLevel = $_SESSION["access_level"];
        if(isset($_SESSION["uid"])) $this->requestingUsersId = $_SESSION["uid"];
    }




    public function exchangeMediaFields(array $scrapeMedia, array $apiMedia = [], bool|int $downloadImages = true): array {
        $collector = array();
        if($downloadImages) $scrapeMedia = $this->downloadMediasAndUpdateUrl($scrapeMedia);

        foreach ($scrapeMedia as $media) {
            $shortCode = array_key_exists("shortcode", $media) ? $media["shortcode"] : $this->instagramUrlShortCode($media["permalink"]);
            $item = $media;

            $twin = empty($apiMedia) ? [] :
                array_values(array_filter($apiMedia, function($item) use ($shortCode) { return $shortCode === $this->instagramUrlShortCode($item["permalink"]); }));
            if(!empty($twin)) {
                $twin = $twin[0];
                foreach ($item as $key => $value) if (array_key_exists($key, $twin) && !empty($twin[$key])) {
                    if($key === "timestamp" && is_string($twin["timestamp"])) $twin[$key] = strtotime($twin[$key]);
                    $item[$key] = $twin[$key];
                }
            }

            $collector[] = $item;
        }
        return $collector;
    }


    public function mediaStorageDirectory(string $type): string {
        $base = "images/content/";
        return match ($type) {
            default => $base . "creators/",
            "post" => $base . "posts/",
            "story" => $base . "stories/",
        };
    }

    public function downloadMediasAndUpdateUrl(
        array $medias,
        bool $extractFilenameFromUrl = true,
        string $prefixString = "",
        string|int $prefixKey = "",
        string $fixedFn = "",
        bool $streamOpt = false
    ): array {
        if(empty($medias)) return $medias;
        if(!array_key_exists(0, $medias)) $medias = [$medias];

        $collector = [];
        foreach ($medias as $media) {
            $type = array_key_exists("type", $media) ? $media["type"]: "";
            $keyList = ["display_url", "media_url", "profile_picture", "video_url", "picture", "image_url"];
            $item = $media;

            foreach (array_keys($media) as $mediaKey) {
                if(!$this->crud->settings->download_media) continue;
                if(!in_array($mediaKey, $keyList)) continue;
                if($mediaKey === "video_url" && !(array_key_exists("type", $media) && $media["type"] === "story")) continue;

                if(array_key_exists($mediaKey, $media) && !empty($media[$mediaKey])) {
                    $filename = !empty($fixedFn) ? $fixedFn : "";
                    if(empty($fixedFn) && !$extractFilenameFromUrl) {
                        if(!empty($prefixKey) && array_key_exists($prefixKey, $item)) $filename = $item[$prefixKey] . "-";
                        if(!empty($prefixString)) $filename .= $prefixString;
                    }
                    $storageDirectory = $this->mediaStorageDirectory($type);
                    $downloadedFilename = $this->crud->downloadMedia(
                        $media[$mediaKey],
                        ROOT . $storageDirectory,
                        null,
                        $filename,
                        empty($fixedFn),
                        ($prefixString === "profile-picture"),
                        $streamOpt
                    );

                    file_put_contents(TESTLOGS . "specialLogs/mediainfo.json", json_encode([
                        $mediaKey, $media[$mediaKey], $storageDirectory, $filename, $prefixString, $downloadedFilename, $streamOpt
                    ], JSON_PRETTY_PRINT));
                    if(!$downloadedFilename) continue;
                    $item[$mediaKey] = $storageDirectory . $downloadedFilename;
                }
            }

            $collector[] = $item;
        }

        return $collector;
    }


    public function storyExtractMedias(array $mediaItems): array {
        return array_map(function ($item) {
            $data = $this->getEdgeData($item, ScraperNestedLists::STORY_MEDIA_DATA);
            $data["display_urls"] = [
                "image_url" => $data["image_url"],
                "video_url" => $data["video_url"],
            ];
            unset($data["image_url"]);
            unset($data["video_url"]);

            $data["mentions"] = $this->storyExtractMentions($data["mentions"]);

            return $data;
        }, $mediaItems);
    }

    public function storyExtractMentions(array $stickers): array {
        if(empty($stickers)) return [];

        file_put_contents(ROOT . "testLogs/story-stickers.json", json_encode($stickers, JSON_PRETTY_PRINT));
        $data = array_map(function ($item) {
            return $this->crud->nestedArray($item, ["bloks_sticker", "sticker_data", "ig_mention", "username"], "");
        }, $stickers);

        return array_values(array_unique(array_filter($data, function ($item) { return !empty($item); })));
    }



    public function hashtagExplorationExtractMedias(array $recentSections): array {
        if(empty($recentSections)) return [];
        $listOfMedias = array_map(function ($list) {
            return $this->getEdgeData($list, ScraperNestedLists::HASHTAG_EXPLORE_RECENT_SECTIONS);
        }, $recentSections);
        if(empty($listOfMedias)) return [];

        $collector = [];
        foreach ($listOfMedias as $list) {
            if(empty($list)) continue;
            $collector = array_merge(
                $collector,
                array_map(function ($item) {
                    $mediaBaseData = $this->getEdgeData($item, ScraperNestedLists::HASHTAG_EXPLORE_MEDIA_DATA);

                    if($mediaBaseData["media_type"] === 8) $mediaBaseData["carousel"] = 1;
                    if($mediaBaseData["media_type"] === 2) $mediaBaseData["media_type"] = "VIDEO";
                    else $mediaBaseData["media_type"] = "IMAGE";

                    return $mediaBaseData;
                }, $list["medias"])
            );
        }
        return $collector;
    }


    public function extractMediaData(array $data, bool $api = true, bool $mediaDataOnly = false, int $followerCount = 0): array {
        $mediaData = $mediaDataOnly ? $data : $data["media"];
        if($this->crud->isAssoc($mediaData)) $mediaData = [$mediaData];
        $edgeData = array_map(function ($media) use ($api) {
            return $this->getEdgeData($media, $api ? ScraperNestedLists::MEDIA_DATA_API : ScraperNestedLists::MEDIA_DATA_SCRAPE);
        }, $mediaData);

        if(!$mediaDataOnly && array_key_exists("reels_media", $data) && !empty($data["reels_media"]))
            $edgeData = array_merge(
                $edgeData,
                array_map(function ($media) use ($api) {
                    return $this->getEdgeData($media, ScraperNestedLists::MEDIA_DATA_VIDEO_SCRAPE);
                }, $data["reels_media"])
            );



        if(!$api) {
            $edgeData = array_map(function ($data) {
                $data["media_type"] = $data["is_video"] ? "VIDEO" : "IMAGE";
                $data["permalink"] = "https://instagram.com/p/" . $data["shortcode"];
                $data["pinned"] = (int)(array_key_exists("pinned", $data) && !empty($data["pinned"]));
                foreach (array("is_video") as $key) unset($data[$key]);
                return $data;
            }, $edgeData);
        }


        $edgeData = array_map(function ($item) use ($data, $mediaDataOnly, $followerCount) {
            return array_merge(
                $item,
                array(
                    "hashtags" => $this->setHashtags($item),
                    "location" => $this->setLocation($item),
                    "engagement_rate" => $this->engagementRate(array_merge($item, array("followers_count" => $mediaDataOnly ? $followerCount : $data["followers_count"])))
                )
            );
        }, $edgeData);

        if($mediaDataOnly) return $edgeData;
        $data["media"] = $edgeData;
        return $data;
    }

    public function fromMediaSetUserAverages(array $userData): array {
        if(empty($userData) || !array_key_exists("media", $userData)) return $userData;
        $mediaAverageKPI = $this->averageKpi($userData["media"]);
        $mediaTotalKpi = $this->averageToTotal($mediaAverageKPI, count($userData["media"]));
        $userData["like_count"] = $mediaTotalKpi["total_likes_count"];
        $userData["comments_count"] = $mediaTotalKpi["total_comments_count"];
        return array_merge($userData, $mediaAverageKPI);
    }







    public function getEdgeData(?array $data, array $nestedList): array {
        $response = array();
        $defaultValues = array(
            "int" => 0,
            "string" => "",
            "string|int" => "",
            "bool" => null,
            "array" => array(),
        );

        foreach ($nestedList as $key => $config) {
            if(is_null($data)) $value = $defaultValues[$config["type"]];
            else $value = $this->crud->nestedArray($data, $config["nest"]);

            if(is_null($value)) $value = $defaultValues[$config["type"]];
            else {
                switch ($config["type"]) {
                    default: break;
                    case "int": $value = (int)$value; break;
                    case "string": $value = (string)$value; break;
                    case "string|int": $value = is_int($value) ? $value : (is_string($value) ? $value : (string)$value); break;
                    case "bool": $value = (bool)$value; break;
                    case "array":
                        $value = !is_array($value) ? json_decode($value, true) : $value;
                        if(!is_array($value)) $value = $defaultValues[$config["type"]]; break;
                }
            }

            $response[$key] = $value;
        }

        return $response;
    }


    public function setLibraryData(array $edgeData): array {
        return array_merge($edgeData, array(
            "engagement_rate" => $this->engagementRate($edgeData),
            "gender" => $this->setGender($edgeData),
        ));
    }





    /*
     * Below is support functions
     *
     */


    #[ArrayShape(["total_likes_count" => "float|int", "total_comments_count" => "float|int"])]
    public function averageToTotal(array $averageKpi, int $totalMediaCount): array {
        $averageLikesToTotal = $averageKpi["average_like_count"] < 0 ? -1 : ($averageKpi["average_like_count"] * $totalMediaCount);
        $averageCommentsToTotal = $averageKpi["average_comments_count"] * $totalMediaCount;
        return array("total_likes_count" => $averageLikesToTotal,"total_comments_count" => $averageCommentsToTotal);
    }


    #[ArrayShape(["average_like_count" => "float|int", "average_comments_count" => "float|int"])]
    public function averageKpi(array $postData): array {
        $res = array("average_like_count" => 0,"average_comments_count" => 0);
        if(empty($postData)) return $res;
        $likesCountFromPosts = array_reduce($postData, function ($initial, $post) { return (!isset($initial) ? 0 : $initial) + $post["like_count"]; });
        $commentsCountFromPosts = array_reduce($postData, function ($initial, $post) { return (!isset($initial) ? 0 : $initial) + $post["comments_count"]; });
        $postCount = count($postData);

        $res["average_like_count"] = $postCount === 0 ? 0 : ceil($likesCountFromPosts / $postCount);
        $res["average_comments_count"] = $postCount === 0 ? 0 : ceil($commentsCountFromPosts / $postCount);
        return $res;
    }



    public function engagementRate(array $object): float {
        $expectedFields = array("like_count", "comments_count", "followers_count");
        foreach ($expectedFields as $field) if(!array_key_exists($field, $object)) return 0;

        $totalEngagement =
            (array_key_exists("average_comments_count", $object) ? $object["average_comments_count"] : $object["comments_count"])
            +
            (array_key_exists("average_like_count", $object) ? $object["average_like_count"] : $object["like_count"]) ;

        return $object["followers_count"] === 0 ? 100 : round($totalEngagement / $object["followers_count"] * 100, 2);
    }



    private function setGender(array $object, ?Misc $misc = null): ?string {
        $expectedFields = array("full_name");
        foreach ($expectedFields as $field) if(!array_key_exists($field, $object)) return null;

        $fullName = trim(strtolower($object["full_name"]));
        if(empty($fullName)) return null;

        if($misc === null) $misc = $this->crud->misc();

        $nameList = explode(" ", $fullName);
        $firstName = $nameList[0];
        $genderLibrary = $misc->getNamesLibrary();

        $matchingName = array_values(array_filter(array_keys($genderLibrary), function ($name) use ($firstName) {
            return $firstName === strtolower($name);
        }));
        if(empty($matchingName)) return null;

        $matchingName = $matchingName[0];
        return $genderLibrary[$matchingName]["gender"];
    }




    public function setHashtags(array $object): array {
        $collector = array();
        $expectedFields = array("caption");
        foreach ($expectedFields as $field) if(!array_key_exists($field, $object)) return array();

        $caption = $object["caption"];
        if(empty($caption)) return array();

        $words = explode(" ", $caption);

        foreach ($words as $word) {
            $word = trim($word);
            if(!str_starts_with($word, "#")) continue;

            if(strlen($word) < 2) continue;
            $collector[] = strtolower($word);
        }

        return $collector;
    }


    public function setLocation(array $object): array {
        if(array_key_exists("media", $object)) $object = $object["media"];
        $expectedFields = array("caption", "location");
        foreach ($expectedFields as $field) if(!array_key_exists($field, $object)) return array();

        $caption = trim($object["caption"]);
        $location = $object["location"];

        if(!empty($location)) {
            $captureLocationFields = array();
            foreach (array("zip_code", "city_name", "region_name", "country_code") as $key) {
                if(!array_key_exists($key, $location)) continue;
                $captureLocationFields[$key] = $location[$key];
            }
            if(empty($captureLocationFields)) {
                foreach (array("name") as $key) {
                    if(!array_key_exists($key, $location)) continue;

                    $split = explode(",", $location[$key]);
                    if(count($split) === 1) $captureLocationFields["city_name"] = $split[0];
                    elseif(count($split) === 2) {
                        $captureLocationFields["city_name"] = $split[0];
                        $captureLocationFields["country_name"] = $split[1];
                    }
                    elseif(count($split) === 3) {
                        $captureLocationFields["city_name"] = $split[0];
                        $captureLocationFields["region_name"] = $split[1];
                        $captureLocationFields["country_name"] = $split[2];
                    }

                }
            }
            if(empty($captureLocationFields)) return array();


            if(empty($captureLocationFields["country_code"]) && array_key_exists("city_name", $captureLocationFields) && !empty($captureLocationFields["city_name"])) {
                $city = array_key_exists("country_name", $captureLocationFields) ? $captureLocationFields["country_name"] : $captureLocationFields["city_name"];
                $countryCodesByLocations = json_decode(file_get_contents(COUNTRY_SEARCH_LIB), true);

                $city = str_replace(",", "", $city);
                $city = str_replace(".", "", $city);
                $cityWordParts = explode(" ", $city);

                if(!empty($cityWordParts)) {
                    $cityWordParts = array_reverse($cityWordParts);
                    foreach ($cityWordParts as $wordPart) {
                        $word = strtolower(trim($wordPart));
                        if(array_key_exists($word, $countryCodesByLocations)) {
                            $captureLocationFields["country_code"] = $countryCodesByLocations[$word];
                            break;
                        }
                    }
                }
            }

            $countryNames = json_decode(file_get_contents(COUNTRY_NAME_BY_CODE), true);
            if(array_key_exists("country_code", $captureLocationFields))  {
                $captureLocationFields["country_name"] = array_key_exists($captureLocationFields["country_code"], $countryNames) ?
                    strtolower($countryNames[ ($captureLocationFields["country_code"]) ]) : "";
            }

            $captureLocationFields["tagged_location"] = true;

            return $captureLocationFields;
        }

        $searchLocation = $this->searchStringForCountry($caption);
        if(empty($searchLocation)) return array();

        $searchLocation["tagged_location"] = false;
        return $searchLocation;
    }







    private function searchStringForCountry(string $string): array {
        if(empty($string)) return array();
        $response = array();
        $debugNewCountries = false;

        $countries = json_decode(file_get_contents(COUNTRY_SEARCH_LIB), true);
        $countryNames = json_decode(file_get_contents(COUNTRY_NAME_BY_CODE), true);
        $debugList = file_exists(DEBUG_COUNTRY) ? json_decode(file_get_contents(DEBUG_COUNTRY), true) : array();

        $words = explode(" ",$string);
        foreach ($words as $word) {
            $word = trim(strtolower($word));

            if(array_key_exists($word,$countries) && preg_match('~^\p{Lu}~u', $word) && strlen($word) > 4) {
                $countryCode = $countries[$word];
                if(!array_key_exists($countryCode,$countryNames)) continue;

                $countryName = strtolower($countryNames[$countryCode]);
                $response = array("country_code" => $countryCode, "country_name" => $countryName, "key_word" => $word);

                if(!array_key_exists($countryName,$debugList)) $debugList[$countryName] = array();
                if(!in_array($word,$debugList[$countryName])) {
                    array_push($debugList[$countryName], $word);
                    $debugNewCountries = true;
                }
            }
        }

        if($debugNewCountries) file_put_contents(DEBUG_COUNTRY,json_encode($debugList,JSON_PRETTY_PRINT), 2);
        return $response;
    }



    public function trimHashtagLookup(?array $data, ?int $top = null, bool $aiList = false): array {
        if(empty($data)) return array();
        if($aiList) {
            $tagEdgeData = array_map(function ($item) {
                $list = explode("#", $item);
                return [
                "name" => array_pop($list),
                "count" => 0,
                "subtitle" => "",
                "picture" => "",
            ]; }, $data);
        }
        else {
            if(!array_key_exists("hashtags", $data) || empty($data["hashtags"])) return [];
            $hashtags = $data["hashtags"];
            $this->crud->sortByKey($hashtags, "position", true);

            $tagEdgeData = array_map(function ($item) { return $this->getEdgeData($item, ScraperNestedLists::HASHTAG_LOOKUP); }, $hashtags);
        }

        return is_null($top) ? $tagEdgeData : array_splice($tagEdgeData, 0, $top);
    }

    public function sortAndTrim(?array $data, string $key, bool $ascending = false, ?int $top = null): array {
        $this->crud->sortByKey($data, $key, $ascending);
        return is_null($top) ? $data : array_splice($data, 0, $top);
    }



    public function instagramUrlShortCode(string $url): string {
        if(empty($url)) return "";
        if(!filter_var($url,FILTER_VALIDATE_URL)) return "";

        $url = parse_url($url);
        if(!array_key_exists("path",$url)) return "";

        $path = trim($url["path"]);
        if(empty($path) || !str_contains($path, "/")) return $path;

        $split = explode("/",$path);
        $lastIndex = (count($split) - 1);

        return empty($split[$lastIndex]) ? $split[ ($lastIndex-1) ] :  $split[$lastIndex];
    }

    public function hasNext(array $cursor): bool { return $this->crud->nestedArray($cursor, ["has_next_page"], false); }
    public function getNextHash(array $cursor): ?string { return $this->crud->nestedArray($cursor, ["end_cursor"]); }
    public function mediaGetOldestTimestamp(array $medias): int {
        $dataNoPins = array_values(array_filter($medias, function ($media) { return $media["pinned"] === 0; }));
        $this->crud->sortByKey($dataNoPins, "timestamp", true);
        return (int)$dataNoPins[0]["timestamp"];
    }



    public function findAndFlushEmail($userData): ?string {
        foreach ($userData as $key => $value) {
            if(!is_string($value)) continue;
            $words = explode(" ", $value);

            $emailSearch = array_values(array_filter($words, function ($word) {
                return filter_var($word, FILTER_VALIDATE_EMAIL);
            }));
            if(!empty($emailSearch)) return $emailSearch[0];
        }
        return null;
    }



    public function guessLocation(array $objects): string {
        if(empty($objects)) return "";
        $codes = [];
        if (
            array_key_exists("location", $objects[0]) && array_key_exists("country_code", $objects[0]["location"]) ||
            array_key_exists("country_code", $objects)
        ) $key = "country_code";
        elseif (
            array_key_exists("location", $objects[0]) && array_key_exists("country", $objects[0]["location"]) ||
            array_key_exists("country", $objects)
        ) $key = "country";
        else return "";


        foreach ($objects as $object) {
            if(array_key_exists("location", $object) && array_key_exists($key, $object["location"])) $codes[] = $object["location"][$key];
            elseif(array_key_exists($key, $object)) $codes[] = $object[$key];
        }
        if(empty($codes)) return "";

        $codeCount = [];
        foreach ($codes as $code) {
            if(!array_key_exists($code, $codeCount)) $codeCount[$code] = ["code" => $code, "count" => 0];
            $codeCount[$code]["count"]++;
        }

        $this->crud->sortByKey($codeCount, "count");
        return $codeCount[0]["code"];
    }



    public function taggedPageMediaData(array $edges): array {
        if(empty($edges)) return $edges;
        $edgeData = array_map(function ($data) {
            return $this->getEdgeData($data, ScraperNestedLists::TAGGED_PAGE_EDGE);
        }, $edges);


        $edgeData = array_map(function ($data) {
            $data["media_type"] = $data["is_video"] ? "VIDEO" : "IMAGE";
            $data["permalink"] = "https://instagram.com/p/" . $data["shortcode"];
            $data["type"] = "post";
            foreach (array("is_video") as $key) unset($data[$key]);


            return $data;
        }, $edgeData);

        return $edgeData;
    }




}