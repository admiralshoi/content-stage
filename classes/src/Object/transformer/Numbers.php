<?php
namespace classes\src\Object\transformer;

use JetBrains\PhpStorm\ArrayShape;

class Numbers {
    static public function shortenNumber($number,$shortM = true, $shortK = true, $includeCharSeparate = false): array|int|string {
        $number = (int)$number;
        $mil = 1000000; $kilo = 1000; $m="M"; $k="K"; $response = "";
        if(($number >= $mil || $number <= -$mil)  && $shortM) {
            $x = round($number / $mil,2);
            $response = $includeCharSeparate ? array("number" => $x,"char" => $m) : $x.$m;
        } else if(($number >= $kilo || $number <= -$kilo) && $shortK) {
            $x = round($number / $kilo,2);
            $response = $includeCharSeparate ? array("number" => $x,"char" => $k) : $x.$k;
        } else
            $response = $number;
        return $response;
    }


    static public function timeAgo(string|int $timestamp, bool $countdown = false, bool $pluralS = true, array $prefixes = array()): string {
        if($countdown) {
            $timeNow = (int)$timestamp;
            $timestamp = time();
        }
        else {
            $timestamp = (int)$timestamp;
            $timeNow = time();
        }

        $standardPrefix = array(
            "year" => "year",
            "month" => "month",
            "day" => "day",
            "hour" => "hour",
            "minute" => "minute",
            "ago" => "ago",
        );
        foreach ($standardPrefix as $key => $name) {
            if(array_key_exists($key, $prefixes)) continue;
            $prefixes[$key] = $name;
        }

        $difference = round(($timeNow-$timestamp));
        $hoursFloor = floor($difference / 3600);

        if((24 * 365) < $hoursFloor) { //Year in hours
            $count = floor($hoursFloor / (24 * 365));
            $response = $count . ($pluralS ? " " . Titles::pluralS($count,$prefixes["year"]) : $prefixes["year"]) . " " . $prefixes["ago"];
        }
        else if((24 * 30 * 3) < $hoursFloor) { // 3 months in hours (we display days if not greater than 3 months)
            $count = floor($hoursFloor / (24 * 30)); // Display in unit of 1 month (not 3 months)
            $response = $count . ($pluralS ? " " . Titles::pluralS($count,$prefixes["month"]) : $prefixes["month"]) . " " . $prefixes["ago"];
        }
        else if(24 < $hoursFloor) { // day in hours
            $count = floor($hoursFloor / 24);
            $response = $count . ($pluralS ? " " . Titles::pluralS($count,$prefixes["day"]) : $prefixes["day"]) . " " . $prefixes["ago"];
        }
        else  {
            if(!($hoursFloor > 0)) {
                $count = round($difference / 60);
                $response = $count . ($pluralS ? " " . Titles::pluralS($count,$prefixes["minute"]) : $prefixes["minute"]) . " " . $prefixes["ago"];
            }
            else $response = $hoursFloor . ($pluralS ? " " . Titles::pluralS($hoursFloor,$prefixes["hour"]) : $prefixes["hour"]) . " " . $prefixes["ago"]; // Hours
        }

        return $response;
    }


    static public function timeDifferenceInUnits(int $currentTime, int $compareTime, string $unit = "daily"): int|float|null {

        $timeDifference = $currentTime - $compareTime;
        $dayTimeDefined = (3600 * 24);
        switch ($unit) {
            default: return null;
            case "daily": return floor($timeDifference / $dayTimeDefined);
            case "weekly": return floor($timeDifference / ($dayTimeDefined * 7));
            case "monthly": return floor($timeDifference / ($dayTimeDefined * 365.25 / 12));
            case "yearly": return floor($timeDifference / ($dayTimeDefined * 365.25));
        }
    }




    static public function countdownInUnits(string|int $targetTime): \DateInterval {
        $targetTime = (int)$targetTime;
        $targetDate = date('m/d/Y H:i:s', $targetTime);


        $dt = new \DateTime($targetDate);
        return $dt->diff(new \DateTime());
    }






}