<?php

namespace classes\src\Object\Messaging;

use classes\src\AbstractCrudObject;
use classes\src\Object\transformer\Titles;

class Conversations {


    private AbstractCrudObject $crud;
    private int $requestingUsersAccessLevel = 0;
    private int $requestingUsersId = 0;
    private bool $disabledDepthCheck = false;
    public bool $isError = true;


    function __construct(AbstractCrudObject $crud) {
        $this->crud = $crud;
        if(isset($_SESSION["access_level"])) $this->requestingUsersAccessLevel = $_SESSION["access_level"];
        if(isset($_SESSION["uid"])) $this->requestingUsersId = $_SESSION["uid"];
    }








    private function access(int $actionType): bool {
        if($this->disabledDepthCheck) return true;
        return $this->crud->hasAccess("node","conversations",$actionType, $this->requestingUsersAccessLevel);
    }

    public function disableDepthCheck(): static { $this->disabledDepthCheck = true; return $this; }
    public function enableDepthCheck(): static { $this->disabledDepthCheck = false; return $this; }



    /*
     * Core CRUD features END
     */

    public function getByX(array $params = array(), array $fields = array(), string $customSql = ""): array {
        if(!$this->access(READ_ACTION)) return array();
        return $this->crud->retrieve("conversations",$params, $fields,$customSql);
    }
    public  function create(array $params): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        if(!array_key_exists("created_at", $params)) $params["created_at"] = time();
        return $this->crud->create("conversations", array_keys($params), $params);
    }
    public function update(array $params, array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        return $this->crud->update("conversations", array_keys($params), $params, $identifier);
    }
    public  function delete(array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        return $this->crud->delete("conversations", $identifier);
    }
    public function get(string|int $id, array $fields = array()): array {
        $item = $this->getByX(array("id" => $id), $fields);
        return array_key_exists(0, $item) ? $item[0] : $item;
    }



    public function createOrUpdate(array $data): ?int {
        $rowParams = [
            "provider" => $data["provider"],
            "participant_username" => $data["participant_username"],
            "participant_id" => $data["participant_id"],
            "owner_user_id" => $data["owner_user_id"],
        ];
        $row = $this->getByX($rowParams);
        if(!empty($row)) {
            $rowId = $row[0]["id"];
            $params = [
                "last_message_at" => $data["last_message_at"],
                "text_short" => $data["text_short"],
                "last_message_outbound" => $data["last_message_outbound"],
            ];
            if(!empty($data["profile_picture"])) $params["profile_picture"] = $data["profile_picture"];
            $this->update($params, ["id" => $rowId]);
            return (int)$rowId;
        }

        $this->create($data);

        usleep(1000);
        $row = $this->getByX($data);
        return !empty($row) ? (int)$row[0]["id"] : null;
    }



    public function loadUserConversations(array $args): array {
        if(!array_key_exists("cursor", $args) || empty($args["cursor"])) {
            $cursor = [
                "owner_user_id" => $this->requestingUsersId,
                "upper_boundary" => 0,
                "lower_boundary" => 0,
                "limit" => 10,
                "key" => "last_message_at"
            ];
        }
        else {
            $cursor = $this->crud->decodeCursor($args["cursor"]);
            foreach (["owner_user_id", "limit", "upper_boundary", "lower_boundary", "key"] as $key) if (!array_key_exists($key, $cursor)) return [];
        }



        $ownerUserId = $cursor["owner_user_id"];
        $upperBoundary = (int)$cursor["upper_boundary"];
        $columnKey = $cursor["key"];
        $limit = (int)$cursor["limit"];
        $sortOrder = empty($upperBoundary) ? "DESC" : "ASC";


        $sql = "SELECT * FROM conversations WHERE `owner_user_id` = $ownerUserId";
        if(!empty($upperBoundary)) $sql .= " AND `$columnKey` > $upperBoundary";
        $sql .= " ORDER BY `$columnKey` $sortOrder LIMIT $limit";
        $rows = $this->getByX([], [], $sql);
        if(empty($rows)) return [];

        $this->crud->sortByKey($rows, $columnKey);
        $cursor["upper_boundary"] = $rows[0][$columnKey];
        $cursor["lower_boundary"] = $rows[ (count($rows) -1) ][$columnKey];


        $lookupHandler = $this->crud->lookupList();
        $collector = [];

        foreach ($rows as $row) {
            $conversationId = $row["id"];
            $username = $row["participant_username"];
            if(empty($row["profile_picture"])) {
                $creatorData = $lookupHandler->getByUsername($username);
                if(empty($creatorData)) $profilePicture = IMAGE_PATH . USER_NO_PB;
                else $profilePicture = $creatorData["profile_picture"];
            }
            else $profilePicture = $row["profile_picture"];

            $collector[$conversationId] = [
                "conversation_id" => $conversationId,
                "profile_picture" => $profilePicture,
                "username" => $username,
                "name" => $row["participant_name"],
                "timestamp" => $row["last_message_at"],
                "short_text" => Titles::truncateStr(((int)$row["last_message_outbound"] ? "You: " : $row["participant_name"] .": ") . $row["text_short"], 30),
            ];
        }

        return [
            "cursor" => $this->crud->encodeCursor($cursor),
            "data" => $collector
        ];

    }



    public function loadConversationMessages(array $args): array {

        if(!array_key_exists("cursor", $args) || empty($args["cursor"])) {
            if (!array_key_exists("conversation_id", $args)) return [];
            $conversationId = (int)$args["conversation_id"];
            $row = $this->get($conversationId);
            if(empty($row) || (int)$row["owner_user_id"] !== $this->requestingUsersId) return [];

            $cursor = [
                "conversation_id" => $conversationId,
                "upper_boundary" => 0,
                "lower_boundary" => 0,
                "limit" => 10,
                "key" => "id"
            ];
        }
        else {
            $cursor = $this->crud->decodeCursor($args["cursor"]);
            foreach (["limit", "conversation_id", "upper_boundary", "lower_boundary", "key"] as $key) if (!array_key_exists($key, $cursor)) return [];
        }


        return $this->crud->messages()->getNewMessages($cursor);
    }





    public function sendNewMessage(array $args): array {
        if(!$this->access(MODIFY_ACTION)) return ["status" => "error", "error" => ["message" => "Unauthorized"]];
        foreach (["conversation_id", "message"] as $key) if(!array_key_exists($key, $args))
            return ["status" => "error", "error" => ["message" => "Missing key: $key"]];

        $text = trim($args["message"]);
        $conversationId = trim($args["conversation_id"]);
        $conversation = $this->get($conversationId);
        if(empty($conversation) || (int)$conversation["owner_user_id"] !== $this->requestingUsersId) return ["status" => "error", "error" => ["message" => "Unauthorized"]];


        $integrationHandler = $this->crud->integrations();
        $integration = $integrationHandler->getMyIntegration(0, [], "facebook");
        if(empty($integration)) return ["status" => "error", "error" => ["message" => "Missing integration"]];


        $timestamp = time();
        $response = $this->crud->messages()->sendSocialMessage([
            "conversation_id" => $conversation['id'],
            "access_token" => $integration['item_token'],
            "recipient_id" => $conversation['participant_id'],
            "message" => $text,
            "timestamp" => $timestamp
        ]);

        if($response["status"] === "error") return $response;
        $this->update([
            "last_message_at" => $timestamp,
            "text_short" => Titles::truncateStr($text, 20),
            "last_message_outbound" => 1
        ], ["id" => $conversationId]);
        return $response;
    }











}