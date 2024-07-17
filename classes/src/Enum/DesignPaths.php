<?php

namespace classes\src\Enum;

class DesignPaths {

    const path_base = "images/icons/";
    const main_bottom_section_left = "images/mainpage/bottom_section_left.png";
    const main_bottom_section_right = "images/mainpage/bottom_section_right.png";
    const main_bottom_section_second_left = "images/mainpage/bottom_section_second_left.png";
    const main_bottom_section_second_right = "images/mainpage/bottom_section_second_right.png";
    const bottom_nav_logo = "images/mainpage/bottom_nav_logo.png";
    const main_banner = "images/mainpage/main_banner.png";
    const promo_video = "images/videos/promo-video-22-10-22.mp4";
    const main_first_section = "images/mainpage/main_first_section.png";
    const main_fourth_section = "images/mainpage/main_fourth_section.png";
    const main_second_section = "images/mainpage/main_second_section.png";
    const main_third_section = "images/mainpage/main_third_section.png";
    const orange_banner = "images/mainpage/orange_banner.png";
    const top_nav_logo = "images/mainpage/top_nav_logo.png";
    const orange_bullet_point = "images/mainpage/bulletpoint.png";
    const dark_nav_logo = "images/mainpage/dark_logo.png";
    const s_design = "images/mainpage/signin_s_design.png";
    const users_black = "images/icons/users_black.png";
    const users_orange = "images/icons/users_orange.png";
    const marketplace_black = "images/mainpage/marketplace_black.png";
    const marketplace_orange = "images/mainpage/marketplace_orange.png";
    const notifications_black = "images/mainpage/notifications_black.png";
    const notifications_orange = "images/mainpage/notifications_orange.png";
    const profile_black = "images/mainpage/profile_black.png";
    const profile_orange = "images/mainpage/profile_orange.png";
    const orders_black = "images/mainpage/orders_black.png";
    const orders_orange = "images/mainpage/orders_orange.png";
    const settings_black = "images/mainpage/settings_black.png";
    const settings_orange = "images/mainpage/settings_orange.png";
    const wallet_black = "images/mainpage/wallet_black.png";
    const wallet_orange = "images/mainpage/wallet_orange.png";
    const marketplace_banner = "images/mainpage/marketplace_banner.png";
    const example_portrait = "images/mainpage/example_portrait.jpg";
    const email_icon = "images/icons/email.png";
    const brand_icon_not_clicked = "images/icons/brand_icon_not_clicked.png";
    const brand_icon_clicked = "images/icons/brand_icon_clicked.png";
    const influencer_icon_not_clicked = "images/icons/influencer_icon_not_clicked.png";
    const influencer_icon_clicked = "images/icons/influencer_icon_clicked.png";
    const add_more_packages = "images/icons/add_more_packages.png";
    const logout_icon = "images/icons/logout_icon.png";
    const upload_icon = "images/icons/upload_icon.png";
    const camera_icon = "images/icons/camera_icon.png";
    const video_icon = "images/icons/video_icon.png";
    const other_icon = "images/icons/other_icon.png";
    const tiktok_small_icon = "images/icons/tiktok_small_icon.png";
    const post_icon = "images/icons/post_icon.png";
    const following_icon = "images/icons/following_icon.png";
    const followers_icon = "images/icons/followers_icon.png";
    const support_icon = "images/other/support_icon.png";
    const personal_details = "images/other/personal_details.png";
    const my_profile = "images/other/my_profile.png";
    const connected_accounts = "images/other/connected_accounts.png";
    const total_new_users_icon = "images/icons/total_new_users_icon.png";
    const total_influencers_icon = "images/icons/total_influencers_icon.png";
    const total_brands_icon = "images/icons/total_brands_icon.png";
    const package_offered = "images/icons/package_offered.png";
    const order_history = "images/icons/order_history.png";
    const delete_package = "images/icons/delete_package.png";
    const order_requirements = "images/icons/order_requirements.png";
    const orange_bullet = "images/icons/orange_bullet.png";
    const visa_mastercard = "images/icons/visa_mastercard.png";
    const order_in_progress = "images/icons/order_in_progress.png";
    const delivery_section_icon = "images/icons/delivery_section_icon.png";
    const delivery_section_icon2 = "images/icons/delivery_section_icon2.png";
    const green_paper_sheet = "images/icons/green_paper_sheet.png";
    const green_note = "images/icons/green_note.png";
    const req_safety = "images/icons/req_safety.png";
    const approve_icon = "images/icons/approve-icon.png";
    const approve_white_icon = "images/icons/approve_white_icon.png";
    const no_delivery_icon = "images/icons/no_delivery_icon.png";
    const communication_icon = "images/icons/communication_icon.png";
    const send_message = "images/icons/send_message.png";
    const order_delivered = "images/icons/order_delivered.png";
    const order_late = "images/icons/order_late.png";
    const order_revision = "images/icons/order_revision.png";
    const order_complete = "images/icons/order_complete.png";
    const order_cancelled = "images/icons/order_cancelled.png";
    const customer_support_profile = "images/icons/customer_support_profile.png";
    const notification_cancel = "images/icons/notification_cancel.png";
    const notification_completed = "images/icons/notification_completed.png";
    const notification_support = "images/icons/notification_support.png";
    const notification_delivered = "images/icons/notification_delivered.png";
    const notification_late = "images/icons/notification_late.png";
    const notification_order_in_progress = "images/icons/notification_order_in_progress.png";
    const notification_revision = "images/icons/notification_revision.png";
    const gray_icon = "images/gray_icon.png";
    const orange_icon = "images/orange_icon.png";


    public static function returnByName(string $name): string {
        try {
            $constant_reflex = new \ReflectionClassConstant(get_called_class(), $name);
            return $constant_reflex->getValue();
        } catch (\ReflectionException $e) {
            return "";
        }
    }

}