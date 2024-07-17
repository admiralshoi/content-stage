<?php

namespace classes\src\Object\objects;
use classes\src\AbstractCrudObject;

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

class AccessPoints {

    public AbstractCrudObject $crud;
    private array $specs;
    private bool $validSpec = false;

    function __construct(AbstractCrudObject $crud, array $specs = array()){
        $this->crud = $crud;
        $this->specs = $specs;
    }


    public function name(array $specs = array()): string {
        $field = "name";
        $accessPoint = $this->get($specs, array($field));
        return empty($accessPoint) ? "" : $accessPoint[$field];
    }

    public function accessLevels(array $specs = array()): array {
        $field = "access_levels";
        $accessPoint = $this->get($specs, array($field));
        if(empty($accessPoint) || empty($accessPoint[$field])) return array();

        return explode(",", $accessPoint[$field]);
    }

    public function type(array $specs = array()): string {
        $field = "type";
        $accessPoint = $this->get($specs, array($field));
        return empty($accessPoint) ? "" : $accessPoint[$field];
    }
    public function description(array $specs = array()): string {
        $field = "description";
        $accessPoint = $this->get($specs, array($field));
        return empty($accessPoint) ? "" : $accessPoint[$field];
    }

    public function actionLevel(array $specs = array()): int {
        $field = "action_level";
        $accessPoint = $this->get($specs, array($field));
        return empty($accessPoint) ? 0 : (int)$accessPoint[$field];
    }

    public function get(array $specs = array(), array $fields = array()): array {
        $this->identifier($specs);
        if (!$this->status()) return array();

        $accessPoint = $this->crud->retrieve("access",$specs, $fields);
        return array_key_exists(0, $accessPoint) && is_array($accessPoint[0]) ? $accessPoint[0] : $accessPoint;
    }

    public function getByX(array $params = array(), array $fields = array()): array { return $this->crud->retrieve("access", $params, $fields); }


    public function getMultiPoints(array $params = array()): array {
        $list = $this->getByX($params);

        if(empty($list)) return array();

        return array_map(function ($accessPoint) {
            if(empty($accessPoint["access_levels"])) return array();
            return explode(",", $accessPoint["access_levels"]);
        }, $list);
    }

    public function getAccessLevelsFromMultiPoint(array $params, array $list): array {
        if(empty($list) || empty($params)) return array();
        if(!array_key_exists("type",$params) || !array_key_exists("name", $params)) return array();

        $point = array_filter($list, function ($item) use ($params) {
            if(!array_key_exists("type",$item) || !array_key_exists("name", $item)) return false;

            return $item["type"] === $params["type"] && $item["name"] === $params["name"];
        });

        $point = array_values($point);
        if(is_array($point) && array_key_exists(0,$point)) $point = $point[0];

        if(empty($point) || !is_array($point) || !array_key_exists("access_levels",$point) || empty($point["access_levels"])) return array();

        return explode(",",$point["access_levels"]);
    }


    public function exists(array $specs): bool {
        if(empty($specs)) return false;
        return ($this->crud->check("access", $specs)) === 1;
    }

    private function identifier(array $specs): void {
        if (empty($specs)) return;
        if ($specs === $this->specs) return;
        if ($this->exists($specs)) {
            $this->specs = $specs;
            $this->validSpec = true;
        };
    }

    private function status(): bool { return $this->validSpec; }


    public function update(array $params, array $identifier): bool {
        if (empty($params) || empty($identifier)) return false;

        return $this->crud->update("access", array_keys($params), $params, $identifier) === 1;
    }

    public function create(array $params): bool {
        if (empty($params)) return false;

        return $this->crud->create("access", array_keys($params), $params);
    }

    public function userCanAccess(string|int $accessLevel, array $authorizedLevels): bool {
        if(empty($authorizedLevels)) return true;

        return in_array($accessLevel,$authorizedLevels);
    }


    public function hasAccess(string|int $accessLevel, array $specs = array()): bool {
        if(!isset($accessLevel)) return false;
        $this->identifier($specs);
        if (!$this->status()) return false;

        $accessLevels = $this->accessLevels();
        return in_array($accessLevel,$accessLevels);
    }


}






























