<?php

namespace classes\src\Object\objects;
use classes\src\AbstractCrudObject;
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

class UserRoles {

    public AbstractCrudObject $crud;
    private int $accessLevel = 0;
    private bool $validRole = false;
    private int $requestingUsersAccessLevel = 0;
    private int $requestingUsersId = 0;

    function __construct(AbstractCrudObject $crud, string|int $accessLevel = 0){
        $this->crud = $crud;
        if(isset($_SESSION["access_level"])) $this->requestingUsersAccessLevel = $_SESSION["access_level"];
        if(isset($_SESSION["uid"])) $this->requestingUsersId = (int)$_SESSION["uid"];
        $this->identifier($accessLevel);
    }


    public function name(string|int $accessLevel = 0): string {
        $field = "name";
        $role = $this->get($accessLevel, array($field));
        return empty($role) ? "" : $role[$field];
    }

    public function description(string|int $accessLevel = 0): string {
        $field = "description";
        $role = $this->get($accessLevel, array($field));
        return empty($role) ? "" : $role[$field];
    }

    public function depth(string|int $accessLevel = 0): string {
        $field = "depth";
        $role = $this->get($accessLevel, array($field));
        return empty($role) ? "" : $role[$field];
    }

    public function isDefined(string|int $accessLevel = 0): int {
        $field = "defined";
        $role = $this->get($accessLevel, array($field));
        return empty($role) ? 0 : (int)$role[$field];
    }

    public function get(string|int $accessLevel = 0,  $fields = array()): array {
        if($accessLevel === 0 && $this->accessLevel === 0) $accessLevel = $this->requestingUsersAccessLevel;
        $this->identifier($accessLevel);
        if (!$this->status()) return array();

        $role = $this->crud->retrieve("roles", array("access_level" => $this->accessLevel), $fields);
        return array_key_exists(0, $role) && is_array($role[0]) ? $role[0] : $role;
    }

    public function getByX(array $params = array(), array $fields = array()): array { return $this->crud->retrieve("roles", $params, $fields); }


    public function exists(string|int $accessLevel): bool {
        return ($this->crud->check("roles", array("access_level" => $accessLevel))) === 1;
    }

    private function identifier(string|int $accessLevel): void {
        if ($accessLevel === 0) return;
        if ($accessLevel === $this->accessLevel) return;
        if ($this->exists($accessLevel)) {
            $this->accessLevel = $accessLevel;
            $this->validRole = true;
        };
    }

    private function status(): bool { return $this->validRole; }


    public function update(array $params, array $identifier): bool {
        if (empty($params) || empty($identifier)) return false;

        return $this->crud->update("roles", array_keys($params), $params, $identifier) === 1;
    }

    public function create(array $params): bool {
        if (empty($params)) return false;

        return $this->crud->create("roles", array_keys($params), $params);
    }


    public function isAdmin(string|int $accessLevel = 0): bool {
        if(isset($_SESSION["guest"])) return false;
        if($accessLevel === 0 && $this->accessLevel === 0) $accessLevel = $this->requestingUsersAccessLevel;
        elseif($accessLevel === 0 && $this->accessLevel !== 0) $accessLevel = $this->accessLevel;

        return in_array($this->name($accessLevel), array("admin", "system_admin"));
    }
    public function isCreator(string|int $accessLevel = 0, bool $strict = true): bool {
        if(isset($_SESSION["guest"])) return false;
        if($accessLevel === 0 && $this->accessLevel === 0) $accessLevel = $this->requestingUsersAccessLevel;
        elseif($accessLevel === 0 && $this->accessLevel !== 0) $accessLevel = $this->accessLevel;

        $namedRoles = $strict ? ["creator"] : ["creator", "creator_tester"];
        return in_array($this->name($accessLevel), $namedRoles);
    }
    public function isCreatorTester(string|int $accessLevel = 0, bool $strict = true): bool {
        if(isset($_SESSION["guest"])) return false;
        if($accessLevel === 0 && $this->accessLevel === 0) $accessLevel = $this->requestingUsersAccessLevel;
        elseif($accessLevel === 0 && $this->accessLevel !== 0) $accessLevel = $this->accessLevel;

        return $this->name($accessLevel) === "creator_tester";
    }
    public function isBrandTester(string|int $accessLevel = 0): bool {
        if(isset($_SESSION["guest"])) return false;
        if($accessLevel === 0 && $this->accessLevel === 0) $accessLevel = $this->requestingUsersAccessLevel;
        elseif($accessLevel === 0 && $this->accessLevel !== 0) $accessLevel = $this->accessLevel;

        return $this->name($accessLevel) === "brand_tester";
    }
    public function isGuest(string|int $accessLevel = 0): bool {
        return isset($_SESSION["guest"]);
    }
    public function isBrand(string|int $accessLevel = 0, bool $strict = true): bool {
        if(isset($_SESSION["guest"])) return false;
        if($accessLevel === 0 && $this->accessLevel === 0) $accessLevel = $this->requestingUsersAccessLevel;
        elseif($accessLevel === 0 && $this->accessLevel !== 0) $accessLevel = $this->accessLevel;

        $namedRoles = $strict ? ["brand"] : ["brand", "brand_tester"];
        return in_array($this->name($accessLevel), $namedRoles);
    }


    public function getRoleByName(string $name): array {
        if(empty($name)) return array();
        $roles = $this->getByX(array("name" => $name));

        return array_key_exists(0, $roles) ? $roles[0] : $roles;
    }

    public function accessLevel(string $name): int {
        $role = $this->getRoleByName($name);
        if(empty($role)) return 0;

        return !array_key_exists("access_level", $role) ? 0 : (int)$role["access_level"];
    }


    public function hasRoleOfX(string|int $accessLevel, string $roleName): bool {
        if(empty($accessLevel) || empty($roleName)) return false;
        if($accessLevel === 0) return false;

        return $this->accessLevel($roleName) === (int)$accessLevel;
    }


    public function userRoleSettings(array|object $data = []): array {
        $roleName = $this->name();
        if(empty($roleName)) return [];
        if(is_object($data)) $data = (array)$data;
        if(empty($data) || !array_key_exists("user_role_settings", $data)) $data = $this->crud->appMeta()->get("user_role_settings");
        else $data = $data["user_role_settings"];

        if(empty($data) || !array_key_exists($roleName, $data)) return [];
        return $data[$roleName];
    }
}






























