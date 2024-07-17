<?php
namespace classes\src\Fields;

use classes\src\Enum\Paths;

class Fields {
    protected $page_construct;
    public $page_construct_paths = array();

    function __construct(array $page) {
        $this->page_construct = array(
            "html" => array(
                "header" => $page["header"],
                "body" => $page["body"],
                "footer" => $page["footer"],
            ),
            "css" => array(
                "css" => $page["css"]
            ),
            "js" => array(
                "js" => $page["js"]
            ),
        );
    }

    public function getFields() {
        foreach ($this->page_construct as $type => $list) {
            foreach ($list as $section => $keys) {
                $this->page_construct_paths = array_merge($this->page_construct_paths,array($section => Paths::path($keys,$type)));
            }
        }
        return $this->page_construct_paths;
    }



}