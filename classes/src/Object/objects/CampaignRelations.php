<?php

namespace classes\src\Object\objects;

use classes\src\AbstractCrudObject;

class CampaignRelations {


    private AbstractCrudObject $crud;
    private int $requestingUsersAccessLevel = 0;
    private int $requestingUsersId = 0;
    private bool $disabledDepthCheck = false;
    public bool $isError = true;
    private array $responseError = array(
        "status" => "error",
        "error" => array(
            "message" => "",
            "code" => 101
        )
    );
    private array $responseSuccess = array(
        "status" => "success",
        "data" => array()
    );


    function __construct(AbstractCrudObject $crud) {
        $this->crud = $crud;
        if(isset($_SESSION["access_level"])) $this->requestingUsersAccessLevel = $_SESSION["access_level"];
        if(isset($_SESSION["uid"])) $this->requestingUsersId = $_SESSION["uid"];
    }





    public function getResponse(): array {
        return $this->isError ? $this->responseError : $this->responseSuccess;
    }



    private function access(int $actionType): bool {
        if($this->disabledDepthCheck) return true;
        return $this->crud->hasAccess("node","campaigns",$actionType, $this->requestingUsersAccessLevel);
    }

    public function disableDepthCheck(): static { $this->disabledDepthCheck = true; return $this; }
    public function enableDepthCheck(): static { $this->disabledDepthCheck = false; return $this; }

    /*
     * Core CRUD features END
     */

    public function getByX(array $params = array(), array $fields = array(), string $customSql = ""): array {
        if(!$this->access(READ_ACTION)) return array();
        return $this->crud->retrieve("campaign_relations",$params, $fields,$customSql);
    }
    public  function create(array $params): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        if(!array_key_exists("created_at", $params)) $params["created_at"] = time();
        return $this->crud->create("campaign_relations", array_keys($params), $params);
    }
    public function update(array $params, array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        return $this->crud->update("campaign_relations", array_keys($params), $params, $identifier);
    }
    public  function delete(array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        return $this->crud->delete("campaign_relations", $identifier);
    }
    public function get(string|int $id, array $fields = array()): array {
        $item = $this->getByX(array("id" => $id), $fields);
        return array_key_exists(0, $item) ? $item[0] : $item;
    }














}