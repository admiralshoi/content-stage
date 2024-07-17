<?php
namespace classes\src\Db;
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
use PDO;
USE PDOException;

class DbConnect {
    static public array $table = array(
        "user" => "users",
        "activity_log" => "activity_log",
        "access" => "access_points",
        "roles" => "user_roles",
        "external" => "external_access",
        "error" => "errors",
        "meta" => "app_meta",
        "integration" => "integrations",
        "package" => "packages",
        "order" => "orders",
        "customer" => "customers",
        "pwd" => "password_resets",
        "notification" => "notifications",
        "ig_media" => "medias",
        "media_analytics" => "media_analytics",
        "account_analytics" => "account_analytics",
        "cronJob" => "cronjob",
        "categories" => "categories",
        "cookie" => "http_cookies",
        "media" => "lookup_media",
        "lookup_list" => "lookup_list",
        "campaigns" => "campaigns",
        "campaign_relations" => "campaign_relations",
        "creator_relations" => "creator_relations",
        "conversations" => "conversations",
        "messages" => "messages",
    );

    public static function getTable($prefix){
        return !array_key_exists($prefix,self::$table) ? false : self::$table[$prefix];
    }

    static public string $error = "";

    public static function link() {
        try{
            $db_conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
            $db_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $db_conn;
        } catch (PDOException $e) {
            self::$error = $e->getMessage();
            return null;
        }
    }

}