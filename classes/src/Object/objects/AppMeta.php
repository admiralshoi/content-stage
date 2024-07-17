<?php
namespace classes\src\Object\objects;
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

use classes\src\AbstractCrudObject;
use JetBrains\PhpStorm\ArrayShape;

class AppMeta {
    private int $requestingUsersAccessLevel = 0;
    private int $requestingUsersId = 0;
    private AbstractCrudObject $crud;

    function __construct(AbstractCrudObject $crud) {
        $this->crud = $crud;

        if(isset($_SESSION["access_level"])) $this->requestingUsersAccessLevel = $_SESSION["access_level"];
        if(isset($_SESSION["uid"])) $this->requestingUsersId = $_SESSION["uid"];
    }

    private function access(int $actionType): bool {
        return $this->crud->hasAccess("node","meta",$actionType, $this->requestingUsersAccessLevel);
    }


    public function get(string $metaName): mixed {
        $item = $this->getRow($metaName);
        if(empty($item)) return null;

        $data = $item["value"];
        $dataType = $item["type"];

        return match ($dataType) {
            "array" => json_decode($data, true),
            "string" => (string)$data,
            "int" => (int)$data,
            "float" => (float)$data,
            "bool" => in_array($data, array("true", "false")) ? (str_contains($data, "true")) : (bool)$data,
            default => null
        };
    }




    public function getRow(string $metaName, $fields = array()): array {
        if(!$this->access(READ_ACTION)) return array();

        $row = $this->crud->retrieve("meta",array("name" => $metaName),$fields);
        return array_key_exists(0,$row) && is_array($row[0]) ? $row[0] : $row;
    }


    public function getByX(array $params = array(), array $fields = array()): array {
        if(!$this->access(READ_ACTION)) return array();
        return $this->crud->retrieve("meta",$params,$fields);
    }


    public function update(array|string|int|float $value, string $metaName, string $sql = ""): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(empty($metaName)) return false;

        if(is_array($value)) $value = json_encode($value);
        $params = array("value" => $value);

        return $this->crud->update("meta",array_keys($params),$params,array("name" => $metaName), $sql) === 1;
    }

    public function create(array $params): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(empty($params)) return false;

        return $this->crud->create("meta",array_keys($params),$params);
    }



    public function getAllAsKeyPairs(): object {
        $items = $this->getByX();
        if(empty($items)) return (object)[];
        return (object) array_reduce($items, function ($initial, $item) {
            if(!isset($initial)) $initial = [];
            $key = $item["name"];
            $value = $this->crud->enforceDataType($item["value"], $item["type"]);
            return array_merge($initial, [$key => $value]);
        });
    }

    public function mergeSettingsWithRole(array|object $settings): object {
        if(empty($settings)) return (object)[];
        if(is_object($settings)) $settings = (array)$settings;
        $roleSettings = $this->crud->userRoles()->userRoleSettings($settings);
        if(array_key_exists("user_role_settings", $settings)) unset($settings["user_role_settings"]);
        if(empty($roleSettings)) return (object)$settings;

        foreach ($roleSettings as $key => $value) $settings[$key] = $value;
        return (object)$settings;
    }


}

