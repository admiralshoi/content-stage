<?php

namespace classes\src\Object;

require PARSER;

use classes\src\Enum\HandlerErrors;
use classes\src\AbstractCrudObject;
use Controller\src\Tools\Utilities;

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

class Scraper extends \classes\src\Http\Request {

    public AbstractCrudObject $crud;

    function __construct(AbstractCrudObject $crud, string|array $postParams = array(), array $headers = array()) {
        parent::__construct($postParams, $headers);
        $this->crud = $crud;
    }



    public function lookupStories(string|int $igId): ?array {
        if(empty($igId)) return null;
        $requestUrl = "https://www.instagram.com/api/v1/feed/reels_media/?reel_ids=$igId";

        $this->send($requestUrl);
        $response = $this->getResponse();

        if($this->isError()) return null;
        if(empty($response)) return null;

        return $response;
    }




    public function exploreHashtag(string $keyword): ?array {
        if(empty($keyword)) return null;
        $requestUrl = "https://www.instagram.com/api/v1/tags/web_info/?tag_name=$keyword";

        $this->send($requestUrl);
        $response = $this->getResponse();
        if($this->isError()) return null;
        if(empty($response)) return null;

        return $response;
    }





    public function hashtagSearch(string $keyword): ?array {
        if(empty($keyword)) return null;
        $requestUrl = "https://www.instagram.com/web/search/topsearch/?context=hashtag&query=$keyword";

        $this->send($requestUrl);
        $response = $this->getResponse();

        if($this->isError()) return null;
        if(empty($response)) return null;

        return $response;
    }




    public function getProfilePage(string $username): ?array {
        if(empty($username)) return null;
        $profileUrl = "https://i.instagram.com/api/v1/users/web_profile_info/?username=$username";

        $this->addHeader("Referer: https://www.instagram.com/$username", "Referer");
        $this->send($profileUrl);
        $response = $this->getResponse();
//        $response = json_decode($response, true);

        if($this->isError()) return null;
        if(empty($response)) return null;

        $data = $this->crud->nestedArray($response, array("data", "user"));
        if(empty($data)) return null;

        $fullName = $this->crud->nestedArray($data, array("full_name"));
        if(is_null($fullName)) return HandlerErrors::SCRAPER_USER_UNAVAILABLE;

        return $data;
    }


    public function postPageFirstScrape(string $shortcode): ?string { //Does not require cookie
        $this->setHeaders([
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36",
            'Sec-Ch-Ua-Platform: "Windows"',
            'Sec-Ch-Ua-Platform-Version: "10.0.0"',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: none',
        ]);
        $url = filter_var($shortcode, FILTER_VALIDATE_URL) ? $shortcode : "https://www.instagram.com/p/$shortcode/";
        $this->send($url);
        $htmlResponse = $this->getResponse(false);
        file_put_contents(ROOT . "testLogs/lookuppost.html", $htmlResponse);
        return $htmlResponse;
    }



    public function getPostPageMediaId(string $shortcode, $htmlResponse = ""): ?int {
        if(empty($htmlResponse)) $htmlResponse = $this->postPageFirstScrape($shortcode);
        require_once PARSER;

        file_put_contents(TESTLOGS . "media-html.html", $htmlResponse);
        if(empty($htmlResponse)) return null;
        $dom = new \PHPHtmlParser\Dom();
        $dom->loadStr($htmlResponse);

        foreach ($dom->find("meta") as $metaTag) {
            $content = $metaTag->getAttribute("content");
            if(empty($content)) continue;

            $key = "media?id=";
            if(str_contains($content, $key)) {
                $split = explode($key, $content);
                return (int)array_pop($split);
            }

        }

        return null;
    }


    public function getPostPageThumbnail(string $shortcode, $htmlResponse = ""): ?string { //Does not require cookie
        if(empty($htmlResponse)) $htmlResponse = $this->postPageFirstScrape($shortcode);
        preg_match_all('/"thumbnailUrl":".*?"}/', $htmlResponse, $matches);

        if(empty($matches)) return null;
        $match = $matches[0];
        if(array_key_exists(0, $match)) $match = $match[0];

        $split = array_reverse(explode('"', $match));
        return empty($split) || !array_key_exists(1, $split) ? null : $split[1];
    }




    public function mediaInfo(string|int $mediaId): ?array {
        $url = "https://i.instagram.com/api/v1/media/$mediaId/info/";
        $this->send($url);
        return $this->crud->nestedArray($this->getResponse(), ["items", 0]);
    }






    public function queryUserTimelineMedia(string $afterHash, string|int $igId): ?array {
        if(empty($igId)) return null;
        $baseUrl = "https://www.instagram.com/graphql/query/";

        $query = [
            "query_hash" => "69cba40317214236af40e7efa697781d",
            "variables" => json_encode(
                [
                    "id" => $igId,
                    "first" => 12,
                    "after" => $afterHash
                ]
            )
        ];
        $targetUrl = $baseUrl . "?" . http_build_query($query);

        $this->send($targetUrl);
        $response = $this->getResponse(true);
        file_put_contents(TESTLOGS . "media-query.json", json_encode($response, JSON_PRETTY_PRINT));

        if($this->isError()) return null;
        if(empty($response)) return null;
        return $this->crud->nestedArray($response, array("data", "user", "edge_owner_to_timeline_media"));
    }







    public function getUserTaggedPage(string|int $igId): ?array {
        if(empty($igId)) return null;
        $baseUrl = "https://www.instagram.com/graphql/query/";

        $query = [
            "query_hash" => "be13233562af2d229b008d2976b998b5",
            "variables" => json_encode(
                [
                    "id" => $igId,
                    "first" => 12
                ]
            )
        ];
        $targetUrl = $baseUrl . "?" . http_build_query($query);

        $this->send($targetUrl);
        $this->getResponse(false);
        $response = $this->getResponse(true);
        file_put_contents(ROOT . "testLogs/tagpage-raw.json", json_encode($response, JSON_PRETTY_PRINT));

        if($this->isError()) return null;
        if(empty($response)) return null;


        if($this->crud->nestedArray($response, ["status"]) === "fail") return $response;


        $data = $this->crud->nestedArray($response, array("data", "user", "edge_user_to_photos_of_you"));
        if(empty($data)) return null;
        if(is_null($this->crud->nestedArray($data, array("count")))) return null;
        return $data;
    }





}