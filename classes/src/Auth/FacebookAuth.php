<?php
namespace classes\src\Auth;

use classes\src\AbstractCrudObject;
use classes\src\Enum\AppSettings;
use classes\src\Http\Request;

class FacebookAuth extends AbstractAuth {

    protected ?Request $httpHandler = null;
    function __construct(AbstractCrudObject $crud) { parent::__construct($crud); $this->httpHandler = new Request(); }



    public function initiateIntegration(array $args): array {
        foreach (array("token", "expiresIn") as $field)
            if(!array_key_exists($field, $args) || empty($args[$field]))
                return array("error" => "Given fields are not sufficient for this request. Expected $field");

        $expiresIn = $args["expiresIn"];
        $initialToken = $args["token"];

        if(!$this->checkPermissions($initialToken)) return array("error" => "You did not grant all necessary permissions");
        $exchangeTokenInfo = $this->exchangeToLongLivedToken($initialToken,$expiresIn);

        return empty($exchangeTokenInfo) ? array("error" => "Failed to fetch LongLivedToken") : $exchangeTokenInfo;
    }


    /**
     * @param string $code
     * @param int|string $expiresIn
     * @return array|null [Return format: ["access_token" => "string", "expires_at" => "int"]]
     *
     */
    public function exchangeToLongLivedToken(string $code, int|string $expiresIn = 0, string $redirect_uri = ""): ?array {
        //If you logged in before, sometimes Facebook will return long-lived token first time
        if((string)$expiresIn === "never" || (is_int($expiresIn) && $expiresIn > (3600 * 24)))
            return array("access_token" => $code, "expires_at" => $expiresIn);

        $url = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . AppSettings::AUTH_GRAPH_ENDPOINTS["longLivedToken"];
        $query = array(
            "client_id" => AppSettings::APP_ID,
            "redirect_uri" => empty($redirect_uri) ? AppSettings::FB_REDIRECT_URL : $redirect_uri,
            "client_secret" => AppSettings::APP_SECRET,
            "code" => $code
        );

//        echo $url . "?" . http_build_query($query); return array();

        $this->httpHandler->send($url . "?" . http_build_query($query));
        $result = $this->httpHandler->getResponse();

        if(array_key_exists("error", $result)) return $result;
        $result = !array_key_exists("access_token", $result) ? $result[0] : $result;

        $tokenInfo = self::$util::ensureValidObjectResponse($result);
        if(empty($tokenInfo)) return null;

        $expiresAt = !array_key_exists("expires_in",$tokenInfo) ? "never" : $tokenInfo["expires_in"];
        $expiresAt = $expiresAt === "never" ? $expiresAt : (time() + (int)$expiresAt);
        $longLivedToken = $tokenInfo["access_token"];

        return array("access_token" => $longLivedToken, "expires_at" => $expiresAt);
    }


    /**
     * @param $token
     * @return bool
     */
    public function checkPermissions($token): bool {
        $url = AppSettings::FB_GRAPH_BASE_URL . AppSettings::FB_GRAPH_VERSION . "/" . AppSettings::AUTH_GRAPH_ENDPOINTS["user"];
        $permissionList = $this->crud->isCreator() ? AppSettings::CREATOR_PERMISSIONS : AppSettings::PERMISSIONS;
        $query = array(
            "fields" => "permissions",
            "access_token" => $token,
        );

        $this->httpHandler->send($url . "?" . http_build_query($query));
        $graphResponse = $this->httpHandler->getResponse();

        if (!(is_array($graphResponse) && array_key_exists("permissions", $graphResponse) &&
            array_key_exists("data", $graphResponse["permissions"]) && !empty($graphResponse["permissions"]["data"]))) return false;

        $data = array_filter($graphResponse["permissions"]["data"], function ($item) {
            return array_key_exists("status", $item) && array_key_exists("permission", $item) && $item["status"] === "granted";
        });
        if (empty($data)) return false;

        $grantedPermissions = array_map(function ($item) {
            return $item["permission"];
        }, $data);

        return empty(array_diff($permissionList, $grantedPermissions));
    }
}