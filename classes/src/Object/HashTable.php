<?php

namespace classes\src\Object;

use classes\src\AbstractCrudObject;
use JetBrains\PhpStorm\ArrayShape;

class HashTable {
    private array $TABLE;
    private string $TABLE_PATH = "";
    private int $TABLE_ITEM_COUNT = 0;
    private int $TABLE_SIZE = 0;
    private const LOAD_FACTOR = .75;
    private const TABLE_SIZE_INITIAL = 10007;
    private const TABLE_SIZE_INCREMENT = 10007;

    function __construct(string $tablePath = "") {
        $this->TABLE_PATH = ROOT . (empty($tablePath) ? HASH_TABLE : $tablePath);

        if(!file_exists($this->TABLE_PATH)) file_put_contents($this->TABLE_PATH, json_encode(array_fill(0, self::TABLE_SIZE_INITIAL, array())));

        $table = json_decode(file_get_contents($this->TABLE_PATH));
        if(!is_array($table))
            (new AbstractCrudObject())->errorLog(array(
                "error_code" => 9844, "error" => "Corrupted table file: $this->TABLE_PATH"
            ));

        $this->TABLE = json_decode(file_get_contents($this->TABLE_PATH));

        if(!empty($this->TABLE)) $this->TABLE_ITEM_COUNT = count(array_filter($this->TABLE, function ($item){return !empty($item); }));
        $this->TABLE_SIZE = count($this->TABLE);

        $this->resize();
    }

    private function resize(): void {
        if($this->TABLE_ITEM_COUNT < ($this->TABLE_SIZE * self::LOAD_FACTOR)) return;

        $this->TABLE_SIZE =  ($this->TABLE_SIZE + self::TABLE_SIZE_INCREMENT);
        $newTable = array_fill(0,$this->TABLE_SIZE, array());
        foreach ($this->TABLE as $list) {
            if(empty($list)) continue;

            foreach ($list as $keyValuePair) {
                $key = $keyValuePair[0];
                $value = $keyValuePair[1];

                $newIdx = $this->hash($key);
                $newTable[$newIdx][] = array($key,$value);
            }
        }

        $this->TABLE = $newTable;
    }



    public function setItem(string|int $key, mixed $value): void {
        if(empty($key)) return;
        $idx = $this->hash($key);
        $isSet = $revert = false;

        if(gettype($this->TABLE[$idx]) === "object") {
            $this->TABLE[$idx] = json_decode(json_encode($this->TABLE[$idx]), true);
            $revert = true;
        }

        if(empty($this->TABLE[$idx])) $this->TABLE_ITEM_COUNT += 1;
        else {
            $existingKey = array_search($key, array_column(((array)$this->TABLE[$idx]), 0));

            if(false !== $existingKey) {
                $isSet = true;
                $this->TABLE[$idx][$existingKey] = array($key,$value);
            }
        }


        if(!$isSet) $this->TABLE[$idx][] = array($key,$value);
        if($revert) $this->TABLE[$idx] = json_decode(json_encode($this->TABLE[$idx]));

        $this->resize();
    }


    public function getItem(string|int $key): mixed {
        if(empty($key)) return null;

        $idx = $this->hash($key);
        if(empty($this->TABLE[$idx])) return null;

        $res = (array_values(array_filter((array)$this->TABLE[$idx], function($item) use ($key) { return $item[0] === $key; })));

        return empty($res) ? null :
            (is_array($res[0][1]) ? json_decode(json_encode($res[0][1]), true) :
            (gettype($res[0][1]) === "object" ? (array)json_decode(json_encode($res[0][1]), true) : $res[0][1]));
    }

    public function removeItem(string|int $key): bool {
        if(empty($key)) return false;

        $idx = $this->hash($key);
        if(empty($this->TABLE[$idx])) return false;

        //Preserve keys
        $res = ((array_filter((array)$this->TABLE[$idx], function($item) use ($key) { return $item[0] === $key; })));
        if(empty($res)) return false;

        $collector = array();
        foreach ($res as $index => $item) $collector[] = $index;

        if(gettype($this->TABLE[$idx]) === "object") $this->TABLE[$idx] = (array)$this->TABLE[$idx];
        rsort($collector);

        foreach ($collector as $index) unset($this->TABLE[$idx][$index]);

        if(empty($this->TABLE[$idx])) $this->TABLE_ITEM_COUNT -= 1;
        return true;
    }

    public function tablePath(): string { return str_replace(ROOT, "", $this->TABLE_PATH); }
    public function save(): void { file_put_contents($this->TABLE_PATH,json_encode($this->TABLE)); }
    private function hash(string|int $key): int { return (crc32($key) % $this->TABLE_SIZE); }


    #[ArrayShape(["current_size" => "string","table_item_count" => "string","table_size" => "string", "initial" => "int", "increment" => "int",
        "load_factor" => "float", "current_load" => "string"])]
    public function tableStats(): array {
        return array(
            "current_size" => $this->TABLE_ITEM_COUNT . " of " . $this->TABLE_SIZE,
            "table_item_count" => $this->TABLE_ITEM_COUNT,
            "table_size" => $this->TABLE_SIZE,
            "initial" => self::TABLE_SIZE_INITIAL,
            "increment" => self::TABLE_SIZE_INCREMENT,
            "load_factor" => self::LOAD_FACTOR,
            "current_load" => number_format(($this->TABLE_ITEM_COUNT / $this->TABLE_SIZE),8)
        );
    }


    public function getTable(): array { return $this->TABLE; }

    public function getTableItems(bool $includeKey = false): array {
        $response = array();
        $items = array_values(array_filter((array)$this->TABLE, function ($item){return !empty($item); }));

        foreach ($items as $idxCol) {
            if(empty($idxCol)) continue;
            foreach ($idxCol as $item) $response[] = $includeKey ? $item : (gettype($item[1]) === "object" ? (array)$item[1] : $item[1]);
        }

        return json_decode(json_encode($response), true);
    }


    /**
     * @param array $list
     *
     * list MUST be a key-value-pair...
     */
    public function setMultiple(array $list): void {
        if(empty($list)) return;

        foreach ($list as $keyValuePair) {
            $key = $keyValuePair[0];
            $value = $keyValuePair[1];

            $this->setItem($key, $value);
        }
    }

}