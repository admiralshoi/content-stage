<?php

namespace classes\src\Object\objects;
use classes\src\AbstractCrudObject;
use classes\src\Enum\DesignPaths;
use classes\src\Enum\NotificationItems as ITEMS;

class NotificationHandler extends Notifications {

    //---------------------------- CORE METHODS START -----------------------------------------//

    private function setValuesTimestamp(array &$values):void { $values["timestamp"] = time(); }
    private function validateParams(array &$values, array $params, array $requiredItems, array $optionalItems, array $additionAllowedKeys): bool {
        foreach ($optionalItems as $optionalItem) if(array_key_exists($optionalItem, $params)) $values[$optionalItem] = $params[$optionalItem];
        foreach ($requiredItems as $requiredItem) {
            if (!array_key_exists($requiredItem, $params)) return false;
            $values[$requiredItem] = $params[$requiredItem];
        }

        $allowedKeys = array_merge($requiredItems, $optionalItems, $additionAllowedKeys);
        foreach (array_keys($values) as $valueKey) if(!in_array($valueKey, $allowedKeys)) return false;

        $this->setValuesTimestamp($values);
        return true;
    }


    private function exec(AbstractCrudObject $crud, array $values): bool {
        $this->prepare($crud, $values);
        return parent::execute();
    }
    private function prepare(AbstractCrudObject $crud, array $values): void {
        parent::setCrud($crud);
        $this->setValues($values);
    }

    private function setValues(array $values): void {
        parent::initNewNotification(array(
            "type" => $values["type"],
            "recipient_id" => $values["uid"],
            "push_type" => $values["push_type"],
            "ref" => $values["ref"],
        ));
        parent::setContent($values);
        parent::setReflectionClassName(get_parent_class());
    }


    //---------------------------- CORE METHODS END -----------------------------------------//

    //---------------------------- VIEW METHODS START -----------------------------------------//

    public function getNode(string $node = "", array $row = array()): array {
        if(empty($node) && !array_key_exists("node", $row)) return array();
        if(empty($node)) $node = $row["node"];
        if(!file_exists(ROOT . $node)) return array();
        return json_decode(file_get_contents(ROOT . $node), true);
    }

    public function resolveLargeIcon(array $iconDetails): string {
        if(empty($iconDetails)) return "";
        if(!array_key_exists("type", $iconDetails) || !array_key_exists("id", $iconDetails)) return "";

        if($iconDetails["type"] === "default") return DesignPaths::returnByName($iconDetails["id"]);
        if($iconDetails["type"] === "profile_picture") {
            if(!isset(self::$crud)) self::$crud = new AbstractCrudObject();
            $userHandler = self::$crud->user();
            $userHandler->doIgnoreSuspensions();
            return $userHandler->getProfilePicture($iconDetails["id"]);
        }
        return "";
    }


    //---------------------------- VIEW METHODS END -----------------------------------------//



    //---------------------------- NOTIFICATION METHODS START -----------------------------------------//


    /**  PASSWORD RESET */
    public function pwdReset(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::PWD_RESET_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::PWD_RESET_REQUIRED, ITEMS::PWD_RESET_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  WELCOME EMAIL */
    public function welcomeEmail(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::WELCOME_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::WELCOME_REQUIRED, ITEMS::WELCOME_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  ACCOUNT SUSPENSIONS */
    public function accountSuspension(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::SUSPENSION_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::SUSPENSION_REQUIRED, ITEMS::SUSPENSION_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  NEW ORDER */
    public function newOrder(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::NEW_ORDER_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::NEW_ORDER_REQUIRED, ITEMS::NEW_ORDER_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  CANCEL ORDER */
    public function cancelOrder(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::CANCEL_ORDER_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::CANCEL_ORDER_REQUIRED, ITEMS::CANCEL_ORDER_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  ORDER CHARGEBACK */
    public function orderChargeback(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::ORDER_CHARGEBACK_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::ORDER_CHARGEBACK_REQUIRED, ITEMS::ORDER_CHARGEBACK_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  ORDER REOPEN */
    public function orderReopened(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::ORDER_REOPEN_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::ORDER_REOPEN_REQUIRED, ITEMS::ORDER_REOPEN_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  ORDER COMPLETION */
    public function orderCompletion(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::ORDER_COMPLETED_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::ORDER_COMPLETED_REQUIRED, ITEMS::ORDER_COMPLETED_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  ORDER DELIVERY */
    public function orderDelivery(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::ORDER_DELIVERY_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::ORDER_DELIVERY_REQUIRED, ITEMS::ORDER_DELIVERY_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  ORDER LATE */
    public function orderLate(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::ORDER_LATE_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::ORDER_LATE_REQUIRED, ITEMS::ORDER_LATE_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  ORDER NEW MESSAGE */
    public function orderNewMessage(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::ORDER_NEW_MESSAGE_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::ORDER_NEW_MESSAGE_REQUIRED, ITEMS::ORDER_NEW_MESSAGE_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  ORDER DISPUTE */
    public function orderDispute(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::ORDER_DISPUTE_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::ORDER_DISPUTE_REQUIRED, ITEMS::ORDER_DISPUTE_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  ORDER DISPUTE RESOLVED */
    public function orderDisputeResolved(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::ORDER_DISPUTE_RESOLVED_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::ORDER_DISPUTE_RESOLVED_REQUIRED, ITEMS::ORDER_DISPUTE_RESOLVED_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  PACKAGE REMOVAL */
    public function packageRemoval(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::PACKAGE_RM_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::PACKAGE_RM_REQUIRED, ITEMS::PACKAGE_RM_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  PAYOUT */
    public function payout(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::PAYOUT_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::PAYOUT_REQUIRED, ITEMS::PAYOUT_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    /**  PROFILE PICTURE REMOVAL */
    public function profilePictureRemoval(AbstractCrudObject $crud, array $params): void {
        $values = ITEMS::PROFILE_PICTURE_RM_VALUES;
        if(!$this->validateParams($values, $params, ITEMS::PROFILE_PICTURE_RM_REQUIRED, ITEMS::PROFILE_PICTURE_RM_OPTIONAL, array_keys($values))) return;
        $this->exec($crud, $values);
    }


    //---------------------------- NOTIFICATION METHODS END -----------------------------------------//



}