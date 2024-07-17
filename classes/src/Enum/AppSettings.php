<?php

namespace classes\src\Enum;

class AppSettings {
    const APP_ID = 555456350062580; //goodbrands
//    const APP_ID = 1095366668006187; //dmapi
    const APP_SECRET = "5abe6dabeb01244e0906d69f598cd78e"; //goodbrands
//    const APP_SECRET = "41e0fd895062a05446722fc1a6dbb0ab"; //dmapi

    const FB_GRAPH_VERSION = "v19.0";
    const FB_GRAPH_BASE_URL = "https://graph.facebook.com/";
    const FB_REDIRECT_URL = HOST . "?page=integrations";
    const AUTH_STATE_PHRASE = "some-state-192484";

    const SUBSCRIBE_APP = true;
//    const SUBSCRIPTION_FIELDS = ["mention"];
    const SUBSCRIPTION_FIELDS = ["messages", "mention"];
    const CREATOR_SUBSCRIPTION_FIELDS = [];
    const PERMISSIONS = array(
        "public_profile",
        "pages_show_list",
        "pages_read_engagement",
        "business_management",
        "instagram_basic",
        "instagram_manage_insights",
        "instagram_manage_messages",
        "instagram_manage_comments",
        "pages_manage_metadata"
    );



    const CREATOR_PERMISSIONS = array(
        "public_profile",
        "pages_show_list",
        "pages_read_engagement",
        "business_management",
        "instagram_basic",
        "instagram_manage_insights",
    );

    const AUTH_GRAPH_ENDPOINTS = array(
        "longLivedToken" => "oauth/access_token",
        "user" => "me",
        "user_accounts" => "me/accounts",
        "ig_accounts" => "__PAGE_ID__",
    );


    const MENTIONS_QUERY = [
        "story" => [],
        "post" => ["caption","username","timestamp","permalink", "media_url", "media_type", "like_count", "comments_count"],
        "comment" => ["text","username","timestamp","media{permalink}"],
    ];






    const SEND_MESSAGE_ENDPOINT = "me/messages";
    const FB_SUBSCRIBE_WEBHOOK_ENDPOINT = "me/subscribed_apps";
    const INSIGHT_GRAPH_ENDPOINT = "__ACCOUNT_ID__/insights";
    const FB_POST_ENDPOINTS = array(
        "photos" => "__PAGE_ID__/photos",
        "feed" => "__PAGE_ID__/feed",
        "videos" => "__PAGE_ID__/videos",
    );
    const IG_POST_ENDPOINTS = array(
        "container" => "__ACCOUNT_ID__/media",
        "publish_container" => "__ACCOUNT_ID__/media_publish"
    );
    const IG_MEDIA_ENDPOINT = "__ACCOUNT_ID__/media";
    const IG_BUSINESS_DISCOVERY_ENDPOINT = "__IG_ID__";
    const BUSINESS_DISCOVERY_FIELDS = [
        "username",
        "followers_count",
        "follows_count",
        "media_count",
        "biography",
        "ig_id",
        "profile_picture_url",
        "media{comments_count,caption,like_count,media_type,permalink,timestamp}"
    ];









    /*
     * Metrics that support lifetime periods will have results returned in an array of 24 hour periods,
     * with periods ending on UTC−07:00. audience_* metrics do not support since and until range parameters.
     * Reference: https://developers.facebook.com/docs/instagram-api/reference/ig-user/insights
     */
    const IG_INSIGHT_METRICS_AND_COMPATIBLE_PERIODS = array(
        "audience_city" => array("lifetime"),
        "audience_country" => array("lifetime"),
        "audience_gender_age" => array("lifetime"),
        "audience_locale" => array("lifetime"),
        "email_contacts" => array("day"),
        "follower_count" => array("day"),
        "get_directions_clicks" => array("day"),
        "impressions" => array("day", "week", "days_28"),
        "online_followers" => array("lifetime"),
        "phone_call_clicks" => array("day"),
        "profile_views" => array("day"),
        "reach" => array("day", "week", "days_28"),
        "text_message_clicks" => array("day"),
        "website_clicks" => array("day"),
    );
    const IG_INSIGHT_METRICS_NO_SUPPORT_CUSTOM_TIME_INTERVAL = array(
        "audience_city","audience_country","audience_gender_age","audience_locale",
    );


    /**
     * date_presets: enum{today, yesterday, this_month, last_month, this_quarter, maximum, data_maximum,
     * last_3d, last_7d, last_14d, last_28d, last_30d, last_90d, last_week_mon_sun, last_week_sun_sat,
     * last_quarter, last_year, this_week_mon_today, this_week_sun_today, this_year}
     *
     * Preset a date range, like lastweek, yesterday. If since or until presents, it does not work.
     *
     *
     * Please use https://developers.facebook.com/docs/graph-api/reference/v13.0/insights
     * for full descriptions of the different metrics, periods, presets etc.
     */
    const FB_PAGE_INSIGHT_PARAMS = array(
        "date_preset", "metric", "period", "since", "until"
    );
    const FB_PAGE_INSIGHT_FIELDS = array(
        "id", "description", "name", "period", "title", "values"
    );


    const FB_PAGE_METRICS = array(
        "tab_views" => array(
            "page_tab_views_login_top_unique" => array("day", "week"),
            "page_tab_views_login_top" => array("day", "week"),
            "page_tab_views_logout_top" => array("day"),
        ),
        "cta_clicks" => array(
            "page_total_actions" => array("day", "week", "days_28"),
            "page_cta_clicks_logged_in_total" => array("day", "week", "days_28"),
            "page_cta_clicks_logged_in_unique" => array("day", "week", "days_28"),
            "page_cta_clicks_by_site_logged_in_unique" => array("day", "week", "days_28"),
            "page_cta_clicks_by_age_gender_logged_in_unique" => array("day", "week", "days_28"),
            "page_cta_clicks_logged_in_by_country_unique" => array("day", "week"),
            "page_cta_clicks_logged_in_by_city_unique" => array("day", "week"),
            "page_call_phone_clicks_logged_in_unique" => array("day", "week", "days_28"),
            "page_call_phone_clicks_by_age_gender_logged_in_unique" => array("day", "week", "days_28"),
            "page_call_phone_clicks_logged_in_by_country_unique" => array("day", "week"),
            "page_call_phone_clicks_logged_in_by_city_unique" => array("day", "week"),
            "page_call_phone_clicks_by_site_logged_in_unique" => array("day", "week", "days_28"),
            "page_get_directions_clicks_logged_in_unique" => array("day", "week", "days_28"),
            "page_get_directions_clicks_by_age_gender_logged_in_unique" => array("day", "week", "days_28"),
            "page_get_directions_clicks_logged_in_by_country_unique" => array("day", "week"),
            "page_get_directions_clicks_logged_in_by_city_unique" => array("day", "week"),
            "page_get_directions_clicks_by_site_logged_in_unique" => array("day", "week", "days_28"),
            "page_website_clicks_logged_in_unique" => array("day", "week", "days_28"),
            "page_website_clicks_by_age_gender_logged_in_unique" => array("day", "week", "days_28"),
            "page_website_clicks_logged_in_by_country_unique" => array("day", "week"),
            "page_website_clicks_logged_in_by_city_unique" => array("day", "week"),
            "page_website_clicks_by_site_logged_in_unique" => array("day", "week", "days_28"),
        ),
        "page_engagement" => array(
            "page_engaged_users" => array("day", "week", "days_28"),
            "page_post_engagements" => array("day", "week", "days_28"),
            "page_consumptions" => array("day", "week", "days_28"),
            "page_consumptions_unique" => array("day", "week", "days_28"),
            "page_consumptions_by_consumption_type" => array("day", "week", "days_28"),
            "page_consumptions_by_consumption_type_unique" => array("day", "week", "days_28"),
            "page_places_checkin_total" => array("day", "week", "days_28"),
            "page_places_checkin_total_unique" => array("day", "week", "days_28"),
            "page_places_checkin_mobile" => array("day", "week", "days_28"),
            "page_places_checkin_mobile_unique" => array("day", "week", "days_28"),
            "page_places_checkins_by_age_gender" => array("day"),
            "page_places_checkins_by_locale" => array("day"),
            "page_places_checkins_by_country" => array("day"),
            "page_negative_feedback" => array("day", "week", "days_28"),
            "page_negative_feedback_unique" => array("day", "week", "days_28"),
            "page_negative_feedback_by_type" => array("day", "week", "days_28"),
            "page_negative_feedback_by_type_unique" => array("day", "week", "days_28"),
            "page_positive_feedback_by_type" => array("day", "week", "days_28"),
            "page_positive_feedback_by_type_unique" => array("day", "week", "days_28"),
            "page_fans_online" => array("day"),
            "page_fans_online_per_day" => array("day"),
            "page_fan_adds_by_paid_non_paid_unique" => array("day"),
        ),
        "page_impressions" => array(
            "page_impressions" => array("day", "week", "days_28"),
            "page_impressions_unique" => array("day", "week", "days_28"),
            "page_impressions_paid" => array("day", "week", "days_28"),
            "page_impressions_paid_unique" => array("day", "week", "days_28"),
            "page_impressions_organic" => array("day", "week", "days_28"),
            "page_impressions_organic_unique" => array("day", "week", "days_28"),
            "page_impressions_viral" => array("day", "week", "days_28"),
            "page_impressions_viral_unique" => array("day", "week", "days_28"),
            "page_impressions_nonviral" => array("day", "week", "days_28"),
            "page_impressions_nonviral_unique" => array("day", "week", "days_28"),
            "page_impressions_by_story_type" => array("day", "week", "days_28"),
            "page_impressions_by_story_type_unique" => array("day", "week", "days_28"),
            "page_impressions_by_city_unique" => array("day", "week", "days_28"),
            "page_impressions_by_country_unique" => array("day", "week", "days_28"),
            "page_impressions_by_locale_unique" => array("day", "week", "days_28"),
            "page_impressions_by_age_gender_unique" => array("day", "week", "days_28"),
            "page_impressions_frequency_distribution" => array("day", "week", "days_28"),
            "page_impressions_viral_frequency_distribution" => array("day", "week", "days_28"),
        ),
        "page_posts" => array(
            "page_posts_impressions" => array("day", "week", "days_28"),
            "page_posts_impressions_unique" => array("day", "week", "days_28"),
            "page_posts_impressions_paid" => array("day", "week", "days_28"),
            "page_posts_impressions_paid_unique" => array("day", "week", "days_28"),
            "page_posts_impressions_organic" => array("day", "week", "days_28"),
            "page_posts_impressions_organic_unique" => array("day", "week", "days_28"),
            "page_posts_served_impressions_organic_unique" => array("day", "week", "days_28"),
            "page_posts_impressions_viral" => array("day", "week", "days_28"),
            "page_posts_impressions_viral_unique" => array("day", "week", "days_28"),
            "page_posts_impressions_nonviral" => array("day", "week", "days_28"),
            "page_posts_impressions_nonviral_unique" => array("day", "week", "days_28"),
            "page_posts_impressions_frequency_distribution" => array("day", "week", "days_28"),
        ),
        "page_posts_engagement" => array(
            "post_engaged_users" => array("lifetime"),
            "post_negative_feedback" => array("lifetime"),
            "post_negative_feedback_unique" => array("lifetime"),
            "post_negative_feedback_by_type" => array("lifetime"),
            "post_negative_feedback_by_type_unique" => array("lifetime"),
            "post_engaged_fan" => array("lifetime"),
            "post_clicks" => array("lifetime"),
            "post_clicks_unique" => array("lifetime"),
            "post_clicks_by_type" => array("lifetime"),
            "post_clicks_by_type_unique" => array("lifetime"),
        ),
        "page_post_impressions" => array(
            "post_impressions" => array("lifetime"),
            "post_impressions_unique" => array("lifetime"),
            "post_impressions_paid" => array("lifetime"),
            "post_impressions_paid_unique" => array("lifetime"),
            "post_impressions_fan" => array("lifetime"),
            "post_impressions_fan_unique" => array("lifetime"),
            "post_impressions_fan_paid" => array("lifetime"),
            "post_impressions_fan_paid_unique" => array("lifetime"),
            "post_impressions_organic" => array("lifetime"),
            "post_impressions_organic_unique" => array("lifetime"),
            "post_impressions_viral" => array("lifetime"),
            "post_impressions_viral_unique" => array("lifetime"),
            "post_impressions_nonviral" => array("lifetime"),
            "post_impressions_nonviral_unique" => array("lifetime"),
            "post_impressions_by_story_type" => array("lifetime"),
            "post_impressions_by_story_type_unique" => array("lifetime"),
        ),
        "page_post_reactions" => array(
            "post_reactions_like_total" => array("lifetime"),
            "post_reactions_love_total" => array("lifetime"),
            "post_reactions_wow_total" => array("lifetime"),
            "post_reactions_haha_total" => array("lifetime"),
            "post_reactions_sorry_total" => array("lifetime"),
            "post_reactions_anger_total" => array("lifetime"),
            "post_reactions_by_type_total" => array("lifetime"),
        ),
        "page_reactions" => array(
            "page_actions_post_reactions_like_total" => array("day", "week", "days_28"),
            "page_actions_post_reactions_love_total" => array("day", "week", "days_28"),
            "page_actions_post_reactions_wow_total" => array("day", "week", "days_28"),
            "page_actions_post_reactions_haha_total" => array("day", "week", "days_28"),
            "page_actions_post_reactions_sorry_total" => array("day", "week", "days_28"),
            "page_actions_post_reactions_anger_total" => array("day", "week", "days_28"),
            "page_actions_post_reactions_total" => array("day"),
        ),
        "page_user_demographics" => array(
            "page_fans" => array("day"),
            "page_fans_locale" => array("day"),
            "page_fans_city" => array("day"),
            "page_fans_country" => array("day"),
            "page_fans_gender_age" => array("day"),
            "page_fan_adds" => array("day"),
            "page_fan_adds_unique" => array("day", "week", "days_28"),
            "page_fans_by_like_source" => array("day"),
            "page_fans_by_like_source_unique" => array("day"),
            "page_fan_removes" => array("day"),
            "page_fan_removes_unique" => array("day", "week", "days_28"),
            "page_fans_by_unlike_source_unique" => array("day"),
        ),
        "page_video_views" => array(
            "page_video_views" => array("day", "week", "days_28"),
            "page_video_views_paid" => array("day", "week", "days_28"),
            "page_video_views_organic" => array("day", "week", "days_28"),
            "page_video_views_by_paid_non_paid" => array("day", "week", "days_28"),
            "page_video_views_autoplayed" => array("day", "week", "days_28"),
            "page_video_views_click_to_play" => array("day", "week", "days_28"),
            "page_video_views_unique" => array("day", "week", "days_28"),
            "page_video_repeat_views" => array("day", "week", "days_28"),
            "page_video_complete_views_30s" => array("day", "week", "days_28"),
            "page_video_complete_views_30s_paid" => array("day", "week", "days_28"),
            "page_video_complete_views_30s_organic" => array("day", "week", "days_28"),
            "page_video_complete_views_30s_autoplayed" => array("day", "week", "days_28"),
            "page_video_complete_views_30s_click_to_play" => array("day", "week", "days_28"),
            "page_video_complete_views_30s_unique" => array("day", "week", "days_28"),
            "page_video_complete_views_30s_repeat_views" => array("day", "week", "days_28"),
            "post_video_complete_views_30s_autoplayed" => array("lifetime"),
            "post_video_complete_views_30s_clicked_to_play" => array("lifetime"),
            "post_video_complete_views_30s_organic" => array("lifetime"),
            "post_video_complete_views_30s_paid" => array("lifetime"),
            "post_video_complete_views_30s_unique" => array("lifetime"),
            "page_video_views_10s" => array("day", "week", "days_28"),
            "page_video_views_10s_paid" => array("day", "week", "days_28"),
            "page_video_views_10s_organic" => array("day", "week", "days_28"),
            "page_video_views_10s_autoplayed" => array("day", "week", "days_28"),
            "page_video_views_10s_click_to_play" => array("day", "week", "days_28"),
            "page_video_views_10s_unique" => array("day", "week", "days_28"),
            "page_video_views_10s_repeat" => array("day", "week", "days_28"),
            "page_video_view_time" => array("day"),
        ),
        "page_views" => array(
            "page_views_total" => array("day", "week", "days_28"),
            "page_views_logout" => array("day"),
            "page_views_logged_in_total" => array("day", "week", "days_28"),
            "page_views_logged_in_unique" => array("day", "week", "days_28"),
            "page_views_external_referrals" => array("day"),
            "page_views_by_profile_tab_total" => array("day", "week", "days_28"),
            "page_views_by_profile_tab_logged_in_unique" => array("day", "week", "days_28"),
            "page_views_by_internal_referer_logged_in_unique" => array("day", "week", "days_28"),
            "page_views_by_site_logged_in_unique" => array("day", "week", "days_28"),
            "page_views_by_age_gender_logged_in_unique" => array("day", "week", "days_28"),
            "page_views_by_referers_logged_in_unique" => array("day", "week"),
        ),
        "page_video_posts" => array(
            "post_video_avg_time_watched" => array("lifetime"),
            "post_video_complete_views_organic" => array("lifetime"),
            "post_video_complete_views_organic_unique" => array("lifetime"),
            "post_video_complete_views_paid" => array("lifetime"),
            "post_video_complete_views_paid_unique" => array("lifetime"),
            "post_video_retention_graph" => array("lifetime"),
            "post_video_retention_graph_clicked_to_play" => array("lifetime"),
            "post_video_retention_graph_autoplayed" => array("lifetime"),
            "post_video_views_organic" => array("lifetime", "day"),
            "post_video_views_organic_unique" => array("lifetime"),
            "post_video_views_paid" => array("lifetime", "day"),
            "post_video_views_paid_unique" => array("lifetime"),
            "post_video_length" => array("lifetime"),
            "post_video_views" => array("lifetime", "day"),
            "post_video_views_unique" => array("lifetime", "day"),
            "post_video_views_autoplayed" => array("lifetime"),
            "post_video_views_clicked_to_play" => array("lifetime"),
            "post_video_views_15s" => array("lifetime"),
            "post_video_views_60s_excludes_shorter" => array("lifetime", "day"),
            "post_video_views_10s" => array("lifetime", "day"),
            "post_video_views_10s_unique" => array("lifetime"),
            "post_video_views_10s_autoplayed" => array("lifetime"),
            "post_video_views_10s_clicked_to_play" => array("lifetime"),
            "post_video_views_10s_organic" => array("lifetime"),
            "post_video_views_10s_paid" => array("lifetime", "day"),
            "post_video_views_10s_sound_on" => array("lifetime"),
            "post_video_views_sound_on" => array("lifetime"),
            "post_video_view_time" => array("lifetime", "day"),
            "post_video_view_time_organic" => array("lifetime", "day"),
            "post_video_view_time_by_region_id" => array("lifetime", "day"),
            "post_video_view_time_by_age_bucket_and_gender" => array("lifetime"),
            "post_video_views_by_distribution_type" => array("lifetime"),
            "post_video_view_time_by_country_id" => array("lifetime"),
            "post_video_view_time_by_distribution_type" => array("lifetime"),
        ),
        "stories" => array(
            "page_content_activity_by_action_type_unique" => array("day", "week", "days_28"),
            "page_content_activity_by_age_gender_unique" => array("day", "week", "days_28"),
            "page_content_activity_by_city_unique" => array("day", "week", "days_28"),
            "page_content_activity_by_country_unique" => array("day", "week", "days_28"),
            "page_content_activity_by_locale_unique" => array("day", "week", "days_28"),
            "page_content_activity" => array("day", "week", "days_28"),
            "page_content_activity_by_action_type" => array("day", "week", "days_28"),
            "post_activity" => array("lifetime"),
            "post_activity_unique" => array("lifetime"),
            "post_activity_by_action_type" => array("lifetime"),
            "post_activity_by_action_type_unique" => array("lifetime"),
        ),
    );

    const CTA_TYPES = array(
        "OPEN_LINK", "LIKE_PAGE", "SHOP_NOW", "PLAY_GAME", "INSTALL_APP", "USE_APP", "CALL", "CALL_ME", "VIDEO_CALL",
        "INSTALL_MOBILE_APP", "USE_MOBILE_APP", "MOBILE_DOWNLOAD", "BOOK_TRAVEL", "LISTEN_MUSIC", "WATCH_VIDEO", "LEARN_MORE",
        "SIGN_UP", "DOWNLOAD", "WATCH_MORE", "NO_BUTTON", "VISIT_PAGES_FEED", "CALL_NOW", "APPLY_NOW", "CONTACT", "BUY_NOW", "GET_OFFER",
        "GET_OFFER_VIEW", "BUY_TICKETS", "UPDATE_APP", "GET_DIRECTIONS", "BUY", "MESSAGE_PAGE", "DONATE", "SUBSCRIBE", "SAY_THANKS",
        "SELL_NOW", "SHARE", "DONATE_NOW", "GET_QUOTE", "CONTACT_US", "ORDER_NOW", "START_ORDER", "ADD_TO_CART", "VIDEO_ANNOTATION", "MOMENTS",
        "RECORD_NOW", "REFER_FRIENDS", "REQUEST_TIME", "GET_SHOWTIMES", "LISTEN_NOW", "WOODHENGE_SUPPORT", "SOTTO_SUBSCRIBE", "FOLLOW_USER",
        "EVENT_RSVP", "WHATSAPP_MESSAGE", "FOLLOW_NEWS_STORYLINE", "SEE_MORE", "FIND_A_GROUP", "FIND_YOUR_GROUPS", "PAY_TO_ACCESS",
        "PURCHASE_GIFT_CARDS", "FOLLOW_PAGE", "SEND_A_GIFT", "SWIPE_UP_SHOP", "SWIPE_UP_PRODUCT", "SEND_GIFT_MONEY", "PLAY_GAME_ON_FACEBOOK"
    );


}