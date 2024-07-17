<?php

namespace classes\src\Object\objects;
use classes\src\AbstractCrudObject;
use JetBrains\PhpStorm\ArrayShape;
use classes\src\Object\objects\Notifications;
use classes\src\Enum\NotificationTypes;
use classes\src\Enum\PushTypes as PUSHTYPE;

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

class PasswordResets {
    private AbstractCrudObject $crud;
    private const lifetime = (3600 * 2);

    function __construct(AbstractCrudObject $crud) {
        $this->crud = $crud;
    }




    private function create(array $args): bool {
        $args["created_at"] = time();
        $args["expires_at"] = $args["created_at"] + self::lifetime;
        return $this->crud->create("pwd", array_keys($args), $args);
    }


    public function exist(string $token): bool { return !empty($this->get($token)); }
    public function get(string $token): array {
        $row = $this->crud->retrieve("pwd", array("token" => $token));
        return array_key_exists(0, $row) ? $row[0] : $row;
    }

    public function update(array $params, array $identifier, string $sql = ""): bool {
        if ((empty($params) || empty($identifier)) && empty($sql)) return false;
        return $this->crud->update("pwd", array_keys($params), $params, $identifier, $sql) === 1;
    }



    public function createNewPwdReset(string $email): array {
        if(isset($_SESSION["pwd_reset"])) unset($_SESSION["pwd_reset"]);
        if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) return array("status" => "error", "error" => "Invalid email");
        $userHandler = $this->crud->user();

        $userHandler->disableDepthCheck();
        $user = $userHandler->getByEmail($email);
        if(empty($user)) return array("status" => "success", "message" => "Email sent"); //Dont allow users to guess random emails

        $uid = $user["uid"];
        while (true) {
            $token = md5($uid . $email . "__" . time() . "__" . rand(10,100000));
            if(!$this->exist($token)) break;
        }

        $creation = $this->create(array(
            "uid" => $uid,
            "email" => $email,
            "token" => $token,
        ));


        $this->crud->notificationHandler()->pwdReset(
            $this->crud,
            array(
                "uid" => $uid,
                "token" => $token,
                "full_name" => $userHandler->name($uid),
            )
        );
        return $creation ? array("status" => "success", "message" => "Email sent") :  array("status" => "error", "message" => "Failed the attempt to reset password. Try again later");
    }



    public function resetAvailable(string $token): bool {
        $row = $this->get($token);
        if(empty($row)) return false;

        $expiresAt = (int)$row["expires_at"];
        if(time() > $expiresAt) return false;

        if((int)$row["is_used"] === 0) {
            $_SESSION["pwd_reset"] = true;
            return true;
        }
        return false;
    }

    #[ArrayShape(["status" => "string", "message" => "string"])]
    public function createNewPassword(array $args): array {
        if(!array_key_exists("data", $args)) return array("status" => "error", "message" => "Missing fields");
        $data = $args["data"];

        foreach (array("password", "password_repeat", "token") as $key) if(!array_key_exists($key, $data)) return array("status" => "error", "message" => "Missing field $key");
        $password = $data["password"];
        $passwordRepeat = $data["password_repeat"];
        $token = $data["token"];

        if(!isset($_SESSION["pwd_reset"]) || $_SESSION["pwd_reset"] !== true) return array("status" => "error", "message" => "You do not have permission to perform this action");
        $userHandler = $this->crud->user();

        $row = $this->get($token);
        if(!$this->resetAvailable($token)) return array("status" => "error", "message" => "#2 You do not have permission to perform this action");
        if($password !== $passwordRepeat)  return array("status" => "error", "message" => "The passwords do not match. Try again");
        if(strlen($password) < 6)  return array("status" => "error", "message" => "The password must be at least 6 characters long");


        $uid = $row["uid"];
        $newPassword = $this->crud->passwordHashing($password);

        $userHandler->disableDepthCheck();
        $user = $userHandler->get($uid);
        if($user["password"] === $newPassword || $userHandler->update(array("password" => $newPassword), array("uid" => $uid))) {
            $this->update(array("is_used" => 1), array("token" => $token));
            unset($_SESSION["pwd_reset"]);
            return array("status" => "success", "message" => "Your password has been updated");
        }

        return array("status" => "error", "message" => "Failed to update password. Try again later");
    }




}