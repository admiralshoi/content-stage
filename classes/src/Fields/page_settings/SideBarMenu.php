<?php
namespace classes\src\Fields\page_settings;
use classes\src\AbstractCrudObject;


class SideBarMenu {


    public function sideBarAccess(string|int $loggedIn): array {
        $loggedIn = (int)$loggedIn;
        $accessPoints = (new AbstractCrudObject())->accessPoints();
        $menus = array(
            0 => array(), //Not logged in
            1 => array( // Logged in
                "admin" => array(
                    "access_level" => $accessPoints->accessLevels(array("type" => "content", "name" => "admin_menu")),
                    "pathName" => "admin",
                    "show_title" => false
                ),
                "system_admin" => array(
                    "access_level" => $accessPoints->accessLevels(array("type" => "content", "name" => "developer_menu")),
                    "pathName" => "developer",
                    "show_title" => false
                ),
                "creator" => array(
                    "access_level" => $accessPoints->accessLevels(array("type" => "content", "name" => "creator_menu")),
                    "pathName" => "creator",
                    "show_title" => false
                ),
                "brand" => array(
                    "access_level" => $accessPoints->accessLevels(array("type" => "content", "name" => "brand_menu")),
                    "pathName" => "brand",
                    "show_title" => false
                )
            )
        );

        return array_key_exists($loggedIn,$menus) ? $menus[$loggedIn] : array();
    }


    public function sideBarLinks($barName){
        $accessPoints = (new AbstractCrudObject())->accessPoints();
        $pointList = $accessPoints->getByX(array("type" => "content"));
        $sideBarLinks = array(
            "developer" => array(
//                "developer" => array(
//                    "link" => "?page=developer",
//                    "title" => "Developer",
//                    "data-value" => "market",
//                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_developer"), $pointList)
//                ),
            ),
            "creator" => array(
                "home" => array(
                    "link" => "?",
                    "title" => "Profile",
                    "data-value" => "market",
                    "icon-class" => "mdi mdi-home",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_home"), $pointList)
                ),
                "campaigns" => array(
                    "link" => "?page=campaigns",
                    "title" => "Campaigns",
                    "data-value" => "wallet",
                    "icon-class" => "mdi mdi-bullhorn",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_campaigns"), $pointList)
                ),
                "integrations" => array(
                    "link" => "?page=integrations",
                    "title" => "Integrations",
                    "data-value" => "orders",
                    "icon-class" => "mdi mdi-math-integral-box",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_integrations"), $pointList)
                ),
//                "settings" => array(
//                    "link" => "?page=settings",
//                    "title" => "Settings",
//                    "data-value" => "settings",
//                    "icon-class" => "mdi mdi-cog",
//                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_settings"), $pointList)
//                ),
                "logout" => array(
                    "link" => "?logout",
                    "title" => "Logout",
                    "data-value" => "settings",
                    "icon-class" => "mdi mdi-logout",
                    "access_level" => []
                ),
            ),
            "brand" => array(
                "home" => array(
                    "link" => "?",
                    "title" => "Analytics",
                    "data-value" => "market",
                    "icon-class" => "mdi mdi-home",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_home"), $pointList)
                ),
                "campaigns" => array(
                    "link" => "?page=campaigns",
                    "title" => "Campaigns",
                    "data-value" => "wallet",
                    "icon-class" => "mdi mdi-bullhorn",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_campaigns"), $pointList)
                ),
                "creators" => array(
                    "link" => "?page=creators",
                    "title" => "Creators",
                    "data-value" => "profile",
                    "icon-class" => "mdi mdi-account-star",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_creators"), $pointList)
                ),
                "my-mentions" => array(
                    "link" => "?page=my-mentions",
                    "title" => "My Mentions",
                    "data-value" => "profile",
                    "icon-class" => "mdi mdi-at",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_my_mentions"), $pointList)
                ),
                "integrations" => array(
                    "link" => "?page=integrations",
                    "title" => "Integrations",
                    "data-value" => "orders",
                    "icon-class" => "mdi mdi-math-integral-box",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_integrations"), $pointList)
                ),
//                "settings" => array(
//                    "link" => "?page=settings",
//                    "title" => "Settings",
//                    "data-value" => "settings",
//                    "icon-class" => "mdi mdi-cog",
//                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_settings"), $pointList)
//                ),
                "logout" => array(
                    "link" => "?logout",
                    "title" => "Logout",
                    "data-value" => "settings",
                    "icon-class" => "mdi mdi-logout",
                    "access_level" => []
                ),
            ),
            "admin" => array(
                "home" => array(
                    "link" => "?",
                    "title" => "Analytics",
                    "data-value" => "market",
                    "icon-class" => "mdi mdi-home",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_home"), $pointList)
                ),
                "creators" => array(
                    "link" => "?page=creators",
                    "title" => "Creators",
                    "data-value" => "profile",
                    "icon-class" => "mdi mdi-account-star",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_creators"), $pointList)
                ),
                "campaigns" => array(
                    "link" => "?page=campaigns",
                    "title" => "Campaigns",
                    "data-value" => "wallet",
                    "icon-class" => "mdi mdi-bullhorn",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_campaigns"), $pointList)
                ),
                "integrations" => array(
                    "link" => "?page=integrations",
                    "title" => "Integrations",
                    "data-value" => "orders",
                    "icon-class" => "mdi mdi-math-integral-box",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_integrations"), $pointList)
                ),
                "users" => array(
                    "link" => "?page=users",
                    "title" => "Users",
                    "data-value" => "profile",
                    "icon-class" => "mdi mdi-account-multiple",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_users"), $pointList)
                ),
                "cookie-manager" => array(
                    "link" => "?page=cookie-manager",
                    "title" => "Cookies",
                    "data-value" => "notifications",
                    "icon-class" => "mdi mdi-cookie",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_cookies"), $pointList)
                ),
                "cron-logs" => array(
                    "link" => "?page=cron-logs",
                    "title" => "Cron logs",
                    "data-value" => "wallet",
                    "icon-class" => "mdi mdi-code-parentheses",
                    "access_level" => []
                ),
                "settings" => array(
                    "link" => "?page=settings",
                    "title" => "Settings",
                    "data-value" => "settings",
                    "icon-class" => "mdi mdi-cog",
                    "access_level" => $accessPoints->getAccessLevelsFromMultiPoint(array("type" => "content", "name" => "link_settings"), $pointList)
                ),
                "logout" => array(
                    "link" => "?logout",
                    "title" => "Logout",
                    "data-value" => "settings",
                    "icon-class" => "mdi mdi-logout",
                    "access_level" => []
                ),
            )
        );

        return array_key_exists($barName,$sideBarLinks) ? $sideBarLinks[$barName] : array();
    }

}