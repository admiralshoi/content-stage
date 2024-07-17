<?php

namespace classes\src\Object\Messaging;

use classes\src\AbstractCrudObject;
use classes\src\Media\Medias;

class Messages {


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
        return $this->crud->retrieve("messages",$params, $fields,$customSql);
    }
    public  function create(array $params): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        if(!array_key_exists("created_at", $params)) $params["created_at"] = time();
        return $this->crud->create("messages", array_keys($params), $params);
    }
    public function update(array $params, array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        if(!array_key_exists("updated_at", $params)) $params["updated_at"] = time();
        return $this->crud->update("messages", array_keys($params), $params, $identifier);
    }
    public  function delete(array $identifier): bool {
        if(!$this->access(MODIFY_ACTION)) return false;
        return $this->crud->delete("messages", $identifier);
    }
    public function get(string|int $id, array $fields = array()): array {
        $item = $this->getByX(array("id" => $id), $fields);
        return array_key_exists(0, $item) ? $item[0] : $item;
    }


    public function getByMessageId(string|int $id, array $fields = array()): array {
        $item = $this->getByX(array("mid" => $id), $fields);
        return array_key_exists(0, $item) ? $item[0] : $item;
    }






    /**
     * @param array $cursor
     * @return array
     *
     *
     * Should be loaded through Conversations > loadConversationMessages()
     */
    public function getNewMessages(array $cursor): array {
        $conversationId = (int)$cursor["conversation_id"];
        $upperBoundary = (int)$cursor["upper_boundary"];
        $columnKey = $cursor["key"];
        $limit = (int)$cursor["limit"];
        $sortOrder = empty($upperBoundary) ? "DESC" : "ASC";


        $sql = "SELECT * FROM messages WHERE `conversation_id` = $conversationId";
        if(!empty($upperBoundary)) $sql .= " AND `$columnKey` > $upperBoundary";
        $sql .= " ORDER BY `$columnKey` $sortOrder LIMIT $limit";
        $rows = $this->getByX([], [], $sql);
        if(empty($rows)) return [];

        $this->crud->sortByKey($rows, $columnKey, true);
        $cursor["lower_boundary"] = $rows[0][$columnKey];
        $cursor["upper_boundary"] = $rows[ (count($rows) -1) ][$columnKey];


        return [
            "cursor" => $this->crud->encodeCursor($cursor),
            "data" => $rows
        ];
    }



    public function sendSocialMessage(array $data): array {
        $provider = "instagram";
        $mediaHandler = new Medias();
        $mediaHandler->init($provider);

        $response = $mediaHandler->sendMessage($data["access_token"], $data["recipient_id"], $data["message"]);
        file_put_contents(TESTLOGS . "message_send.json", json_encode($response, JSON_PRETTY_PRINT));

        if(!array_key_exists("message_id", $response))
            return ["status" => "error", "error" => ["message" => "Failed to send $provider message", "error" => $response]];

        $messageId = $response["message_id"];
        $integration = $this->crud->integrations()->getMyIntegration();

        $this->create([
            "mid" => $messageId,
            "recipient_id" => $data["recipient_id"],
            "sender_id" => $integration["item_id"],
            "text" => $data["message"],
            "timestamp" => $data["timestamp"],
            "type" => "message",
            "outbound" => 1,
            "conversation_id" => $data["conversation_id"],
        ]);

        return ["status" => "success", "message" => "Message sent"];
    }









}