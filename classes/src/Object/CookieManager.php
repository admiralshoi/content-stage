<?php

namespace classes\src\Object;

use classes\src\AbstractCrudObject;
use JetBrains\PhpStorm\ArrayShape;
use classes\src\Enum\HandlerErrors;

class CookieManager{

    public AbstractCrudObject $crud;
    protected array $availableCookies;
    protected array $currentCookie;
    protected array $usedCookieIds = [];

    function __construct(AbstractCrudObject $crud) {
        $this->crud = $crud;
        $this->getCookies();
    }

    /*
     * Core CRUD features START
     */

    public function getByX(array $params = array(), array $fields = array(), string $customSql = ""): array {
        return $this->crud->retrieve("cookie",$params, $fields,$customSql);
    }
    public function get(string|int $rowId, array $fields = array()): array {
        $rows = $this->getByX(array("id" => $rowId), $fields);
        return array_key_exists(0, $rows) ? $rows[0] : $rows;
    }
    private function getDefault(): array {
        $rows = $this->getByX(array("default_cookie" => 1));
        return array_key_exists(0, $rows) ? $rows[0] : $rows;
    }
    private function getByCookie(string $cookie): array {
        $rows = $this->getByX(array("cookie" => $cookie));
        return array_key_exists(0, $rows) ? $rows[0] : $rows;
    }
    private function create(array $params): bool {
        $params["created_at"] = time();
        $params["updated_at"] = time();
        return $this->crud->create("cookie", array_keys($params), $params);
    }
    private function update(array $params, array $identifier): bool {
        $params["updated_at"] = time();
        return $this->crud->update("cookie", array_keys($params), $params, $identifier);
    }
    private function delete(array $identifier): bool {
        return $this->crud->delete("cookie", $identifier);
    }

    /*
     * Core CRUD features END
     */

    #[ArrayShape(["valid" => "array", "invalid" => "array"])]
    public function getCookieDisplayList(): array {
        $res = ["valid" => $this->availableCookies, "invalid" => $this->getByX(["is_valid" => 0])];
        $this->crud->sortByKey($res["invalid"], "total_uses");
        $this->crud->sortByKey($res["valid"], "total_uses");
        $this->crud->sortByKey($res["valid"], "default");
        return $res;
    }

    public function createNewCookie(array $request): array {
        foreach (["name", "cookie"] as $key) if(!array_key_exists($key, $request) || empty($request[$key])) return HandlerErrors::NO_INPUT;
        $cookie = $request["cookie"];
        $name = $request["name"] . " - " . date("F d, Y");
        $default = array_key_exists("default_cookie", $request) && $request["default_cookie"] === "on" ? 1: 0;
        $maxErrorStreak = array_key_exists("max_error_streak", $request) && (int)$request["max_error_streak"] > 0 ? (int)$request["max_error_streak"] : 20;

        if(!str_starts_with($cookie, "cookie: ") && !str_starts_with($cookie, "Cookie: ")) $cookie = "cookie: " . $cookie;
        if(!empty($this->getByCookie($cookie))) return HandlerErrors::CREATE_COOKIE_ALREADY_EXIST;

        $isCreated = $this->create([
            "name" => $name,
            "cookie" => $cookie,
            "max_error_streak" => $maxErrorStreak,
        ]);

        if(!$isCreated) return HandlerErrors::CREATE_COOKIE_ERROR;
        sleep(1);
        if($default) {
            $row = $this->getByCookie($cookie);
            if(empty($row)) return HandlerErrors::CREATE_COOKIE_ERROR;
            $this->changeDefault($row["id"]);
        }

        return [
            "status" => "success",
            "data" => "The new cookie '$name' is set successfully"
        ];
    }

    public function changeDefault(string|int $rowId): bool {
        $row = $this->get($rowId);
        if(empty($row) || (int)$row["default_cookie"] === 1) return false;

        $currentDefault = $this->getDefault();
        if(!empty($currentDefault)) $this->update(["default_cookie" => 0], ["id" => $currentDefault["id"]]);
        return $this->update(["default_cookie" => 1], ["id" => $rowId]);
    }



    public function reloadCookies(): static { return $this->getCookies(); }
    protected function getCookies(): static {
        $this->availableCookies = $this->getByX(["is_valid" => 1]);
        return $this;
    }


    public function remainingUnusedCookies(): int { return count($this->availableCookies) - count($this->usedCookieIds); }
    public function isUnusedCookies(): bool { return count($this->usedCookieIds) < count($this->availableCookies); }
    public function cookieGet(bool $setNewIfNone = true): string {
        return !empty($this->currentCookie) ? $this->currentCookie["cookie"] : ($setNewIfNone ? $this->cookieSet()->cookieGet(false) : "");
    }
    public function cookieSet(): static {
        if(!empty($this->availableCookies)) {
            $newCookies = array_values(array_filter($this->availableCookies, function ($cookie) { return !in_array($cookie["id"], $this->usedCookieIds); }));
            $this->currentCookie = empty($newCookies) ? $this->availableCookies[(rand(0, (count($this->availableCookies) - 1)))] :
                $newCookies[(rand(0, (count($newCookies) - 1)))];
        }
        else $this->currentCookie = [];
        if(!empty($this->currentCookie)) $this->usedCookieIds[] = $this->currentCookie["id"];
        return $this;
    }
    public function cookieInvalidate(): static {
        if(!empty($this->currentCookie)) $this->update(["is_valid" => 0], ["id" => $this->currentCookie["id"], "default_cookie" => 0]);
        return $this;
    }
    public function cookieUsageIncrement(bool $success = true): static {
        if(!empty($this->currentCookie))
            $this->currentCookie["error_streak"] = $success ? 0 : (int)$this->currentCookie["error_streak"] +1;
        $defaultCookie = (int)$this->currentCookie["default_cookie"] === 1 ? ($success ? 1 : 0) : 0;
        $this->update(
            [
                "total_uses" => (int)$this->currentCookie["total_uses"] +1,
                "failures" => !$success ? (int)$this->currentCookie["failures"] +1 : (int)$this->currentCookie["failures"],
                "successes" => $success ? (int)$this->currentCookie["successes"] +1 : (int)$this->currentCookie["successes"],
                "error_streak" => $this->currentCookie["error_streak"],
                "is_valid" => (int)($this->currentCookie["error_streak"] <= (int)$this->currentCookie["max_error_streak"]),
                "default_cookie" => $defaultCookie
            ],
            ["id" => $this->currentCookie["id"]                ]
        );
        return $this;
    }
    public function cookieSetDefault(): static {
        if(!empty($this->availableCookies)) {
            $default = array_values(array_filter($this->availableCookies, function ($cookie) {
                return (int)$cookie["default_cookie"] === 1;
            }));
            if(!empty($default)) $this->currentCookie = $default[0];
            else $this->cookieSet();
        }
        else $this->cookieSet();
        return $this;
    }
}