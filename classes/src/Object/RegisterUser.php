<?php
namespace classes\src\Object;
use classes\src\AbstractCrudObject;

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

class RegisterUser {

    public AbstractCrudObject $crud;
    static array $EXPECTED_FIELDS = array("full_name","email","password", "password_repeat", "access_level");
    protected array $givenFields = array();
    public ?array $error = null;
    private bool $thirdPartyCreation = false;
    private const PWDDEF = "123456";

    private int $requestingUsersAccessLevel = 0;
    private int $requestingUsersUid = 0;

    function __construct(AbstractCrudObject $crud, array $params = array()) {
        $this->crud = $crud;
        $this->setFields($params);

        if(isset($_SESSION["access_level"])) $this->requestingUsersAccessLevel = $_SESSION["access_level"];
        if(isset($_SESSION["uid"])) $this->requestingUsersUid = $_SESSION["uid"];
    }

    private function setFields($params): void {
        if(empty($params)) return;
        if($this->thirdPartyCreation) self::$EXPECTED_FIELDS[] = "access_level";

        foreach (self::$EXPECTED_FIELDS as $field) {
            if(!array_key_exists($field,$params)) {
                if($this->thirdPartyCreation && str_contains($field, "password")) continue;
                $this->error = array("error" => "No $field was given");
                return;
            }

            $this->givenFields[$field] = $params[$field];
        }

        if($this->thirdPartyCreation) $this->givenFields["password"] = $this->givenFields["password_repeat"] = self::PWDDEF;
        if(in_array("email", self::$EXPECTED_FIELDS) && !array_key_exists("username", $this->givenFields))
            $this->givenFields["username"] = array_key_exists("username", $params) ? $params["username"] : $this->givenFields["email"];
    }

    public function thirdPartyCreation():  void { $this->thirdPartyCreation = true; }

    public function execute(array $params = array()): bool {
        if(!empty($params)) $this->setFields($params);
        if(!empty($this->error)) return false;


        $this->validate();
        if(!empty($this->error)) return false;


        if( //Assumes hat accss_level is always present in thirdparty creation
            !$this->thirdPartyCreation &&
            !(array_key_exists("access_level", $this->givenFields) && in_array($this->givenFields["access_level"], [1,2]))

        ) $this->givenFields["access_level"] = 1;



        $this->givenFields["registration_complete"] = (int)((int)$this->givenFields["access_level"] !== 1);

        $this->givenFields["created_by"] = $this->requestingUsersUid;
        $this->givenFields["lang"] = "english";
//        return json_encode(array("error" => "Due to missing SocialMedia integrations, we're currently unable to create your account. Please try again later"));



        $userHandler = $this->crud->user();
        $userHandler->disableDepthCheck();

        if(!empty($userHandler->getByX(array("email" => $this->givenFields["email"])))) {
            $this->error = array("error" => "This email is already in use");
            return false;
        }
        if(!empty($userHandler->getByX(array("username" => $this->givenFields["username"])))) {
            $this->error = array("error" => "This username is already in use");
            return false;
        }

        while(true){
            $uid = crc32($this->givenFields["full_name"] . "_" . $this->givenFields["email"]) . rand(2,10000);
            if(!$userHandler->exists($uid)) break;
        }

        $userDir = "users/$uid/";
        if(is_dir(ROOT . $userDir)) {
            $this->error = array("error" => "User-Directory already exists!");
            return false;
        }

        $params = array(
            "username" => trim($this->givenFields["username"]), "password" =>  $this->crud->passwordHashing($this->givenFields["password"]),
            "full_name" => trim($this->givenFields["full_name"]),"created_by" => $this->givenFields["created_by"],
            "access_level" => $this->givenFields["access_level"], "created_at" => time(),
            "email" => trim($this->givenFields["email"]), "directory" => $userDir,
            "uid" => $uid, "lang" => $this->givenFields["lang"], "registration_complete" => $this->givenFields["registration_complete"]
        );

        $insert = $this->crud->create( "user", array_keys($params), $params );
        if($insert === false) {
            $this->error = array("error" => "Failed to create user");
            return false;
        }


        mkdir(ROOT . $userDir);
        mkdir(ROOT . $userDir . "images");
        mkdir(ROOT . $userDir . "messages");
        mkdir(ROOT . $userDir . "logs");
        if((in_array($this->givenFields["access_level"], array(1, 2)))) mkdir(ROOT . $userDir . "integrations");
        if((int)$this->givenFields["access_level"] === 1) mkdir(ROOT . $userDir . "packages/");

        $baseFileContent = array(
            "name" => $this->givenFields["full_name"],
            "picture" => "images/" . USER_NO_PB,
            "has_profile_picture" => false,
            "change_log" => array()
        );

        file_put_contents(ROOT . $userDir . USER_BASE_FILE, json_encode($baseFileContent,JSON_PRETTY_PRINT));


//        $this->crud->notificationHandler()->welcomeEmail(
//            $this->crud,
//            array(
//                "uid" => $uid,
//            )
//        );

        if(!$this->thirdPartyCreation) {
            $signin = new ConnectUser($this->crud, array("email" => $this->givenFields["email"], "password" => $this->givenFields["password"]));
            $signin->execute();
        }

        return true;
    }

    protected function validate(): void {
        foreach ($this->givenFields as $fieldName => $fieldValue) {
            if($fieldName === "email" && !filter_var($fieldValue,FILTER_VALIDATE_EMAIL)) $this->error = array("error" => "$fieldValue is not a legitimate email");
            elseif(in_array($fieldName, ["full_name", "username"]) && (strlen($fieldValue) <= 1 && strlen($fieldValue) >= 50)) $this->error = array("error" => "$fieldValue must be between 2 and 49 characters");
            elseif($fieldName === "password" && $fieldValue !== $this->givenFields["password_repeat"]) $this->error = array("error" => "The passwords do not match");
            elseif($fieldName === "access_level" && ((int)$fieldValue > 2 && $this->requestingUsersAccessLevel < 8)) $this->error = array("error" => "Cannot create user with access level $fieldValue");
        }
    }

}