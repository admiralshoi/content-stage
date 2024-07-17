<?php
namespace classes\src\Object\transformer;


class URL {

    public static function addParam(string $uri, array $param,$exclusive = false): string {

        if($exclusive) return $uri . "?" . http_build_query($param);

        $urlParsed = parse_url($uri);
        if(!is_array($urlParsed))
            return $uri;

        if(array_key_exists("query",$urlParsed) && !empty($urlParsed["query"]) && !empty($param)) {
            $splitQuery = $newQ = explode("&",$urlParsed["query"]);
            if(count($splitQuery) > 0) {
                foreach ($splitQuery as $n => $q) {
                    if(strpos($q,"=") !== false) {
                        $splitQ = explode("=",$q);
                        if(array_key_exists($splitQ[0],$param)) {
                            unset($newQ[$n]);
                        }
                    }
                }
            }
            $urlParsed["query"] = count($newQ) === 0 ? "" : implode("&",$newQ);
        }

        $newParams = empty($param) ? "" : http_build_query($param);
        $buildResponse = "";
        $buildResponse .= array_key_exists("scheme",$urlParsed) ? $urlParsed['scheme']."://" : "";
        $buildResponse .= array_key_exists("host",$urlParsed) ? $urlParsed['host']."/" : "";
        $buildResponse .= array_key_exists("path",$urlParsed) ? $urlParsed['path'] : "";
        $buildResponse .= (!array_key_exists("query",$urlParsed) || empty($urlParsed["query"])) ? "?$newParams" : "?".$urlParsed["query"]."&$newParams";


        return str_replace("&&","&",$buildResponse);
    }

}