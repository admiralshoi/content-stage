<?php
namespace classes\src;

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

use classes\src\Db\DbConnect;
use classes\src\Enum\QuerySelection;
use JetBrains\PhpStorm\Pure;
use PDO;
use PDOException;
use classes\src\Object\objects\User;
use classes\src\Object\objects\UserRoles;
use classes\src\Object\objects\AccessPoints;
use classes\src\Object\objects\ActivityTracker;
use classes\src\Object\HashTable;
use classes\src\Object\objects\AppMeta;
use classes\src\Http\Request;
use classes\src\Object\objects\Misc;
use classes\src\Object\ConnectUser;
use classes\src\Object\RegisterUser;
use classes\src\Object\MediaStream;
use classes\src\Object\objects\Integrations;
use classes\src\Object\objects\PasswordResets;
use classes\src\Object\objects\NotificationHandler;
use classes\src\Auth\Auth;
use classes\src\Object\Scraper;
use classes\src\Object\objects\DataHandler;
use classes\src\Object\Handler;
use classes\src\Object\objects\LookupList;
use classes\src\Object\objects\LookupMedia;
use classes\src\Object\objects\Campaigns;
use classes\src\Object\objects\CampaignRelations;
use classes\src\Object\objects\AccountAnalytics;
use classes\src\Object\objects\CreatorRelations;
use classes\src\Object\CronRequestHandler;
use classes\src\Object\Messaging\Conversations;
use classes\src\Object\Messaging\Messages;


class AbstractCrudObject extends AbstractObject {
    public static PDO|null $db;
    private AccessPoints $AP;
    public ?object $settings = null;

    private array $sorting = array(
        "replacement" => array(),
        "splitReplacement" => array(),
        "key" => "",
        "key_2" => "",
        "ascending" => false
    );
    function __construct() {
        parent::__construct();
        self::$db = DbConnect::link();
        $appMeta = $this->appMeta();
        $this->settings = $appMeta->mergeSettingsWithRole(
            $appMeta->getAllAsKeyPairs()
        );
    }

    public function __get($prop){
        return $this->$prop ?? null;
    }

    protected function db_link(){
        if(self::$db instanceof PDO === false) {
            try{
                self::$db = DbConnect::link();
            } catch (PDOException $exception) {
                throw new $exception;
            }
//            if(!empty(DbConnect::$error))
//                throw new PDOException(DbConnect::$error);
        }
    }
    public function closeConnection(): void{
        self::$db = null;
    }

    public function retrieve(string $type,array $params,array $fields = array(),string|bool $customSql = false): bool|array  {
        if(!DbConnect::getTable($type)) return false;
        $this->db_link();
        $table = DbConnect::getTable($type);
        if(!$customSql) {
            $sqlSelection = $this->assembleSqlSelection($params);
            if(!empty($sqlSelection)) $sqlSelection = "WHERE $sqlSelection";
            $selectionFields = !empty($fields) ? implode(",",$fields) : "*";
            $sql = "SELECT $selectionFields FROM $table $sqlSelection";
        } else {
            $sql = $customSql;
        }

        try {
            if(self::$db === null) $link = DbConnect::link();
            else $link = self::$db;

            $query = $link->query($sql);

        } catch (PDOException $exception) {
            $error = array(
                "error_code" => 901,
                "sql" => $sql,
                "error" => $exception
            );
            $this->errorLog($error);

            return $error;
        }

        if($query->rowCount() === 0) {
            return array();
        }
        else {
            $response = array();
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                array_push($response,$row);
            }

            return $response;
        }
    }

    public function assembleSqlSelection(array $params, string $logic = "AND", string $operatorDefault = "="): string {
        $select = "";
        if(!empty($params)) {
            $i = 1;
            foreach ($params as $param=>$valueInitial) {
                $arrayOperator = "IN";
                $vType = "CUSTOM";
                $operator = $operatorDefault;
                $value = $valueInitial;
                $column = $param;

                if($value instanceof QuerySelection) {
                    $selectionItem = $value->getAsList();
                    if(empty($selectionItem)) continue;
                    $column = $selectionItem["c"];
                    $value = $selectionItem["v"];
                    $operator = $selectionItem["o"];
                    $vType = $selectionItem["t"];
                    if(!($operator === "=" && is_array($value))) $arrayOperator = $operator;
                }

                if(is_array($value)) {
                    $value = array_map(function ($item) {
                        if(is_string($item)) return "'" . $item . "'";
                        else return $item;
                    }, $value);
                    $select .= "`$column`"." $arrayOperator (".implode(",",$value).")";
                }
                else {
                    if($vType === "CUSTOM") $select .= "`$column` " . $operator . " '" . $value . "'";
                    elseif($vType === "SQL") $select .= "`$column` " . $operator . " $value";
                    else continue;
                }

                $select .= $i < count($params) ? " $logic " : "";
                $i++;
            }
        }

        return $select;
    }



    public function fetchAsPage(string $type, array $pageInfo): array  {
        if(!DbConnect::getTable($type)) return array();
        $table = DbConnect::getTable($type);

        if(empty($pageInfo)) return array();
        if(!array_key_exists("page_size", $pageInfo) || !array_key_exists("offset", $pageInfo)
            || !array_key_exists("column", $pageInfo) || !array_key_exists("order", $pageInfo)
            || !array_key_exists("starting_id", $pageInfo)) return array();

        if(!in_array($pageInfo["order"], array("ASC", "DESC"))) return array();


        $limit = (int)$pageInfo["page_size"];
        $startingId = (int)$pageInfo["starting_id"];
        $offset = (int)$pageInfo["offset"];

        $order = $pageInfo["order"];
        $column = $pageInfo["column"];

        $identifyingString = "";
        $identifier = array("uid", "username");
        foreach ($identifier as $key) {
            $collectIdentifiers = array();
            if(array_key_exists($key, $pageInfo)) {
                if(is_array($pageInfo[$key])) {
                    $pageInfo[$key] = array_map(function ($item) {
                        if(is_string($item)) return "'" . $item . "'";
                        else return $item;
                    }, $pageInfo[$key]);
                }
                else if(is_string($pageInfo[$key])) $pageInfo[$key] = "'" . $pageInfo[$key] . "'";

                $collectIdentifiers[] = $key . (is_array($pageInfo[$key]) ? " IN (".implode(", ", $pageInfo[$key]).")" : "=" . (int)$pageInfo[$key]);
            }

            if(!empty($collectIdentifiers)) {
                $identifyingString = implode(" AND ", $collectIdentifiers);
                if($startingId === 0) $identifyingString = "WHERE " . $identifyingString;
                else $identifyingString = " AND " . $identifyingString;
            }
        }

        if($startingId > 0) {
            if($order === "ASC") {
                $offset = $offset + $startingId;
                $sql = "SELECT * FROM $table WHERE $column >= $offset $identifyingString";
            } else {
                $offset = $startingId - $offset;
                $sql = "SELECT * FROM $table WHERE $column <= $offset $identifyingString";
            }
        }
        else $sql = "SELECT * FROM $table $identifyingString";

        $sql .= " ORDER BY $column $order" ;
        $sql .= " LIMIT $limit " ;
        if($startingId === 0) $sql .= " OFFSET $offset";

        $result = $this->retrieve($type, array(),array(), $sql);
        return !is_array($result) ? array() : $result;
    }









    public function create($type,array $keys,array $raw_values): bool {
        if(!DbConnect::getTable($type)) return false;
        $this->db_link();
        $table = DbConnect::getTable($type);
        $queries = $values = array();
        for($i = 0; $i<count($keys);$i++)
            array_push($queries,"?");

        if(!$this->isAssoc($raw_values))
            $values = $raw_values;
        else {
            foreach ($raw_values as $v)
                array_push($values,$v);
        }
        $sql = "INSERT INTO $table (".implode(", ",$keys).") VALUES(".implode(",",$queries).")";


        if(self::$db === null) $link = DbConnect::link();
        else $link = self::$db;

        try {
            $query = $link->prepare($sql);
            $query->execute($values);
        } catch (\PDOException $e) {
            file_put_contents(ERR_LOG, $e->getMessage() . PHP_EOL, 8);
            return 0;
        }



        return $query->rowCount() > 0;
    }



    public function update($type,array $keys = array(),array $values = array(),$identifier = array(), string $customSql = ""): bool|int|string {
        if(!DbConnect::getTable($type)) return false;
        $this->db_link();
        $table = DbConnect::getTable($type);
        if((empty($keys) || empty($values) || empty($identifier)) && empty($customSql)) return false;

        if(empty($customSql)) {
            $queries = $ids = array();
            for($i = 0; $i<count($keys);$i++)
                array_push($queries,$keys[$i]."=:".$keys[$i]);
            foreach ($identifier as $k => $id) {
                if(gettype($id) === "integer")
                    array_push($ids,$k."=".$id);
                else {
                    array_push($ids,$k."='".$id."'");
                }
            }
            $sql = "UPDATE $table SET ".implode(", ",$queries)." WHERE ".implode(" AND ",$ids);
        }
        else $sql = $customSql;



        try{
            if(self::$db === null) $link = DbConnect::link();
            else $link = self::$db;

            $query = $link->prepare($sql);
            $query->execute($values);

            return $query->rowCount();
        } catch (PDOException $e) {

            return $e->getMessage();
        }
    }

    public function check(string $type, array $params, string $sql = ""): bool|int {
        if(!DbConnect::getTable($type)) return false;
        $this->db_link();
        $table = DbConnect::getTable($type);
        $select = ""; $i = 1;

        if(empty($sql)) {
            foreach ($params as $param=>$value) {
                $select .= $param."='".$value."'";
                $select .= $i < count($params) ? " AND " : "";
                $i++;
            }

            $sql = "SELECT * FROM $table WHERE $select";
        }

        if(self::$db === null) $link = DbConnect::link();
        else $link = self::$db;

        $query = $link->query($sql);

        return $query->rowCount();
    }

    public function delete(string $type,array $identifier, string $customSql = ""): bool|int {
        if(!DbConnect::getTable($type)) return false;
        if(count($identifier) < 1 && empty($customSql)) return false;

        $this->db_link();
        $table = DbConnect::getTable($type);
        $selector = $binder = array();


        if(empty($customSql)) {
            foreach ($identifier as $key=>$value) {
                $selector[] = "$key=:$key";
                $binder[(":".$key)] = $value;
            }
            $sql = "DELETE FROM $table WHERE ".(implode(" AND ",$selector));
        }
        else $sql = $customSql;


//        if(empty($customSql)) {
//            $ids = array();
//            foreach ($identifier as $k => $id) {
//                if(gettype($id) === "integer")
//                    array_push($ids,$k."=".$id);
//                else
//                    array_push($ids,$k."='".$id."'");
//            }
//            $sql = "DELETE FROM $table WHERE ".implode(" AND ",$ids);
//        }
//        else $sql = $customSql;

        if(self::$db === null) $link = DbConnect::link();
        else $link = self::$db;

        $stmt = $link->prepare($sql);
        $stmt->execute($binder);

        $this->closeConnection();
        return $stmt->rowCount();
    }


    function sortByKey(&$arr,$key = "", $ascending = false, array $specialReplacement = array(), array $splitReplace = array(), $key2 = "") {
        if(empty($arr)) return;

        $this->sorting["ascending"] = $ascending; $this->sorting["key"] = $key; $this->sorting["key_2"] = $key2;
        $this->sorting["replacement"] = $specialReplacement; $this->sorting["splitReplacement"] = $splitReplace;
        usort($arr, array($this,"sortAscDescByKey"));
    }

    private function sortAscDescByKey($a, $b): int {
        $useKey = !empty($this->sorting["key"]);
        $useKey2 = !empty($this->sorting["key_2"]);
        if($useKey && is_array($a) && is_array($b) &&
            (!array_key_exists($this->sorting["key"],$a) || !array_key_exists($this->sorting["key"],$b))) return 0;

        $valueA = $useKey ? $a[$this->sorting["key"]] : $a;
        $valueB = $useKey ? $b[$this->sorting["key"]] : $b;

        $valueA = $useKey2 ? $valueA[$this->sorting["key_2"]] : $valueA;
        $valueB = $useKey2 ? $valueB[$this->sorting["key_2"]] : $valueB;

        if(!empty($this->sorting["replacement"]) && count($this->sorting["replacement"]) === 2) {
            $valueA = str_replace($this->sorting["replacement"][0],$this->sorting["replacement"][1],$valueA);
            $valueB = str_replace($this->sorting["replacement"][0],$this->sorting["replacement"][1],$valueB);
        }
        elseif(!empty($this->sorting["splitReplacement"]) && count($this->sorting["splitReplacement"]) === 2) {
            $valueA = (explode($this->sorting["splitReplacement"][0], $valueA))[ ($this->sorting["splitReplacement"][1]) ];
            $valueB = (explode($this->sorting["splitReplacement"][0], $valueB))[ ($this->sorting["splitReplacement"][1]) ];
        }

        if ((int)$valueA === (int)$valueB) return 0;
        return ((int)$valueA > (int)$valueB) ? ($this->sorting["ascending"] ? 1 : -1) : ($this->sorting["ascending"] ? -1 : 1);
    }



    public function hasAccess(string $type, string $name, int $actionType, string|int $requestingLevel): bool {
        if(!isset($this->AP)) $this->AP = $this->accessPoints();

        $specs = array("type" => $type, "name" => $name, "action_level" => $actionType);
        if(!$this->AP->exists($specs)) return false;

        $authorizedLevels = $this->AP->accessLevels($specs);
        if(empty($authorizedLevels)) return true;

        return in_array($requestingLevel,$authorizedLevels);
    }

    public function accessDepth(string|int $userId = 0): string {
        $accessLevel = $this->user()->accessLevel($userId);
        if($accessLevel === 0) return "";

        return $this->userRoles($accessLevel)->depth();
    }

    public function csvCreator(string $path, array $content, array $keys = array()): bool {
        if(file_exists(ROOT . $path)) unlink(ROOT . $path);
        $file = fopen(ROOT . $path,"w");

        if(!empty($keys)) fputcsv($file, $keys);
        foreach ($content as $line) fputcsv($file, $line);

        fclose($file);
        return true;
    }



    public function resolveMultiParamsDepth(array $params, array $depthParams, array $keys, bool $depthAssociative = true): ?array {
        if(empty($depthParams) || empty($keys)) return $params;

        foreach($keys as $key) {
            if($depthAssociative) {
                if(array_key_exists($key, $depthParams)) {
                    if(array_key_exists($key,$params)) {
                        if(is_array($params[$key]))
                            if(!empty(array_diff($params[$key], $depthParams[$key]))) return null;
                            else if(!in_array($params[$key], $depthParams[$key])) return null;
                    }
                    else $params[$key] = $depthParams[$key];
                }
            } else {
                if(array_key_exists($key,$params)) {
                    if(is_array($params[$key])) {
                        if(!empty(array_diff($params[$key], $depthParams))) return null;
                    }
                    else if(!in_array($params[$key], $depthParams)) return null;
                }
                else $params[$key] = $depthParams;
            }
        }

        return $params;
    }


    public function nestedArray(?array $targetObject, array $keys, mixed $defaultReturnKey = null): mixed {
        if(empty($keys) || empty($targetObject)) return $defaultReturnKey;

        $loop = $targetObject;
        foreach ($keys as $key) {
            if(!is_array($loop) || !array_key_exists($key, $loop)) return $defaultReturnKey;
            $loop = $loop[$key];
        }

        return $loop;
    }


    public function enforceDataType(mixed $value, string $type, bool $abs = false): mixed {
        return match ($type) {
            default => $value,
            "int" => $abs ? abs((int)$value) : (int)$value,
            "float" => $abs ? abs((float)str_replace(",", ".", $value)) : (float)str_replace(",", ".", $value),
            "string" => (string)$value,
            "bool" => (string)$value === "true" || (int)$value === 1,
            "array" => !is_array($value) ? json_decode($value, true) : $value,
            "string|int" => !is_numeric($value) ? (string)$value : ($abs ? abs((int)$value) : (int)$value)
        };
    }

    public function confirmDataType(mixed $value, string $type): bool {
        return match ($type) {
            default => false,
            "int" => is_int($value),
            "float" => is_float($value),
            "string" => is_string($value),
            "bool" => is_bool($value),
            "array" => is_array($value),
            "numeric" => is_numeric($value),
            "string|int", "int|string" => is_int($value) || is_string($value),
            "float|int", "int|float" => is_int($value) || is_float($value),
            "null" => is_null($value)
        };
    }





    public function isCreator(string|int $accessLevel = 0, bool $strict = true): bool { return $this->userRoles()->isCreator($accessLevel, $strict); }
    public function isBrand(string|int $accessLevel = 0, bool $strict = true): bool { return $this->userRoles()->isBrand($accessLevel, $strict); }
    public function isAdmin(string|int $accessLevel = 0): bool { return $this->userRoles()->isAdmin($accessLevel); }
    public function isGuest(string|int $accessLevel = 0): bool { return $this->userRoles()->isGuest($accessLevel); }
    public function isBrandTester(string|int $accessLevel = 0): bool { return $this->userRoles()->isBrandTester($accessLevel); }
    public function isCreatorTester(string|int $accessLevel = 0): bool { return $this->userRoles()->isCreatorTester($accessLevel); }
    public function registrationIsComplete(): bool { return $this->user()->registrationIsComplete(); }
    public function integrationUnderway(): bool { return $this->user()->integrationUnderway(); }
    public function creatorId(): string|int { return $this->user()->creatorId(); }




    #[Pure] public function httpRequest(array $postParams = array(), array $headers = array()): Request { return new Request($postParams, $headers); }
    #[Pure] public function activityLogging(): ActivityTracker { return new ActivityTracker($this); }
    #[Pure] public function appMeta(): AppMeta { return new AppMeta($this); }
    #[Pure] public function accessPoints(array $specs = array()): AccessPoints { return new AccessPoints($this, $specs); }
    #[Pure] public function misc(): Misc { return new Misc($this); }
    #[Pure] public function mediaStream(): MediaStream { return new MediaStream($this); }
    #[Pure] public function pwdReset(): PasswordResets { return new PasswordResets($this); }
    #[Pure] public function auth(): Auth { return new Auth($this); }
    #[Pure] public function notificationHandler(): NotificationHandler { return new NotificationHandler(); }
    public function hashTable(string $tablePath = ""): HashTable { return new HashTable($tablePath); }
    public function user(string|int $userId = 0): User { return new User($this, $userId); }
    public function userRoles(string|int $accessLevel = 0): UserRoles { return new UserRoles($this, $accessLevel); }
    public function registerUser(array $params = array()): RegisterUser { return new RegisterUser($this,$params); }
    public function connectUser(array $params = array()): ConnectUser { return new ConnectUser($this,$params); }

    #[Pure] public function scraper(string|array $postParams = array(), array $headers = array()): Scraper { return new Scraper($this, $postParams, $headers); }
    #[Pure] public function dataHandler(): DataHandler { return new DataHandler($this); }
    #[Pure] public function handler(): Handler{ return new Handler($this); }
    #[Pure] public function lookupList(): LookupList{ return new LookupList($this); }
    #[Pure] public function mediaLookup(): LookupMedia{ return new LookupMedia($this); }
    #[Pure] public function campaigns(): Campaigns{ return new Campaigns($this); }
    #[Pure] public function campaignRelations(): CampaignRelations{ return new CampaignRelations($this); }
    #[Pure] public function creatorRelations(): CreatorRelations{ return new CreatorRelations($this); }
    #[Pure] public function accountAnalytics(): AccountAnalytics { return new AccountAnalytics($this); }
    #[Pure] public function cronRequestHandler(): CronRequestHandler { return new CronRequestHandler($this); }
    #[Pure] public function integrations(): Integrations { return new Integrations($this); }
    #[Pure] public function conversations(): Conversations { return new Conversations($this); }
    #[Pure] public function messages(): Messages { return new Messages($this); }

}