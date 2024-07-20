<?php

namespace classes\src\Object\objects;
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

use classes\src\AbstractCrudObject;
use classes\src\Object\transformer\Titles;
use classes\src\Object\transformer\URL;
use JetBrains\PhpStorm\ArrayShape;

class User {
    private string|int $userId = 0;
    private bool $validUser = false;
    private int $requestingUsersAccessLevel = 0;
    private int $requestingUsersId = 0;
    private bool $disabledDepthCheck = false;
    private int $searchDeactivatedUsers = 0;

    public AbstractCrudObject $crud;

    function __construct(AbstractCrudObject $crud, string|int $userId = 0) {
        $this->crud = $crud;
        if(isset($_SESSION["access_level"])) $this->requestingUsersAccessLevel = $_SESSION["access_level"];
        if(isset($_SESSION["uid"])) $this->requestingUsersId = (int)$_SESSION["uid"];
        $this->identifier($userId);
    }

    public function setDifferentRequestingUser(string|int $userId, string|int $accessLevel): void {
        $this->requestingUsersAccessLevel = $accessLevel;
        $this->requestingUsersId = $userId;
        $this->validUser = true;
    }

    private function access(int $actionType): bool {
        if($this->disabledDepthCheck) return true;
        return $this->crud->hasAccess("node","user",$actionType, $this->requestingUsersAccessLevel);
    }

    public function disableDepthCheck(): static { $this->disabledDepthCheck = true; return $this; }
    public function enableDepthCheck(): static { $this->disabledDepthCheck = false; return $this; }
    public function disableDeactivatedSearch(): void { $this->searchDeactivatedUsers = 0; }
    public function enableDeactivatedSearch(): void { $this->searchDeactivatedUsers = 1; }

    public function accessToken(string|int $userId = 0): string {
        $field = "access_token";
        $user = $this->get($userId,array($field));
        return empty($user) ? "" : $user[$field];
    }

    public function username(string|int $userId = 0): string {
        $field = "username";
        $user = $this->get($userId,array($field));
        return empty($user) ? "" : $user[$field];
    }

    public function nickname(string|int $userId = 0): string {
        $field = "nickname";
        $user = $this->get($userId,array($field));
        return empty($user) ? "" : $user[$field];
    }

    public function directory(string|int $userId = 0): string {
        $field = "directory";
        $user = $this->get($userId,array($field));
        return empty($user) ? "" : $user[$field];
    }

    public function email(string|int $userId = 0): string {
        $field = "email";
        $user = $this->get($userId,array($field));
        return empty($user) ? "" : $user[$field];
    }

    public function accessLevel(string|int $userId = 0): int {
        $field = "access_level";
        $user = $this->get($userId,array($field));
        return empty($user) ? 0 : (int)$user[$field];
    }


    public function timeOfCreation(string|int $userId = 0): int {
        $field = "created_at";
        $user = $this->get($userId,array($field));
        return empty($user) ? 0 : (int)$user[$field];
    }

    public function creator(string|int $userId = 0): int {
        $field = "created_by";
        $user = $this->get($userId,array($field));
        return empty($user) ? 0 : (int)$user[$field];
    }

    public function uid(string|int $userId = 0): string {
        $field = "uid";
        $user = $this->get($userId,array($field));
        return empty($user) ? "" : $user[$field];
    }

    public function projectId(string|int $userId = 0): string|int {
        $field = "project_id";
        $user = $this->get($userId,array($field));
        return empty($user) ? "" : $user[$field];
    }

    public function getUserProject(): array {
        $projectId = isset($_SESSION["project_id"]) && !empty($_SESSION["project_id"]) ? $_SESSION["project_id"] : $this->projectId($this->requestingUsersId);
        return $this->crud->projects()->get($projectId);
    }



    public function resetPwdToDefault(array $args): array {
        if(empty($args) || !array_key_exists("fields", $args)) return array("status" => "error", "error" => "No fields given");
        $fields = $args["fields"];

        if(!array_key_exists("uid", $fields)) return array("status" => "error", "error" => "uid given");
        $uid = (int)$fields["uid"];

        $user = $this->get($uid);
        if(empty($user)) return array("status" => "error", "error" => "Could not find user");

        $defaultPassword = md5(123456);
        if($user["password"] === $defaultPassword) return array("status" => "success");

        $param = array("password" => $defaultPassword);
        return $this->update($param, array("uid" => $uid)) ? array("status" => "success") : array("status" => "error", "error" => "Failed to update user");
    }



    public function getBaseFileContent(string|int $userId = 0): array {
        $directory = $this->directory($userId);

        if(empty($directory)) return array();
        if(!file_exists(ROOT . $directory . "/" . USER_BASE_FILE)) return array();

        return json_decode(file_get_contents(ROOT . $directory . "/" . USER_BASE_FILE),true);
    }



    public function getByX(array $params = array(), array $fields = array(), string $customSql = "", bool $includeAll = false): array {
        if(!$this->disabledDepthCheck) {
            if(!$this->access(READ_ACTION)) return array();
            $params = $this->crud->resolveMultiParamsDepth($params, $this->userDepthParams(), array("uid"));
            if($params === null) return array();
        }

        if(!$includeAll && !array_key_exists("deactivated", $params))  $params["deactivated"] = $this->searchDeactivatedUsers;

        return $this->crud->retrieve("user",$params, $fields,$customSql);
    }

    public function getXByCreatedAtMax(int $createdAtMax): array {
        return $this->getByX(array(),array(),
            "SELECT * FROM users WHERE created_at <= $createdAtMax"
        );
    }

    public function getByUniqueId(string|int $uniqueId): array {
        $users = $this->getByX(array("unique_id" => $uniqueId));
        return array_key_exists(0, $users) ? $users[0] : $users;
    }

    public function getByEmail(string|int $uniqueId): array {
        $users = $this->getByX(array("email" => $uniqueId));
        return array_key_exists(0, $users) ? $users[0] : $users;
    }

    public function get(string|int $userId = 0, $fields = array()): array {
        $this->identifier($userId);
        if(!$this->status()) return array();

        if(!$this->disabledDepthCheck) $this->userId = $this->consolidateUserDepth($this->userId);

        $user = $this->getByX(array("uid" => $this->userId),$fields);

        return array_key_exists(0,$user) && is_array($user[0]) ? $user[0] : $user;
    }

    public function exists (string|int $userId): bool {
        if(!$this->access(READ_ACTION)) return false;

        return ($this->crud->check("user",array("uid" => $userId))) === 1;
    }

    private function identifier(string|int $userId): void {
        if((int)$userId === 0) return;
        if($userId === $this->userId) return;
        if($this->exists($userId)) {
            $this->userId = $userId;
            $this->validUser = true;
        }
    }

    private function status(): bool { return $this->validUser && $this->access(READ_ACTION); }


    public function update(array $params, array $identifier, string $sql = ""): bool {
        if ((empty($params) || empty($identifier)) && empty($sql)) return false;

        if(!$this->disabledDepthCheck) {
            if(!$this->access(MODIFY_ACTION)) return false;
            $depthParams = $this->userDepthParams();
            if(array_key_exists("uid", $depthParams))
                if((array_key_exists("uid", $identifier) && (int)$identifier["uid"] !== (int)$depthParams["uid"])) return false;
        }

        return $this->crud->update("user", array_keys($params), $params, $identifier, $sql) === 1;
    }


    public function toggleUserSuspension(array $args): array {
        if(!$this->access(MODIFY_ACTION)) return ["status" => "error", "error" => ["message" => "Missing permissions"]];
        if(!array_key_exists("uid", $args)) return ["status" => "error", "error" => ["message" => "Missing user id"]];
        if(!$this->crud->userRoles()->isAdmin()) return ["status" => "error", "error" => ["message" => "Missing permissions"]];

        $user = $this->get($args["uid"]);
        if((int)$user["uid"] === (int)$_SESSION["uid"]) return ["status" => "error", "error" => ["message" => "You cannot toggle yourself"]];
        if(empty($user) || (int)$user["access_level"] > (int)$_SESSION["access_level"])
            return ["status" => "error", "error" => ["message" => "Failed to identify user"]];

        $this->update(["deactivated" => (int)(!((int)$user["deactivated"]))], ["id" => $user["id"]]);
        return array("status" => "success", "success" => true);
    }


    public function getChangeLog(string|int $userId = 0): array {
        $this->identifier($userId);
        if(!$this->status()) return array();

        $baseObject = $this->getBaseFileContent();
        return array_key_exists("change_log", $baseObject) ? $baseObject["change_log"] : array();
    }

    public function registrationIsComplete(): bool {return (int)($this->get($this->requestingUsersId)["registration_complete"]) === 1; }
    public function setCompleteRegistrationIfComplete(): bool {
        $user = $this->get($_SESSION["uid"]);
        if((int)$user["registration_complete"] === 1 ) {
            if($this->crud->isCreator() && empty($this->crud->integrations()->getByX(["user_id" => $user["uid"]]))) {
                $this->update(["registration_complete" => 0], ["uid" => $user["uid"]]);
                return false;
            }
            return true;
        }

        if(!empty($this->crud->integrations()->getByX(["user_id" => $user["uid"]]))) {
            $this->update(["registration_complete" => 1], ["uid" => $user["uid"]]);
            return true;
        }
        return false;
    }
    public function integrationUnderway(): bool {
        if(!$this->crud->isCreator()) return false;
        $integration = $this->crud->integrations()->getByX(["user_id" =>$_SESSION["uid"], "provider" => "instagram"]);
        if(empty($integration)) return false;

        $integration = $integration[0];
        $username = $integration["item_name"];

        return empty($this->crud->lookupList()->getByUsername($username));
    }

    public function creatorId(string|int $uid = 0): string|int {
        if($uid === 0) $uid = $_SESSION["uid"];
        if(!$this->crud->isCreator($this->accessLevel($uid))) return 0;

        $integration = $this->crud->integrations()->getMyIntegration($uid);
        if(empty($integration)) return 0;
        return $this->crud->nestedArray($this->crud->lookupList()->getByUsername($integration["item_name"]), ["id"], 0);
    }


    public function getUserLogs(string $type = "connections"): string {
        $users = $this->getByX();
        if(empty($users)) return json_encode(array());

        $response = array();

        foreach ($users as $user) {
            $userDir = $user["directory"];
            if($type === "connections") $log = ROOT . $userDir . "/" . USER_LOGS . USER_CONNECTION_LOG;
            else continue;

            if(!file_exists($log)) continue;
            $logContents = file_get_contents($log);
            if(empty($logContents)) continue;

            $logContents = (explode(PHP_EOL, $logContents));
            array_pop($logContents);

            $response[] = array(
                "name" => Titles::prettifiedUppercase($user["nickname"]),
                "content" => array_map(function ($item) { return json_decode($item,true); }, $logContents)
            );
        }

        return json_encode($response);
    }






    public function searchUser(array $args): array {
        if(!$this->access(READ_ACTION)) return array();
        if(!isset($args["username"]) || empty($args["username"])) return array("error" => "No username given");
        $name = $args["username"]; $igUserSearch = false;

        if(str_starts_with($name, "@")){
            $igUserSearch = true;
            $name = substr($name,1);
        }


        if(empty($name)) return array();

        $userRoles = $this->crud->userRoles();
        $depth = $userRoles->depth($this->accessLevel($_SESSION["uid"]));

        switch ($depth) {
            default: return array();
            case "user":
                $paramsFilters = array("uid" => $_SESSION["uid"]); //Search user
                break;
            case "all":
                $paramsFilters = array();
                break;
        }


        $paramsFilters["deactivated"] = $this->searchDeactivatedUsers;
        $queries = $this->crud->retrieve("user",$paramsFilters,array("uid","username","nickname","email"));

        $matches = empty($queries) ? $queries : array_values(array_filter($queries,function ($query) use ($name, $queries){
            if(array_key_exists("uid",$query) && (empty($query["uid"]) || (int)$query["uid"] === 0)) return false;

            foreach ($query as $key => $value) {
                if(str_contains(strtolower($value), strtolower($name))) return true;
            }

            return false;
        }));

        if(empty($matches)) return array();

        $matches = (array_chunk($matches,10))[0];
        foreach ($matches as $i => $match) {
            $baseFile = "users/" . $match["uid"] . "/" . USER_BASE_FILE;
            $baseInfo = json_decode(file_get_contents($baseFile),true);

            $matches[$i]["picture"] = $baseInfo["picture"];
        }

        return $matches;
    }



    public function consolidateUserDepth(string|int $requestedUsersId = 0): int {
        $this->identifier($requestedUsersId);
        if(!$this->status()) return 0;

        if($this->disabledDepthCheck) return $this->userId;
        $this->disabledDepthCheck = true;

        $requestedUsersId = (int)$this->userId;
        $requestingUsersId = isset($_SESSION["uid"]) ? (int)$_SESSION["uid"] : 0;

        $depth = $this->crud->userRoles()->depth($this->requestingUsersAccessLevel);

        if($depth === "user") $userId = $requestingUsersId;
        elseif (empty($depth)) $userId = 0;
        else $userId = $requestedUsersId;

        $this->disabledDepthCheck = false;
        return $userId;
    }


    public function userDepthParams(): array {
        if($this->disabledDepthCheck) return array();
        $this->disabledDepthCheck = true;
        $depth = $this->crud->userRoles()->depth($this->requestingUsersAccessLevel);

        if($depth === "user") $param = array("uid" => $this->requestingUsersId);
        elseif (empty($depth)) $param = array("uid" => 0);
        else $param = array();

        $this->disabledDepthCheck = false;
        return $param;
    }





}






























