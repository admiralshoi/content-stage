<?php

namespace classes\src\Object\objects;
use classes\src\AbstractCrudObject;
use JetBrains\PhpStorm\ArrayShape;

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

class Misc {
    private AbstractCrudObject $crud;


    function __construct(AbstractCrudObject $crud) {
        $this->crud = $crud;
    }



    public function getNamesLibrary(): array {
        if(!file_exists(GENDER_LIB)) return array();

        $list = file_get_contents(GENDER_LIB);
        if(empty($list)) return array();

        return json_decode($list, true);
    }


    public function setNewPageContent(array $request): bool {
        foreach (array("target", "content") as $key) if(!array_key_exists($key, $request)) return false;
        $content = $request["content"];
        $targetPage = $request["target"];

        if(!in_array($targetPage, array("privacy_policy", "terms_of_use"))) return false;
        $newFilename = $targetPage . "_" . time() . "_" . rand(5,1000) . ".html";
        $path = "includes/content/legal/";

        $filepath = $path . $newFilename;
        file_put_contents(ROOT . $filepath, $content);

        $metaName = "current_$targetPage";
        $this->crud->appMeta()->update($filepath, $metaName);

        return true;
    }





}