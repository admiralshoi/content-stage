<?php

namespace classes\src\Object\objects;
use classes\src\AbstractCrudObject;
use classes\src\Enum\PushTypes as PUSHTYPE;
use classes\src\Enum\EmailTypes as EMAILTYPE;
use classes\src\Enum\NotificationTypes;
use ReflectionClass;

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();


class Notifications {
    protected static AbstractCrudObject $crud;


    private static string $reflectionClassName;
    private static string $type;
    private static string $ref;
    private static string $pushType;
    private static string|int $recipientId;
    private static array $nodeContent;
    private static string $emailTemplate;
    private static string $nodeDirectory;
    private static string $nodePath;
    private static ?string $nId;

    protected static function update(array $params, array $identifier): bool {
        $params["last_updated"] = time();
        return self::$crud->update("notification", array_keys($params), $params, $identifier);
    }
    public static function get(string|int $nId): array {$row = self::getByX(array("nid" => $nId)); return array_key_exists(0,$row)?$row[0]:$row;}
    public static function getByX(array $params = array(), array $fields = array(), string $sql = ""): array {return self::$crud->retrieve("notification",$params,$fields,$sql);}
    protected static function exists(string $nId): bool { return !empty(self::$crud?->retrieve("notification", array("nid" => $nId))); }
    public static function setCrud(AbstractCrudObject $crud): void {self::$crud = $crud;}
    protected static function setContent(array $content): void {self::$nodeContent = $content;}
    protected static function setReflectionClassName(string $className): void {self::$reflectionClassName = $className;}

    protected static function mayReceiveNotification(int $pushType): bool {
        $row = self::getByX(array("recipient_id" => self::$recipientId, "type" => self::$type, "ref" => self::$ref));
        if(empty($row)) return true;

        self::$crud->sortByKey($row, "created_at");
        $lastCreationTimestamp = self::$crud->nestedArray($row, array(0, "created_at"), 0);
        return NotificationTypes::delayIsOk($pushType, $lastCreationTimestamp, time());
    }

    protected static function setEmailTemplate(?User $userHandler = null): void {
        if(!isset(self::$crud)) return;
        if(!isset($userHandler)) {
            $userHandler = self::$crud->user();
            $userHandler->disableDepthCheck();
        }
        $userHandler->doIgnoreSuspensions();
        $accessLevel = $userHandler->accessLevel(self::$recipientId);
        self::$emailTemplate = EMAILTYPE::getTemplate(self::$type, self::$crud->userRoles($accessLevel)->name());
    }


    protected static function initNewNotification(array $args): bool {
        foreach (array(
            "type",
            "recipient_id",
            "ref",
         ) as $key) if(!array_key_exists($key, $args)) return false;

        NotificationTypes::setType($args["type"]);
        if(!NotificationTypes::typeIsValid()) return false;

        $userHandler = self::$crud->user();
        $userHandler->doIgnoreSuspensions();
        $userHandler->disableDepthCheck();
        if(!$userHandler->exists($args["recipient_id"])) return false;

        self::$ref = $args["ref"];
        self::$type = $args["type"];
        self::$recipientId = $args["recipient_id"];
        self::$pushType = array_key_exists("push_type", $args) ? $args["push_type"] : PUSHTYPE::BOTH;
        self::$nodeDirectory = "users/" . $args["recipient_id"] . "/notifications/";

        if(!self::mayReceiveNotification(self::$pushType)) return false;
        self::setEmailTemplate($userHandler);

        while (true) {
            self::$nodePath = self::$nodeDirectory . md5(rand(10,10000) . time() . "_" . self::$recipientId) . ".json";
            if(!file_exists(ROOT . self::$nodePath)) break;
        }

        return true;
    }



    protected static function execute(): bool {
        if(!isset(self::$nodeDirectory, self::$nodeContent, self::$crud, self::$pushType, self::$recipientId, self::$type)) return false;
        if(in_array(self::$pushType, array(PUSHTYPE::EMAIL, PUSHTYPE::BOTH)) && empty(self::$emailTemplate)) return false;

        if(!is_dir(ROOT . self::$nodeDirectory)) mkdir(ROOT . self::$nodeDirectory);
        if(!is_dir(ROOT . self::$nodeDirectory)) return false;
        if(!is_null(self::$emailTemplate) && !class_exists(self::$emailTemplate)) return false;
        if(!self::mayReceiveNotification(0)) return false;

        if(!self::mayReceiveNotification(1)) self::$pushType = PUSHTYPE::PLATFORM; //If cant receive email, then force platform
        file_put_contents(ROOT . self::$nodePath, json_encode(self::$nodeContent, JSON_PRETTY_PRINT));

        self::$nId = self::create(array(
            "recipient_id" => self::$recipientId,
            "ref" => self::$ref,
            "type" => self::$type,
            "push_type" => self::$pushType,
            "node" => self::$nodePath,
            "is_read" => (self::$pushType == PUSHTYPE::EMAIL ? 1 : 0),
        ));


        if(is_null(self::$nId)) return false;
        if(self::$pushType === PUSHTYPE::PLATFORM) return true;

        $emailHandler = new self::$emailTemplate(self::$crud);
        if($emailHandler->set(self::getClassProperties(self::$reflectionClassName))){
            $emailHandler->execute();
            return self::update(array("email_sent" => 1), array("nid" => self::$nId));
        }
        return false;
    }


    private static function create(array $params): ?string {
        if(!isset(self::$crud)) return null;
        foreach (array("recipient_id", "type", "push_type", "node") as $key) if(!array_key_exists($key, $params)) return null;

        $params["created_at"] = time();
        $params["email_sent"] = 0;

        while(true) {
            $params["nid"] = md5(crc32(rand(25,390032) . "_" . $params["created_at"]));
            if(!self::exists($params["nid"])) break;
        }

        return !self::$crud->create("notification", array_keys($params), $params) ? null : $params["nid"];
    }

    protected static function getClassProperties(string $className): ?array {
        $reflectionClass = new ReflectionClass($className);
        return $reflectionClass->getStaticProperties();
    }


    public static function setIsRead(string|array $nIds): void {
        if(!is_array($nIds)) $nIds = [$nIds];
        if(!isset(self::$crud)) self::$crud = new AbstractCrudObject();
        foreach ($nIds as $nId) self::update(array("is_read" => 1), array("nid" => $nId));
    }






}