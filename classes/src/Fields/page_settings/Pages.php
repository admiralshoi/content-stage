<?php
namespace classes\src\Fields\page_settings;


class Pages extends PageSettings {

    function __construct(){
        parent::__construct();
    }

    public function pageContent($page) {
        if(!array_key_exists($page,$this->pageContents)) return array();
        return $this->pageContents[$page];
    }


    public function innerPages($page) {
        $innerPages = $this->innerPages->getPages();

        if(!array_key_exists($page,$innerPages))
            return array();
        return $innerPages[$page];
    }

    public function innerPagesContent($page,$default) {
        $content = $this->innerPages->getPagesContent();
        return array_key_exists($page, $content) ? $content[$page] : $default;
    }

    public function sideBarMenuAccess($loggedIn,$access_level) {
        $bars = $this->sideBar->sideBarAccess($loggedIn);
        if(empty($bars)) return $bars;

        $filter = array_filter($bars,function ($bar) use ($access_level) {
            if(empty($bar["access_level"])) return true;
            return in_array($access_level,$bar["access_level"]);
        });

        $response = array();
        foreach ($filter as $name => $value) $response[$name] = $value;


        return $response;
    }

    public function sideBarMenuLinks($barName) {
        return $this->sideBar->sideBarLinks($barName);
    }


}