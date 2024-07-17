<?php

namespace classes\src\Enum;

class QuerySelection {

    private string $operator = "=";
    private string|int|array $value = "";
    private string $column = "";
    private string $type = "CUSTOM";
    private ?QuerySelection $currentInstance = null;


    public function set(array $list): static {
        $instance = new QuerySelection();
        foreach ($list as $i => $value) {
            if($i === 0) $instance->column = $value;
            if($i === 1 && count($list) === 2) $instance->value = $value;
            if($i === 1 && count($list) >= 3) $instance->operator = $value;
            if($i === 2) $instance->value = $value;
            if($i === 3 && in_array($value, ["CUSTOM", "SQL"]) && !is_array($instance->value)) $instance->type = $value;

        }
        $this->currentInstance = $instance;
        return $this;
    }

    public function getAsString(): string {
        if(empty($this->column) || empty($this->operator)) return "";
        if(is_array($this->value)) return "";
        return $this->column . " " . $this->operator . " '" . $this->value . "'";
    }
    public function getAsList(): ?array {
        if(empty($this->column) || empty($this->operator)) return null;
        return ["c" => $this->column, "o" => $this->operator, "v" => $this->value, "t" => $this->type];
    }

    public function instance(): ?static  {
        return $this->currentInstance;
    }

}