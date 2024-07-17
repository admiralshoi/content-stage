<?php

namespace classes\src\Object\objects;

use classes\src\AbstractCrudObject;
use classes\src\Enum\ExternalItems;
use classes\src\Enum\ScraperNestedLists;
use classes\src\Media\Medias;
use classes\src\Object\CronWorker;
use classes\src\Object\Scraper;
use classes\src\Object\transformer\Titles;
use JetBrains\PhpStorm\Pure;

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();


class AccountAnalytics {

    private int $requestingUsersAccessLevel = 0;
    private int $requestingUsersId = 0;
    private AbstractCrudObject $crud;


    function __construct(AbstractCrudObject $crud) {
        $this->crud = $crud;

        if(isset($_SESSION["access_level"])) $this->requestingUsersAccessLevel = $_SESSION["access_level"];
        if(isset($_SESSION["uid"])) $this->requestingUsersId = $_SESSION["uid"];
    }



    private function access(int $actionType): bool {
        return $this->crud->hasAccess("node","account_analytics",$actionType, $this->requestingUsersAccessLevel);
    }


    /*
     * Core CRUD features START
     */

    public function getByX(array $params = array(), array $fields = array(), string $customSql = ""): array {
        if(!$this->access(READ_ACTION)) return array();
        return $this->keyJsonEncoding($this->crud->retrieve("account_analytics",$params, $fields,$customSql), false);
    }
    public function create(array $params): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        if(!array_key_exists("created_at", $params)) $params["created_at"] = time();
        return $this->crud->create("account_analytics", array_keys($params), $this->keyJsonEncoding($params));
    }
    public function update(array $params, array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        return $this->crud->update("account_analytics", array_keys($params), $this->keyJsonEncoding($params), $identifier);
    }
    public function delete(array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        return $this->crud->delete("account_analytics", $identifier);
    }
    public function get(string|int $id, array $fields = array()): array {
        $item = $this->getByX(array("id" => $id), $fields);
        return array_key_exists(0, $item) ? $item[0] : $item;
    }

    /*
     * Core CRUD features END
     */


    public function getCreatorLatest(string|int $lookupId, array $fields = []): array {
        $selection = empty($fields) ? "*" : implode(",", $fields);
        $rows =  $this->getByX([],[], "SELECT $selection FROM account_analytics WHERE lookup_id = $lookupId ORDER BY created_at DESC LIMIT 1");
        return array_key_exists(0,$rows) ? $rows[0] : $rows;
    }

    public function keyJsonEncoding(array $mediaData, bool $encode = true): array {
        if(empty($mediaData)) return $mediaData;
        $keys = array("audience_city", "audience_country", "audience_gender_age");
        foreach ($keys as $key) {
            if(array_key_exists($key, $mediaData) && $encode && is_array($mediaData[$key])) $mediaData[$key] = json_encode($mediaData[$key]);
            if(array_key_exists($key, $mediaData) && !$encode && !is_array($mediaData[$key]) && !empty($mediaData[$key])) $mediaData[$key] = json_decode($mediaData[$key], true);
        }
        return $mediaData;
    }





    public function formatChartData(array $analyticsRow): array {
        if(empty($analyticsRow)) return [];
        $result = [
            "reach" => array_key_exists("reach_count", $analyticsRow) ? (int)$analyticsRow["reach_count"] : 0,
            "impressions" => array_key_exists("impressions", $analyticsRow) ? (int)$analyticsRow["impressions"] : 0,
            "online_followers" => array_key_exists("online_followers", $analyticsRow) ? (int)$analyticsRow["online_followers"] : 0,
        ];
        $ageGender = array_key_exists("audience_gender_age", $analyticsRow) ? $analyticsRow["audience_gender_age"] : [];
        $countries = array_key_exists("audience_country", $analyticsRow) ? $analyticsRow["audience_country"] : [];
        $cities = array_key_exists("audience_city", $analyticsRow) ? $analyticsRow["audience_city"] : [];

        if(!empty($countries)) {
            $result["countries"] = [
                "labels" => array_keys($countries),
                "series" => array_values($countries),
            ];
        }

        if(!empty($ageGender)) {
            $genderCount = ["female" => 0, "male" => 0, "unknown" => 0];
            $genderAgeRange = ["female" => [], "male" => [], "unknown" => []];
            $ageRange = [];
            foreach ($ageGender as $key => $value) {
                $split = explode(".", $key);
                $age = trim(array_pop($split));
                if(str_contains(strtolower($key), "f.")) $resultKey = "female";
                elseif(str_contains(strtolower($key), "m.")) $resultKey = "male";
                elseif(str_contains(strtolower($key), "u.")) $resultKey = "unknown";
                else continue;

                $genderCount[$resultKey] += (int)$value;
                $genderAgeRange[$resultKey][$age] = (int)$value;

                if(!array_key_exists($age, $ageRange)) $ageRange[$age] = 0;
                $ageRange[$age] += (int)$value;
            }

            $result["gender_count"] = [
                "labels" => array_keys($genderCount),
                "series" => array_values($genderCount),
            ];
            $result["gender_age_range"] = [];
            foreach ($genderAgeRange as $gender => $list) {
                $result["gender_age_range"][$gender] = [
                    "labels" => array_keys($list),
                    "series" => array_values($list),
                ];
            }
            $result["gender_age_range"]["total"] = [
                "labels" => array_keys($ageRange),
                "series" => array_values($ageRange),
            ];
        }


        if(!empty($cities)) {
            $list = [];
            foreach ($cities as $key => $value) {
                $split = explode(",", $key);
                $city = trim(array_shift($split));
                $list[$city] = (int)$value;
            }
            $result["cities"] = $list;
        }

        if(array_key_exists("cities", $result)) {
            arsort($result["cities"]);
            $result["cities"] = array_chunk($result["cities"], 8, true)[0];
            $result["cities"] = [
                "labels" => array_keys($result["cities"]),
                "series" => array_values($result["cities"]),
            ];
        }

        return $result;
    }





}