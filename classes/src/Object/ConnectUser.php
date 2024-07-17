<?php
namespace classes\src\Object;
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

use classes\src\AbstractCrudObject;

class ConnectUser  {
    public AbstractCrudObject $crud;
    private array $session_keys = array("full_name","uid","access_level","email", "lang");
    const EXPECTED_FIELDS = array("email","password");
    protected array $givenFields = array();
    public ?array $error = null;
    public bool $guest = false;


    function __construct(AbstractCrudObject $crud, array $params = array()){
        $this->crud = $crud;
        if(!empty($params)) $this->setFields($params);
    }

    public function setSessionLoggedInd(string|int $userId = 0): void {
        $userHandler = $this->crud->user();
        $userHandler->disableDepthCheck();
    }



    private function setFields($params): void {
        if(empty($params)) return;

        foreach (self::EXPECTED_FIELDS as $field) {
            if(!array_key_exists($field,$params)) {
                $this->error = array("error" => "No $field was given");
                return;
            }

            $this->givenFields[$field] = $params[$field];
        }
    }


    public function execute( array $params = array()): bool{
        if(!empty($params)) $this->setFields($params);
        if(!empty($this->error)) return false;

        foreach ($this->givenFields as $param=>$value) if(empty($value)) $this->error = array("error" => "'".ucfirst($param)."' cannot be empty. ");
        if(!empty($this->error)) return false;

        $userHandler = $this->crud->user();
        $userHandler->disableDepthCheck();

        $password = $this->crud->passwordHashing($this->givenFields["password"]);
        $username = $this->givenFields["email"];

        if($username === "guest" && !$this->guest) {
            $this->error = array("error" => "No user with these credentials");
            return false;
        }

        $user = $userHandler->getByX(array("email" => $username, "password" => $password));
        if(empty($user)) $user = $userHandler->getByX(array("username" => $username, "password" => $password));


        if(empty($user)) {
            $userHandler->enableDeactivatedSearch();
            $user = $userHandler->getByX(array("email" => $username, "password" => $password));
            if(empty($user)) $user = $userHandler->getByX(array("username" => $username, "password" => $password));

            if(!empty($user)) $this->error = array("error" => "Your account has been suspended. Please check your email for details");
            else $this->error = array("error" => "No user with these credentials");
            return false;
        }

        $user = $user[0];
        $this->session_keys[] = "logged_in";
        $this->setSessions($user);
        if($this->guest) $_SESSION["guest"] = true;


        $connectionLog = ROOT . "users/" . $user["uid"] .  "/" . USER_LOGS . USER_CONNECTION_LOG;
        file_put_contents($connectionLog, json_encode(array(
                "timestamp" => time(), "date" => date("F d Y, H:i:s")
            )) . PHP_EOL, 8);

        return true;
    }

    function setSessions($user): void {
        foreach ($this->session_keys as $session_key) {
            if (array_key_exists($session_key, $user)) $_SESSION[$session_key] = $user[$session_key];
            else $_SESSION[$session_key] = true;
        }
    }
}