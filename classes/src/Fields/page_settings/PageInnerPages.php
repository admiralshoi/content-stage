<?php
namespace classes\src\Fields\page_settings;


class PageInnerPages {
    protected array $innerPages = array(
        "landing_wrapper_main" => array(
            "header" => array(),
            "body" => array( "landing_header", "landing_cover", "landing_body" ),
            "footer" => array(),
            "css" => array(),
            "js" => array(),
        ),
        "signin_integrations" => array(
            "header" => array(),
            "body" => array( "landing_header_black", "signin_integrations" ),
            "footer" => array(),
            "css" => array(),
            "js" => array(),
        ),
        "signin_package" => array(
            "header" => array(),
            "body" => array( "landing_header_black", "signin_package" ),
            "footer" => array(),
            "css" => array(),
            "js" => array(),
        ),
        "profile_images" => array(
            "header" => array(),
            "body" => array( "landing_header_black", "profile_images", "editor" ),
            "footer" => array(),
            "css" => array(),
            "js" => array(),
        ),
        "landing_wrapper_login" => array(
            "header" => array(),
            "body" => array( "landing_header_black", "login" ),
            "footer" => array(),
            "css" => array(),
            "js" => array(),
        ),
        "privacy_policy" => array(
            "header" => array(),
            "body" => array( "landing_header_black", "privacy_policy" ),
            "footer" => array(),
            "css" => array(),
            "js" => array(),
        ),
        "terms_of_use" => array(
            "header" => array(),
            "body" => array( "landing_header_black", "terms_of_use" ),
            "footer" => array(),
            "css" => array(),
            "js" => array(),
        ),
        "reset_password" => array(
            "header" => array(),
            "body" => array( "landing_header_black", "reset_pwd" ),
            "footer" => array(),
            "css" => array(),
            "js" => array(),
        ),
        "signup" => array(
            "header" => array(),
            "body" => array( "landing_header_black", "signup" ),
            "footer" => array(),
            "css" => array(),
            "js" => array(),
        ),
        "user_template" => array(
            "header" => array(),
            "body" => array( "left_side_bar","user_page" ),
            "footer" => array(),
            "css" => array(),
            "js" => array(),
        ),
        "user_page" => array(
            "header" => array(),
            "body" => array("PAGE_SWITCH" ),
            "footer" => array(),
            "css" => array(),
            "js" => array(),
        )
    );

    protected array $innerPagesContent = array(
        "register" => array("register"),
        "activity_logs" => array("activity_logs"),
        "general_settings" => array("general_settings"),
        "access_points" => array("access_points"),
        "user_logs" => array("user_logs"),
        "dev_admin" => array("dev_admin"),
        "errors" => array("errors"),
        "admin_dashboard" => array("admin_dashboard"),
        "creator_dashboard" => array("creator_dashboard"),
        "brand_dashboard" => array("brand_dashboard", "live-chat"),
        "notifications" => array("notifications"),
        "support" => array("support"),
        "settings" => array("settings", "editor", "live-chat"),
        "users" => array("users"),
        "lookup" => array("lookup"),
        "edit_page" => array("edit_page"),
        "integrations" => array("integrations", "live-chat"),
        "cron-logs" => array("cron-logs"),
        "cookie-manager" => array("cookie-manager"),
        "campaigns" => array("campaigns", "live-chat"),
        "my-mentions" => array("my_mentions", "live-chat"),
        "creators" => array("creators", "live-chat"),
    );


    public function getPages() {
        return $this->innerPages;
    }

    public function getPagesContent() {
        return $this->innerPagesContent;
    }
}