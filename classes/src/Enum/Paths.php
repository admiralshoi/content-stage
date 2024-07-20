<?php
namespace classes\src\Enum;
class Paths {
    const html = array(
        "login_header" => "includes/html/old/login_header.php",
        "signin_pre" => "includes/html/landing_page/signin_pre.php",
        "signin_package" => "includes/html/landing_page/signin_package.php",
        "signin_integrations" => "includes/html/landing_page/signin_integration.php",
        "login" => "includes/html/landing_page/login.php",
        "reset_pwd" => "includes/html/landing_page/reset_password.php",
        "signup" => "includes/html/landing_page/signup.php",
        "global_by_line" => "includes/html/footers/global_by_line.php",
        "register_header" => "includes/html/old/register_header.php",
        "register_form" => "includes/html/landing_page/register_form.php",
        "user_header" => "includes/html/old/user_header.php",
        "user_body" => "includes/html/old/user_body.php",
        "logout_link" => "includes/html/global_utility/logout_link.php",
        "left_side_bar" => "includes/html/sidebars/left_side_bar.php",
        "user_template" => "includes/html/user_templates/user_template.php",
        "user_top" => "includes/html/user_templates/user_top.php",
        "user_page" => "includes/html/user_templates/user_page.php",
        "user_temp_body" => "includes/html/user_templates/user_temp_body.php",
        "user_footer" => "includes/html/footers/user_footer.php",
        "404" => "includes/html/global_utility/404.php",
        "landing_header" => "includes/html/landing_page/header.php",
        "landing_header_black" => "includes/html/landing_page/header_black_logo.php",
        "landing_wrapper" => "includes/html/landing_page/wrapper.php",
        "landing_cover" => "includes/html/landing_page/cover.php",
        "landing_body" => "includes/html/landing_page/body.php",
        "landing_background" => "includes/html/landing_page/background.html",
        "FAQ" => "includes/html/help/FAQ.php",
        "register" => "includes/html/landing_page/register.php",
        "general_settings" => "includes/html/profile/general_settings.php",
        "errors" => "includes/html/errors/errors.php",
        "activity_logs" => "includes/html/admin_utility/activity_logs.php",
        "access_points" => "includes/html/admin_utility/access_points.php",
        "user_logs" => "includes/html/admin_utility/user_logs.php",
        "dev_admin" => "includes/html/extra/devTestPage.php",
        "marketplace" => "includes/html/other/marketplace.php",
        "admin_dashboard" => "includes/html/dashboards/admin_dashboard.php",
        "brand_dashboard" => "includes/html/dashboards/brand_dashboard.php",
        "creator_dashboard" => "includes/html/dashboards/creator_dashboard.php",
        "profile_images" => "includes/html/profile/profile_images.php",
        "editor" => "includes/html/extra/editor.php",
        "orders" => "includes/html/orders/orders-router.php",
        "support" => "includes/html/help/support_centre.php",
        "notifications" => "includes/html/other/notifications.php",
        "settings" => "includes/html/profile/settings-router.php",
        "wallet" => "includes/html/wallet/wallet-router.php",
        "users" => "includes/html/admin_utility/users.php",
        "stripe_auth" => "includes/html/api/stripe_auth.php",
        "lookup" => "includes/html/other/lookup.php",
        "place_order" => "includes/html/orders/place_order.php",
        "checkout" => "includes/html/orders/checkout.php",
        "view_order" => "includes/html/orders/view_order.php",
        "privacy_policy" => "includes/html/legal/privacy_policy.php",
        "terms_of_use" => "includes/html/legal/terms_of_use.php",
        "user_agreement" => "includes/html/legal/user_agreement.php",
        "edit_page" => "includes/html/admin_utility/edit_page.php",
        "integrations" => "includes/html/admin_utility/integrations.php",
        "cron-logs" => "includes/html/other/cronLogs.php",
        "my_mentions" => "includes/html/other/my_mentions.php",
        "campaigns" => "includes/html/other/campaigns.php",
        "creators" => "includes/html/other/creators.php",
        "cookie-manager" => "includes/html/other/cookieManager.php",
        "live-chat" => "includes/html/other/live-chat.php",
    );
    const css = array(
        "core" => "includes/template/assets/vendors/core/core.css",
        "iconfont" => "includes/template/assets/fonts/feather-font/css/iconfont.css",
        "mdi_icons" => "includes/template/assets/vendors/mdi/css/materialdesignicons.min.css",
        "style" => "includes/template/assets/css/demo_1/style.css",
        "date_picker" => "includes/template/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css",
        "bootstrap" => "includes/template/assets/vendors/tempusdominus-bootstrap-4/tempusdominus-bootstrap-4.min.css",
        "flag_icon" => "includes/template/assets/vendors/flag-icon-css/css/flag-icon.min.css",
        "data_tables" => "includes/template/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css",
        "sweet_alert" => "includes/template/assets/vendors/sweetalert2/sweetalert2.min.css",
        "carousel_min" => "includes/template/assets/vendors/owl.carousel/owl.carousel.min.css",
        "carousel_theme_min" => "includes/template/assets/vendors/owl.carousel/owl.theme.default.min.css",
        "cropper_min" => "includes/template/assets/vendors/cropperjs/cropper.min.css",
        "select2" => "includes/template/assets/vendors/select2/select2.min.css",
        "dropzone" => "https://unpkg.com/dropzone@5/dist/min/dropzone.min.css",



        "landing_style" => "style/landing_design/styler.css",
        "main_style" => "style/main_style.css",
        "styles2" => "style/styles2.css",
        "responsiveness" => "style/responsiveness.css",
        "daterangepicker_min" => "style/includes/daterangepicker.css",
        "filepond" => "style/includes/filePond.css",
    );
    const js = array(
        "main" => "javascript/main.js",
        "async_search" => "javascript/async_search.js",
        "initializer" => "javascript/initializer.js",
        "payments" => "javascript/includes/payments.js",
        "jquery" => "javascript/includes/jquery.js",
        "dataTables" => "javascript/includes/dataTables.js",
        "sweet_alert" => "javascript/includes/sweetAlert.js",
        "filepond" => "javascript/includes/filePond.js",
        "charts" => "javascript/includes/charts.js",
        "editor" => "javascript/Editor.js",
        "adm_scripts" => "javascript/adm-scripts.js",
        "editorHandler" => "javascript/editorHandler.js",
        "daterangepicker_min" => "javascript/includes/daterangepicker.js",
        "live-chat" => "javascript/live-chat.js",
        "modal-handler" => "javascript/modalHandler.js",


        "core" => "includes/template/assets/vendors/core/core.js",
        "form-validation" => "includes/template/assets/js/form-validation.js",
        "bootstrap" => "includes/template/assets/vendors/tempusdominus-bootstrap-4/tempusdominus-bootstrap-4.js",
        "flot" => "includes/template/assets/vendors/jquery.flot/jquery.flot.js",
        "flot_resize" => "includes/template/assets/vendors/jquery.flot/jquery.flot.resize.js",
        "dashboard" => "includes/template/assets/js/dashboard.js",
        "charts_custom" => "includes/template/assets/js/chartjs.js",
        "charts_vendor" => "includes/template/assets/vendors/chartjs/Chart.min.js",
        "carousel_min" => "includes/template/assets/vendors/owl.carousel/owl.carousel.min.js",
        "jq_mousewheel" => "includes/template/assets/vendors/jquery-mousewheel/jquery.mousewheel.js",
        "carousel" => "includes/template/assets/js/carousel.js",
        "moment" => "includes/template/assets/vendors/moment/moment.min.js",
        "date_picker" => "includes/template/assets/js/datepicker.js",
        "date_picker_min" => "includes/template/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js",
        "apex_min" => "includes/template/assets/vendors/apexcharts/apexcharts.min.js",
        "apex" => "includes/template/assets/js/apexCharts.js",
        "progress" => "includes/template/assets/vendors/progressbar.js/progressbar.min.js",
        "feather" => "includes/template/assets/vendors/feather-icons/feather.min.js",
        "template" => "includes/template/assets/js/template.js",
        "dataTablesVendor" => "includes/template/assets/vendors/datatables.net/jquery.dataTables.js",
        "dataTablesBootstrap" => "includes/template/assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js",
        "sweet_alert_min" => "includes/template/assets/vendors/sweetalert2/sweetalert2.min.js",
        "vendor_maxlength" => "includes/template/assets/vendors/bootstrap-maxlength/bootstrap-maxlength.min.js",
        "cropper_min" => "includes/template/assets/vendors/cropperjs/cropper.min.js",
        "select2" => "includes/template/assets/vendors/select2/select2.min.js",
        "dropzone" => "https://unpkg.com/dropzone@5/dist/min/dropzone.min.js",
        "filepond_metadata" => "https://unpkg.com/filepond-plugin-file-metadata/dist/filepond-plugin-file-metadata.js",
        "google_charts" => "https://www.gstatic.com/charts/loader.js",
        "handlebars" => "https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.7.8/handlebars.min.js",
    );

    private const sideBarPaths = array(
        "sideBarMain" => "includes/html/sidebars/sideBarMain.php",
        "sideBarAdmin" => "includes/html/sidebars/sideBarAdmin.php",
        "general" => "includes/html/profile/general_settings.php",
    );


    static function sideBar($list) {
        $response = array();
        foreach ($list as $pathName) {
            if(array_key_exists($pathName,self::sideBarPaths))
                array_push($response,self::sideBarPaths[$pathName]);
        }
        return $response;
    }





    static function path($keys,$type) {
        $response = array();

        switch ($type) {
            case "html":
                foreach ($keys as $key) {
                    if(array_key_exists($key,self::html))
                        array_push($response,str_replace("/",DIRECTORY_SEPARATOR,self::html[$key]));
                }
                return $response;
                break;
            case "css":
                foreach ($keys as $key) {
                    if(array_key_exists($key,self::css))
                        array_push($response,str_replace("/",DIRECTORY_SEPARATOR,self::css[$key]));
                }
                return $response;
                break;
            case "js":
                foreach ($keys as $key) {
                    if(array_key_exists($key,self::js))
                        array_push($response,str_replace("/",DIRECTORY_SEPARATOR,self::js[$key]));
                }
                return $response;
                break;
        }
        return "";
    }
}