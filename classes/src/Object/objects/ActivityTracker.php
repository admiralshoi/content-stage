<?php

namespace classes\src\Object\objects;
use classes\src\AbstractCrudObject;

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

class ActivityTracker {

    private const actionTypes = array( "click", "change", "page_view", "video_view", "passive");
    private const actionNames = array(
        "page_view" => array(
            "app_general", "activity_log", "report_account", "latest_mention", "mapping", "create_user", "manage_brands", "access_points", "connected_account",
            "employee_scripts","employee_payouts","claim_instagram_users","video_guide","violation","rouge","errors","dashboard_employee",
            "dashboard_team","dashboard_admin","login_logs","manage_teams","report_employee","report_team","report_brand",
            "quick_view_employees","quick_view_sales","profile_settings", "quick_view_payment_period", "quick_view_payment",
            "scraper_libraries", "scraper_filtering"
        ),
        "click" => array(
            "user_dashboard_latest_mention_table","user_dashboard_claim_users_link","team_disconnect_account","team_connect_account",
            "remove_team","rename_team","create_team","change_salary","change_minimum_salary","change_payment_start_day","add_script",
            "brand_connect_account","brand_disconnect_account","remove_brand","rename_brand","create_brand","create_user",
            "latest_mention_refresh_data","latest_mention_date_picker","header_profile","header_notification","facebook_connect",
            "employee_script_copy_button","view_employees_employee_link","mark_banned_instagram_user",
            "unclaim_instagram_user","latest_mention_link","brand_add_to_object","team_add_to_object","brand_remove_from_object","team_remove_from_object",
            "remove_integrated_account","confirm_payment_of_payment_period_button","view_payment_period_button","close_payment_period_button",
            "add_new_sale","sales_switch_view","update_profile_details_button","users_script_available_account","users_payout_history_table",
            "claim_instagram_users","video_how_to_claim_instagram_users","user_dashboard_violations_table","user_dashboard_set_paypal_email",
            "payment_period_download_csv","payment_history_view_period","payment_history_download_csv","team_object_remove_member",
            "change_minimum_weekly_comments"
        ),

        "change" => array(
            "edit_script","access_point","sales_search_post_link","header_search_bar",
        ),
        "passive" => array(
            "how_to_spot_customers_from_a_mile_away","how_to_systematically_target_ambassador_pages",
            "how_to_target_customers_through_hashtags","how_to_target_customers_through_locations","why_the_advanced_targeting_matters",
            "approved_hire", "claimed_account", "sale", "leaderboard_1", "leaderboard_2", "leaderboard_3", "weekly_sale_ratio"
        )
    );

    public AbstractCrudObject $crud;
    private int $requestingUsersAccessLevel = 0;
    private int $requestingUsersId = 0;
    private bool $disabledDepthCheck = false;

    function __construct(AbstractCrudObject $crud){
        $this->crud = $crud;
        if(isset($_SESSION["access_level"])) $this->requestingUsersAccessLevel = $_SESSION["access_level"];
        if(isset($_SESSION["id"])) $this->requestingUsersId = $_SESSION["id"];
    }

    private function access(int $actionType): bool {
        if($this->disabledDepthCheck) return true;
        return $this->crud->hasAccess("node","activity_log",$actionType, $this->requestingUsersAccessLevel);
    }

    public function disableDepthCheck(): void { $this->disabledDepthCheck = true; }
    public function enableDepthCheck(): void { $this->disabledDepthCheck = false; }

    public function getByX(array $params = array(), array $fields = array(), string $sql = ""): array {
        if(!$this->access(READ_ACTION)) return array();

        if(!$this->disabledDepthCheck) {
            $params = $this->crud->resolveMultiParamsDepth($params, $this->activityLogDepthParams(), array("uid"));
            if($params === null) return array();
        }

        return $this->crud->retrieve("activity", $params, $fields, $sql);
    }

    public function logActivity(array $args): void {
        if(!$this->access(MODIFY_ACTION)) return;
        if(empty($args)) return;

        if(!array_key_exists("action_type", $args) || !array_key_exists("action_name", $args)) return;
        $actionType = $args["action_type"]; $actionName = $args["action_name"];

        if(!in_array($actionType,self::actionTypes)) return;
        if(!in_array($actionName,self::actionNames[$actionType])) return;

        if($this->requestingUsersId === 0) return;
        $params = array(
            "action_type" => $actionType,
            "action_name" => $actionName,
            "uid" => array_key_exists("uid", $args) ? (int)$args["uid"] : $this->requestingUsersId,
            "created_at" => array_key_exists("created_at", $args) ? (int)$args["created_at"] : time()
        );

        $this->crud->create("activity", array_keys($params), $params);
    }



    public function activityLogDepthParams(): array {
        if($this->disabledDepthCheck) return array();
        $this->disabledDepthCheck = true;
        $depth = $this->crud->userRoles()->depth($this->requestingUsersAccessLevel);

        if($depth === "user") $param = array("uid" => $this->requestingUsersId);
        elseif($depth === "team") {
            $employees = $this->crud->user($this->requestingUsersId)->getByX();

            if(empty($employees)) $param = array("uid" => 0);
            else $param = array("uid" => array_map(function ($employee) { return $employee["id"]; }, $employees));
        }
        elseif (empty($depth)) $param = array("uid" => 0);
        else $param = array();

        $this->disabledDepthCheck = false;
        return $param;
    }

}






























