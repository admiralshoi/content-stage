<?php
namespace classes\src\Object\transformer;


class Titles {
    public static function prettify($input){
        if(!in_array(gettype($input),array("string","array")))
            return $input;

        if(is_array($input)) {
            $input = array_map(function ($string) {
                return self::clean($string);
            },$input);
        }
        else
            $input = self::clean($input);

        return $input;
    }

    public static function prettifiedUppercase(string $str): string {
        if(empty($str)) return $str;
        if(!str_contains($str," ")) return ucfirst($str);

        return trim(array_reduce((explode(" ",$str)), function ($currentStr,$word) {
            return $currentStr . " " . ucfirst($word);
        }));
    }

    public static function cleanUcAll(string $str): string {
        if(empty($str)) return $str;
        $str = self::clean($str);
        return self::prettifiedUppercase($str);
    }


//-------------------------------------------------------------------------------------------------------------------------------------------------------

    public static function truncateStr(string $str, int $n, bool $endOfString = true): string {
        return (strlen($str) > $n) ? ($endOfString ? substr($str,0,($n - 3)) . "..." : "..." . substr($str, (strlen($str) - $n + 3))) : $str;
    }

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

    public static function pluralS(int|float $n, string $str): string { return abs($n) ? $str . "s" : $str; }

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------


    public static function clean(string $input): string{
        return str_replace("_"," ",ucfirst($input));
    }
}