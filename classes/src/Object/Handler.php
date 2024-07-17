<?php

namespace classes\src\Object;

use classes\src\AbstractCrudObject;
use classes\src\Enum\ScraperNestedLists;
use classes\src\Media\Medias;
use classes\src\Object\objects\DataHandler;
use JetBrains\PhpStorm\Pure;
use classes\src\Enum\HandlerErrors;

class Handler extends DataHandler{
    #[Pure] function __construct(AbstractCrudObject $crud) {
        parent::__construct($crud);
    }

    const MIN_DAYS_MEDIA = 0;
    private int $minimumTimestamp = 0;



    public function instagramGetUserEdge(string $username, ?Scraper &$scraper = null): array {
        $i = 0;
        $userData = null;

        if(is_null($scraper)) {
            $scraper = $this->crud->scraper();
            $scraper->setCookieManager($this->crud)->cookieSetDefault()->cookieAddToHeader();
        }

        while($i <= 10) {
            $i++;
            $userData = $scraper->getProfilePage($username);
            if(gettype($userData) !== "NULL" && !is_null($userData)) break;
            $scraper->cookieUsageIncrement(false);
            if(!$scraper->isUnusedCookies()) break;
            $scraper->cookieSet()->cookieAddToHeader(); //Set new random cookie
        }
        if(is_null($userData)) return HandlerErrors::SCRAPER_ERROR;
        if(array_key_exists("error", $userData)) return $userData;

        file_put_contents(ROOT . "testLogs/lookup-raw.json", json_encode($userData, JSON_PRETTY_PRINT));
        $scraper->cookieUsageIncrement();

        return $this->getEdgeData($userData, ScraperNestedLists::PROFILE_PAGE_SCRAPE);
    }

    public function instagramLookupUser(string $username, ?Scraper &$scraper = null): ?array {
        $edgeData = $this->instagramGetUserEdge($username, $scraper);
        if(empty($edgeData["media"])) $edgeData["media"] = $this->crud->nestedArray($scraper->queryUserTimelineMedia("", $edgeData["ig_id"]), ["edges"], []);
        file_put_contents(ROOT . "testLogs/2222.json", json_encode($edgeData, JSON_PRETTY_PRINT));
        $edgeData = $this->extractMediaData($edgeData, false);
        file_put_contents(ROOT . "testLogs/3333.json", json_encode($edgeData, JSON_PRETTY_PRINT));

        $edgeData["media"] = array_map(function ($media) { return array_merge($media, ["type" => "post"]); }, $edgeData["media"]);
        $edgeData["media"] = $this->exchangeMediaFields($edgeData["media"], [], $this->crud->settings->download_media);

        $data = $this->setLibraryData($edgeData);
        $data["email"] = $this->findAndFlushEmail($data);
        $data["has_email"] = (int)(!empty($data["email"]));

        file_put_contents(ROOT . "testLogs/neardonelookup.json", json_encode($data, JSON_PRETTY_PRINT));
        $data["region"] = $this->guessLocation($data["media"]);


        return !$this->crud->settings->download_media ? $data :
            $this->downloadMediasAndUpdateUrl($data, false, "profile-picture", "username")[0];
    }

    public function instagramUserLookupApi(array $integration, string $username = "", ?CronWorker $worker = null): array {
        $integrationHandler = $this->crud->integrations();
        if(empty($integration)) $integration = $integrationHandler->getByUsername($username);
        if(empty($integration)) return [];

        $apiHandler = new Medias();
        if(!$apiHandler->init("instagram")) return [];

        $accessToken = $integration["item_token"];
        $accountId = $integration["item_id"];
//        $accessToken = "EAAH5L0JvBZCQBO9KKdGZAAwASRrp7817oZC4TSbodxAWw5OihbXm7hCUatmKj5MZCW6qcoCPNAGu6LjwpWfpA5m0IGvscXU9rrWvDtc5tycH1H98uPoBWUVBLwifY6JZCMy8s2JMZBjd2vCt9fC2LMfDxQrap07OsejRzoSLExG83wj9iuMWu7Lmscr2LZBJrmwn2O2YDzUUoHq7wmN59ebJYaoqje8wdcQLQvAlooQF16dDK9gHqjZB";
//        $accountId = 17841432368125063;

        $accountData = $apiHandler->accountInsight($accountId, $accessToken);
        $this->crud->multiArrayLog($accountData, "acc-insight");
        if(empty($accountData)) return [];
        $accountData["business_account"] = 1;
        $this->crud->multiArrayLog([$accountId, $accessToken], "stuff-1");
        $accountData["media"] = $apiHandler->mediaDiscovery($accountId, $accessToken);
        $accountData = $this->setLibraryData($accountData);
        $accountData["email"] = $this->findAndFlushEmail($accountData);
        $accountData["has_email"] = (int)(!empty($accountData["email"]));

        file_put_contents(ROOT . "testLogs/neardonelookup.json", json_encode($accountData, JSON_PRETTY_PRINT));
        $accountData["region"] = $this->guessLocation($accountData["media"]);
        return $accountData;
    }




    public function userStorySearch(string $username, string|int $igId = 0, ?Scraper $scraper = null): array {
        if(is_null($scraper)) {
            $scraper = $this->crud->scraper();
            $scraper->setCookieManager($this->crud)->cookieSetDefault()->cookieAddToHeader();
        }

        if($igId === 0) {
            $userLookup = $this->instagramLookupUser($username, $scraper);
            if(!is_array($userLookup) || !array_key_exists("ig_id", $userLookup)) return $userLookup;
            if($userLookup["private_account"]) return HandlerErrors::SCRAPER_STORY_ERROR;
            $igId = $userLookup["ig_id"];
        }

        $i = 0;
        $reelsData = [];
        while($i <= 10) {
            $i++;
            $reelsData = $scraper->lookupStories($igId);
            file_put_contents(TESTLOGS . "storylookup.json", json_encode($reelsData, JSON_PRETTY_PRINT));
            if(is_array($reelsData) && array_key_exists("reels_media", $reelsData)) break;
            if(!$scraper->isUnusedCookies()) break;
            $scraper->cookieSet()->cookieAddToHeader(); //Set new random cookie
            $scraper->cookieUsageIncrement(false);
        }
        if(is_null($reelsData)) return HandlerErrors::SCRAPER_ERROR;
        if(array_key_exists("error", $reelsData)) return $reelsData;

        file_put_contents(ROOT . "testLogs/story-raw.json", json_encode($reelsData, JSON_PRETTY_PRINT));

        $scraper->cookieUsageIncrement();
        $reelsData = $reelsData["reels_media"];
        if(empty($reelsData)) return HandlerErrors::SCRAPER_STORY_ERROR;

        $edgeData = $this->getEdgeData($reelsData, ScraperNestedLists::STORY_BASE_DATA);
        file_put_contents(ROOT . "testLogs/story-nearly.json", json_encode($edgeData, JSON_PRETTY_PRINT));
        $edgeData["media"] = $this->storyExtractMedias($edgeData["media"]);
        file_put_contents(ROOT . "testLogs/story.json", json_encode($edgeData, JSON_PRETTY_PRINT));

        return $edgeData;
    }










    public function hashtagExplore(string $hashtag, ?Scraper $scraper = null): array {
        if(is_null($scraper)) {
            $scraper = $this->crud->scraper();
            $scraper->setCookieManager($this->crud)->cookieSetDefault()->cookieAddToHeader();
        }

        $i = 0;
        $tagData = [];
        while($i <= 5) {
            $i++;
            $tagData = $scraper->exploreHashtag($hashtag);
            if(is_array($tagData) && array_key_exists("data", $tagData)) break;
            if(!$scraper->isUnusedCookies()) break;
            $scraper->cookieSet()->cookieAddToHeader(); //Set new random cookie
            $scraper->cookieUsageIncrement(false);
        }
        if(is_null($tagData)) return HandlerErrors::SCRAPER_ERROR;
        if(array_key_exists("error", $tagData)) return $tagData;
        file_put_contents(TESTLOGS . "tagsstuff.json", json_encode($tagData, JSON_PRETTY_PRINT));

        $scraper->cookieUsageIncrement();
        $tagData = $tagData["data"];

        $edgeData = $this->getEdgeData($tagData, ScraperNestedLists::HASHTAG_EXPLORE);
        $edgeData["media"] = $this->hashtagExplorationExtractMedias($edgeData["sections"]);
        unset($edgeData["sections"]);
        file_put_contents(TESTLOGS . "tags-trimmed.json", json_encode($edgeData, JSON_PRETTY_PRINT));

        return $edgeData;
    }










    public function hashtagSearch(array $userData, ?Scraper $scraper = null): array {
        if(!array_key_exists("username", $userData)) return [];
        $username = $userData["username"];

        if(is_null($scraper)) {
            $scraper = $this->crud->scraper();
            $scraper->setCookieManager($this->crud)->cookieSetDefault()->cookieAddToHeader();
        }

        $i = 0;
        $tagData = [];
        while($i <= 2) {
            $i++;
            $tagData = $scraper->hashtagSearch($username);
            if(is_array($tagData) && array_key_exists("hashtags", $tagData)) break;
            if(!$scraper->isUnusedCookies()) break;

            $scraper->cookieUsageIncrement(false);
            $scraper->cookieSet()->cookieAddToHeader(); //Set new random cookie
        }

        if(is_array($tagData) && array_key_exists("hashtags", $tagData) && !empty($tagData["hashtags"])) return $this->trimHashtagLookup($tagData, 10);
        $category = array_key_exists("category_name", $userData) ? strtolower($userData["category_name"]) : "";

        /* use category to pull hashtags */
        if(!empty($category)) {
            foreach (["company"] as $replaceWord) $category = str_replace($replaceWord, "", $category);
            $category = str_replace(" ", "", $category);

            $i = 0;
            while($i <= 2) {
                $i++;
                $tagData = $scraper->hashtagSearch($category);
                if(is_array($tagData) && array_key_exists("hashtags", $tagData)) break;
                if(!$scraper->isUnusedCookies()) break;

                $scraper->cookieUsageIncrement(false);
                $scraper->cookieSet()->cookieAddToHeader(); //Set new random cookie
            }
            if(is_array($tagData) && array_key_exists("hashtags", $tagData) && !empty($tagData["hashtags"])) return $this->trimHashtagLookup($tagData, 10);
        }

        return [];
    }







    public function getRemainingMedia(
        array $medias, string|int $followersCount, string|int $mediaCount, array $cursor,
        string|int $igId, int $minScrolls = 0, bool $forceMinimumTime = true, ?Scraper &$scraper = null
    ): array {

        if(empty($medias) || empty($igId)) return $medias;

        $this->minimumTimestamp = strtotime(date("Y-m-d H:i:s", strtotime("-" . self::MIN_DAYS_MEDIA . " days")));
        $currentOldestTimestamp = $this->mediaGetOldestTimestamp($medias);
        $cursorHasNext = $this->hasNext($cursor);
        $nextHash = $this->getNextHash($cursor);

        if(!$cursorHasNext || empty($nextHash)) return $medias;
        if($minScrolls === 0 && (($currentOldestTimestamp <= $this->minimumTimestamp) || !$forceMinimumTime)) return $medias;

        if(is_null($scraper)) {
            $scraper = $this->crud->scraper();
            $scraper->setCookieManager($this->crud)->cookieSetDefault()->cookieAddToHeader();
        }


        $counter = 0;
        while ($cursorHasNext && !empty($nextHash) && $counter < 10) {
            $counter++;
            $response = $scraper->queryUserTimelineMedia($nextHash, $igId);
            if(empty($response) || !is_array($response) || !array_key_exists("edges", $response)) break;

            $edges = $response["edges"];
            if(empty($edges)) break;
            $newMedias = $this->extractMediaData($edges, false, true, (int)$followersCount, (int)$mediaCount);
            if(empty($newMedias)) break;

            $cursor = $this->crud->nestedArray($response, ["page_info"], []);
            $medias = array_merge($medias, $newMedias);


            $cursorHasNext = $this->hasNext($cursor);
            $nextHash = $this->getNextHash($cursor);
            $currentOldestTimestamp = $this->mediaGetOldestTimestamp($newMedias);

            if($counter >= $minScrolls && !$forceMinimumTime) break;
            if(($counter >= $minScrolls) && ($currentOldestTimestamp <= $this->minimumTimestamp)) break;

        }


        return $medias;
    }







    public function userTaggedPage(string|int $igId, ?Scraper &$scraper = null): array {
        if(empty($igId)) return [];
        if(is_null($scraper)) {
            $scraper = new Scraper($this->crud);
            $scraper->setCookieManager($this->crud)->cookieSetDefault()->cookieAddToHeader();
        }

        $i = $mentionCount = 0;
        $data = [];
        $taggedPage = null;

        while($i <= 10) {
            $i++;
            $taggedPage = $scraper->getUserTaggedPage($igId);
            file_put_contents(ROOT . "testLogs/tagpage.json", json_encode($taggedPage, JSON_PRETTY_PRINT));
            $mentionCount = $this->crud->nestedArray($taggedPage, array("count"));
            if(!is_null($taggedPage) && !is_null($mentionCount)) break;
            if(!$scraper->isUnusedCookies()) break;

            $scraper->cookieUsageIncrement(false);
            $scraper->cookieSet()->cookieAddToHeader(); //Set new random cookie
        }


        if(!empty($taggedPage) && $mentionCount > 0 && $this->crud->nestedArray($taggedPage, ["status"]) !== "fail") {
            $taggedData = $this->getEdgeData($taggedPage, ScraperNestedLists::TAGGED_PAGE);
            file_put_contents(ROOT . "testLogs/tagpage2.json", json_encode($taggedData, JSON_PRETTY_PRINT));

            $data = $this->taggedPageMediaData($taggedData["edges"]);
        }

        file_put_contents(ROOT . "testLogs/mentions.json", json_encode($data, JSON_PRETTY_PRINT));
        return $data;
    }














}