<?php
namespace classes\src\Enum;

class QuerySelector {


    public static function set(array $list): ?QuerySelection {
        $querySelection = new QuerySelection();
        $querySelection->set($list);
        return $querySelection->instance();
    }



}