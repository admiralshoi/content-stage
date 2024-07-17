<?php

namespace classes\src\Media;
use classes\src\AbstractCrudObject;
use classes\src\Media\Utilities\Util;
use JetBrains\PhpStorm\ArrayShape;
use classes\src\Enum\AppSettings;
use classes\src\Http\Request;

abstract class AbstractMedias {

    protected ?Request $httpHandler = null;
    protected const AuthTypes = array("instagram", "facebook");
    protected static ?Util $util = null;

    abstract public function getAccounts(string|array $item): array;
    abstract public function getInsights(string $accessToken, string|int $accountId, array $metrics, string $period, array $timeInterval = array()): array;

    function __construct() {
        self::$util = new Util();
        $this->httpHandler = new Request();
    }

    public function downloadMedia($url,$dest,$content = null,$file_name = ""): bool|string {
        // Use basename() function to return the base name of file
        $file_name = empty($file_name) ? basename($url) : $file_name;
        $fileInfo = self::filenameInfo($file_name);

        $path = $dest.$fileInfo["fn"];

        if($content !== null && $content !== false) {
            try {
                $size = file_put_contents($path,$content);
                return basename($path);
            } catch (\Exception $e) {
                return false;
            }
        } else {
            // Gets the file from url and saves the file by using its base name
            try {
                $size = file_put_contents( $path,file_get_contents($url));
                return basename($path);
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    #[ArrayShape(["ext" => "array|string|string[]", "fn" => "string", "fnid" => "array|string|string[]"])]
    public  function filenameInfo($pathToFile): array {
        $filename = basename($pathToFile);

        if(strpos($filename,"?") !== false)
            $filename = (explode("?",$filename))[0];
        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
        if($file_ext == false || $file_ext === "image")
            $file_ext = "jpeg";
        if(strpos($filename,"~") !== false)
            $filename = (explode("~",$filename))[0].".".$file_ext;
        $name = str_replace(".".$file_ext,"",$filename);

        return array(
            "ext" => $file_ext,
            "fn" =>  $filename,
            "fnid" => $name
        );
    }



    public function resolveHasNextWithAfter(array &$query, array $graphResponse): bool {
        if(!empty(self::$util::nestedArray($graphResponse, array("paging", "next")))) {
            $query["after"] = self::$util::nestedArray($graphResponse, array("paging", "cursors", "after"));
            return true;
        }

        return false;
    }

    public function resolveHasNextNoAfter(&$queryUrl, array $graphResponse): bool {
        $afterUrl = self::$util::nestedArray($graphResponse, array("paging", "next"));
        if(!empty($afterUrl)) {
            $queryUrl = $afterUrl;
            return true;
        }

        return false;
    }



    public function subscribeWebhook($pageToken): bool {
        $baseUrl = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . AppSettings::FB_SUBSCRIBE_WEBHOOK_ENDPOINT;
        $subscription = (new AbstractCrudObject())->isCreator() ? AppSettings::CREATOR_SUBSCRIPTION_FIELDS : AppSettings::SUBSCRIPTION_FIELDS;
        if(empty($subscription)) return true;
        $query = array(
                "subscribed_fields" => implode(",", $subscription),
            "access_token" => $pageToken,
        );

        $queryUrl = $baseUrl . "?" . http_build_query($query);
        $this->httpHandler->send($queryUrl, "POST");
        $graphResponse = self::$util::ensureValidObjectResponse($this->httpHandler->getResponse());

        file_put_contents(TESTLOGS . "subs.json", json_encode($graphResponse, JSON_PRETTY_PRINT));

        if(empty($graphResponse)) return false;
        return self::$util::nestedArray($graphResponse,array("success"), false);
    }



}