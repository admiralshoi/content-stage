<?php
namespace classes\src\Fields\page_settings;
use classes\src\AbstractCrudObject;

class PageSettings {
    protected PageInnerPages $innerPages;
    protected SideBarMenu $sideBar;

    public function pageSettings($page): bool|array {
        $accessPoints = (new AbstractCrudObject())->accessPoints();
        $pageSettings = array (
            "login" => array("logged_in" => false,"access_level" => array()),
            "reset_pwd" => array("logged_in" => false,"access_level" => array()),
            "signup" => array("logged_in" => false,"access_level" => array()),
            "about" => array("logged_in" => false,"access_level" => array()),
            "signin_integration" => array("logged_in" => true,"access_level" => array(1)),
            "signin_package" => array("logged_in" => true,"access_level" => array(1)),
            "privacy_policy" => array("logged_in" => null,"access_level" => array()),
            "user_agreement" => array("logged_in" => null,"access_level" => array()),
            "terms_of_use" => array("logged_in" => null,"access_level" => array()),
            "FAQ" => array("logged_in" => false,"access_level" => array()),
            "register" => array("logged_in" => false,"access_level" => array()),
            "support" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "support"))),
            "notifications" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "notifications"))),
            "settings" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "settings"))),
            "cookie-manager" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "cookies"))),
            "users" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "users"))),
            "creators" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "creators"))),
            "campaigns" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "campaigns"))),
            "my-mentions" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "my_mentions"))),
            "general_settings" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "general_settings"))),
            "activity_logs" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "activity_logs"))),
            "access_points" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "access_point"))),
            "errors" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "error"))),
            "dev_admin" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "dev_admin"))),
            "lookup" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "lookup"))),
            "user_logs" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "log"))),
            "edit_page" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "edit_page"))),
            "integrations" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "integrations"))),
            "cron-logs" => array("logged_in" => true,"access_level" => $accessPoints->accessLevels(array("type" => "page", "name" => "cron_logs"))),
        );


        if(!array_key_exists($page,$pageSettings)) return false;
        return $pageSettings[$page];
    }


    protected array $pageContents = array(
        "404" => array(
            "header" => array(),"body" => array("404"),"footer" => array(),
            "css" => array(
                "core","iconfont","flag_icon","style"
            ), "js" => array(
                "core","feather","template",
            ),
        ),
        "landing_page" => array("header" => array(),"body" => array("landing_wrapper"),"footer" => array("global_by_line"),
            "css" => array(
                "core","iconfont", "mdi_icons","style", "main_style", "landing_style", "styles2", "responsiveness"
            ),"js" => array(
                "jquery","core", "moment","bootstrap","flot","flot_resize", "feather","template","dashboard","main", "async_search","initializer"
            )
        ),
        "login" => array("header" => array(),"body" => array("landing_wrapper"),"footer" => array("global_by_line"),
            "css" => array(
                "core","iconfont", "mdi_icons","style", "main_style", "landing_style", "styles2", "responsiveness"
            ),"js" => array(
                "jquery","core", "moment","bootstrap","flot","flot_resize", "feather","template","dashboard","main", "async_search","initializer"
            )
        ),
        "reset_pwd" => array("header" => array(),"body" => array("landing_wrapper"),"footer" => array("global_by_line"),
            "css" => array(
                "core","iconfont", "mdi_icons","style", "main_style", "landing_style", "styles2", "responsiveness"
            ),"js" => array(
                "jquery","core", "moment","bootstrap","flot","flot_resize", "feather","template","dashboard","main", "async_search","initializer"
            )
        ),
        "signup" => array("header" => array(),"body" => array("landing_wrapper"),"footer" => array("global_by_line"),
            "css" => array(
                "core","iconfont", "mdi_icons","style", "main_style", "landing_style", "styles2", "responsiveness"
            ),"js" => array(
                "jquery","core", "moment","bootstrap","flot","flot_resize", "feather","template","dashboard","main", "async_search","initializer"
            )
        ),
        "privacy_policy" => array("header" => array(),"body" => array("landing_wrapper"),"footer" => array(),
            "css" => array(
                "core","iconfont", "mdi_icons","style", "main_style", "landing_style", "styles2", "responsiveness"
            ),"js" => array(
                "jquery","core", "moment","bootstrap","flot","flot_resize", "feather","template","dashboard","main", "async_search","initializer"
            )
        ),
        "edit_page" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style","template","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","moment","bootstrap","flot","flot_resize","feather","template", "vendor_maxlength",
                "dashboard", "main", "payments","async_search", "adm_scripts","initializer"
            )
        ),
        "terms_of_use" => array("header" => array(),"body" => array("landing_wrapper"),"footer" => array(),
            "css" => array(
                "core","iconfont", "mdi_icons","style", "main_style", "landing_style", "styles2", "responsiveness"
            ),"js" => array(
                "jquery","core", "moment","bootstrap","flot","flot_resize", "feather","template","dashboard","main", "async_search","initializer"
            )
        ),
        "user_agreement" => array("header" => array(),"body" => array("landing_background","user_agreement"),"footer" => array(),
            "css" => array(
                "core","iconfont","style","main_style","landing_style", "responsiveness"
            ),"js" => array(
                "jquery","core","select2_min","select2","moment","bootstrap","flot","flot_resize","template","dashboard","main","initializer"
            )
        ),
        "FAQ" => array("header" => array(),"body" => array("landing_background","landing_header","FAQ"),"footer" => array(),
            "css" => array(
                "core","iconfont","style","main_style","landing_style", "responsiveness"
            ),"js" => array(
                "jquery","core","select2_min","select2","moment","bootstrap","flot","flot_resize","template","dashboard","main","initializer"
            )
        ),
        "general_settings" => array("header" => array(),"body" => array("user_template"),"footer" => array("footer"),
            "css" => array(
                "core","iconfont","flag_icon","mdi_icons","sweet_alert",
                "template","style","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core", "moment","bootstrap","flot","flot_resize","feather","template",
                "sweet_alert_min","sweet_alert","dashboard","main","async_search","initializer"
            )
        ),
        "cookie-manager" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style", "select2", "data_tables","template","sweet_alert","daterangepicker_min","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","select2_min","select2","moment","bootstrap","flot","flot_resize","feather","template","sweet_alert_min","sweet_alert",
                "daterangepicker_min","dashboard","charts_vendor","charts","apex_min","dataTablesVendor","dataTablesBootstrap","dataTables",
                "main","async_search","initializer"
            )
        ),
        "campaigns" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style", "select2", "data_tables","template","sweet_alert","daterangepicker_min","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","select2_min","select2","moment","bootstrap","flot","flot_resize","feather","template","sweet_alert_min","sweet_alert",
                "daterangepicker_min","dashboard","charts_vendor","charts","apex_min","dataTablesVendor","dataTablesBootstrap","dataTables","handlebars",
                "main","async_search", "live-chat","initializer"
            )
        ),
        "my-mentions" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style", "data_tables","template","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","moment","bootstrap","flot","flot_resize","feather","template",
                "dashboard","dataTablesVendor","dataTablesBootstrap","dataTables","handlebars",
                "main","async_search", "live-chat","initializer"
            )
        ),
        "users" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style", "select2", "data_tables","template","sweet_alert","daterangepicker_min","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","select2_min","select2","moment","bootstrap","flot","flot_resize","feather","template","sweet_alert_min","sweet_alert",
                "daterangepicker_min","dashboard","charts_vendor","charts","apex_min","dataTablesVendor","dataTablesBootstrap","dataTables",
                "main","async_search", "adm_scripts","initializer"
            )
        ),
        "creators" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style", "select2", "data_tables","template","sweet_alert","daterangepicker_min","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","select2_min","select2","moment","bootstrap","flot","flot_resize","feather","template", "google_charts","sweet_alert_min","sweet_alert",
                "daterangepicker_min","dashboard","charts_vendor","charts","apex_min","dataTablesVendor","dataTablesBootstrap","dataTables","handlebars",
                "main","async_search", "live-chat","initializer"
            )
        ),
        "integrations" => array("header" => array(),"body" => array("user_template"),"footer" => array("footer"),
            "css" => array(
                "core","iconfont","flag_icon","mdi_icons","sweet_alert",
                "template","style","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core", "moment","bootstrap","flot","flot_resize","feather","template","handlebars",
                "sweet_alert_min","sweet_alert","dashboard","main", "modal-handler","async_search", "adm_scripts", "live-chat","initializer"
            )
        ),
        "home" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style", "select2", "data_tables","template","sweet_alert","daterangepicker_min","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","select2_min","select2","moment","bootstrap","flot","flot_resize","feather","template", "google_charts","sweet_alert_min","sweet_alert",
                "daterangepicker_min","dashboard","charts_vendor","charts","apex_min","dataTablesVendor","dataTablesBootstrap","dataTables","handlebars",
                "main","async_search", "live-chat","initializer"
            )
        ),
        "cron-logs" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style","data_tables","template","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","moment","bootstrap","flot","flot_resize","feather","template",
                "main","async_search","initializer"
            )
        ),
        "settings" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core", "cropper_min","iconfont","mdi_icons","flag_icon","style","data_tables","template","sweet_alert",
                "daterangepicker_min","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core", "cropper_min","moment","bootstrap","flot","flot_resize","feather","template","sweet_alert_min","sweet_alert",
                "daterangepicker_min","dashboard","charts_vendor","charts","apex_min","dataTablesVendor","dataTablesBootstrap","dataTables","handlebars",
                "main","async_search", "adm_scripts", "editor", "editorHandler", "live-chat","initializer"
            )
        ),
        "notifications" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style","data_tables","template","sweet_alert",
                "daterangepicker_min","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","moment","bootstrap","flot","flot_resize","feather","template","sweet_alert_min","sweet_alert",
                "daterangepicker_min","dashboard","charts_vendor","charts","apex_min","dataTablesVendor","dataTablesBootstrap","dataTables",
                "main","async_search","initializer"
            )
        ),
        "support" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style","data_tables","template","sweet_alert",
                "daterangepicker_min","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","moment","bootstrap","flot","flot_resize","feather","template","sweet_alert_min","sweet_alert",
                "daterangepicker_min","dashboard","charts_vendor","charts","apex_min","dataTablesVendor","dataTablesBootstrap","dataTables",
                "main","async_search","initializer"
            )
        ),
        "errors" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style","date_picker","data_tables",
                "template","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","moment","bootstrap","flot","flot_resize","feather","template",
                "dashboard","date_picker_min","date_picker","dataTablesVendor","dataTablesBootstrap","dataTables",
                "main","async_search","initializer"
            )
        ),
        "activity_logs" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style","sweet_alert","date_picker","data_tables",
                "template","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","moment","bootstrap","flot","flot_resize","feather","template","sweet_alert_min","sweet_alert",
                "dashboard","date_picker_min","date_picker","dataTablesVendor","dataTablesBootstrap","dataTables",
                "main","async_search","initializer"
            )
        ),
        "user_logs" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style","date_picker","data_tables","template","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","moment","bootstrap","flot","flot_resize","feather","template",
                "dashboard","date_picker_min","date_picker","dataTablesVendor","dataTablesBootstrap","dataTables",
                "main","async_search","initializer"
            )
        ),
        "access_points" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style","data_tables","template",
                "daterangepicker_min","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","moment","bootstrap","flot","flot_resize","feather","template","daterangepicker_min","dashboard",
                "charts_vendor","charts","apex_min","dataTablesVendor","dataTablesBootstrap","dataTables",
                "main","async_search","initializer"
            )
        ),
        "dev_admin" => array("header" => array(),"body" => array("user_template"),"footer" => array(),
            "css" => array(
                "core","iconfont","mdi_icons","flag_icon","style","data_tables","template",
                "daterangepicker_min","main_style","styles2", "responsiveness"
            ),"js" => array(
                "jquery","core","moment","bootstrap","flot","flot_resize","feather","template","daterangepicker_min","dashboard",
                "charts_vendor","charts","apex_min","dataTablesVendor","dataTablesBootstrap","dataTables",
                "main","async_search","initializer"
            )
        ),
    );

    function __construct(){
        $this->innerPages = new PageInnerPages();
        $this->sideBar = new SideBarMenu();
    }
}