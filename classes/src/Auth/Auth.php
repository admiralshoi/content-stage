<?php

namespace classes\src\Auth;


use classes\src\AbstractCrudObject;
use classes\src\Enum\AppSettings;

class Auth extends AbstractAuth {

    private ?FacebookAuth $media;
    private ?string $currentAuth = null;
    private const CLASSES = array(
        "facebook" => "classes\src\Auth\FacebookAuth",
    );


    function __construct(AbstractCrudObject $crud) {parent::__construct($crud);}

    public function init(string $authType): bool {
        if(empty($authType)) return false;
        if(!in_array($authType, parent::AuthTypes)) return false;

        $className = self::CLASSES[$authType];
        if(!class_exists($className)) return false;

        $this->media = new $className($this->crud);
        $this->currentAuth = $authType;
        return true;
    }

    public function isInit(string $authType = null): bool {
        if(empty($authType)) return !empty($this->currentAuth);
        return $this->currentAuth === $authType;
    }

    public function oAuthLink(string $redirect_uri = ""): string {
        $baseUrl = "https://www.facebook.com/".AppSettings::FB_GRAPH_VERSION."/dialog/oauth";
        $permissions = $this->crud->isCreator() ? AppSettings::CREATOR_PERMISSIONS : AppSettings::PERMISSIONS;
        $query = array(
            "client_id" => AppSettings::APP_ID,
            "redirect_uri" => !empty($redirect_uri) ? $redirect_uri : AppSettings::FB_REDIRECT_URL,
            "state" => AppSettings::AUTH_STATE_PHRASE,
            "response_type" => "code",
            "scope" => implode(",", $permissions)
        );

        return $baseUrl . "?" . http_build_query($query);
    }

    public function getAccessCode(array $requestData = array()): ?string {
        if(isset($_GET["code"])) return $_GET["code"];
        return array_key_exists("code", $requestData) ? $requestData["code"] : "";
    }

    public  function initiateIntegration(array $args): ?array { return $this->media->initiateIntegration($args); }
    public function checkPermissions($token): bool { return $this->media->checkPermissions($token); }
    public  function exchangeToLongLivedToken(string $code, int|string $expiresIn = 0, string $redirect_uri = ""): ?array {
        return $this->media->exchangeToLongLivedToken($code, $expiresIn, $redirect_uri);
    }


}