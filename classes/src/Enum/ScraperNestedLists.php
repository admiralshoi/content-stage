<?php

namespace classes\src\Enum;

class ScraperNestedLists {



    public const PROFILE_PAGE_SCRAPE = array(
        "biography" => array(
            "type" => "string",
            "nest" => array("biography")
        ),
        "external_url" => array(
            "type" => "string",
            "nest" => array("external_url")
        ),
        "fbid" => array(
            "type" => "int",
            "nest" => array("fbid"),
        ),
        "follows_count" => array(
            "type" => "int",
            "nest" => array("edge_follow", "count"),
        ),
        "followers_count" => array(
            "type" => "int",
            "nest" => array("edge_followed_by", "count"),
        ),
        "full_name" => array(
            "type" => "string",
            "nest" => array("full_name"),
        ),
        "private_account" => array(
            "type" => "int",
            "nest" => array("is_private"),
        ),
        "business_account" => array(
            "type" => "int",
            "nest" => array("is_business_account"),
        ),
        "business_email" => array(
            "type" => "string",
            "nest" => array("business_email"),
        ),
        "business_phone_number" => array(
            "type" => "string",
            "nest" => array("business_phone_number"),
        ),
        "business_category_name" => array(
            "type" => "string",
            "nest" => array("business_category_name"),
        ),
        "category_name" => array(
            "type" => "string",
            "nest" => array("category_name"),
        ),
        "connected_fb_page" => array(
            "type" => "int",
            "nest" => array("connected_fb_page"),
        ),
        "ig_id" => array(
            "type" => "string",
            "nest" => array("id"),
        ),
        "media_count" => array(
            "type" => "int",
            "nest" => array("edge_owner_to_timeline_media", "count"),
        ),
        "profile_picture" => array(
            "type" => "string",
            "nest" => array("profile_pic_url_hd"),
        ),
        "username" => array(
            "type" => "string",
            "nest" => array("username"),
        ),
        "media" => array(
            "type" => "array",
            "nest" => array("edge_owner_to_timeline_media", "edges"),
        ),
        "reels_media" => array(
            "type" => "array",
            "nest" => array("edge_felix_video_timeline", "edges"),
        ),
        "media_cursor" => array(
            "type" => "array",
            "nest" => array("edge_owner_to_timeline_media", "page_info"),
        ),
        "api" => array(
            "type" => "int",
            "nest" => array("api"),
        )
    );

    public const MEDIA_DATA_API = array(
        "comments_count" => array(
            "type" => "int",
            "nest" => array("comments_count")
        ),
        "like_count" => array(
            "type" => "int",
            "nest" => array("like_count")
        ),
        "caption" => array(
            "type" => "string",
            "nest" => array("caption")
        ),
        "permalink" => array(
            "type" => "string",
            "nest" => array("permalink")
        ),
        "media_type" => array(
            "type" => "string",
            "nest" => array("media_type")
        ),
        "timestamp" => array(
            "type" => "string|int",
            "nest" => array("timestamp")
        ),
        "id" => array(
            "type" => "string",
            "nest" => array("id")
        ),
    );

    public const MEDIA_DATA_SCRAPE = array(
        "comments_count" => array(
            "type" => "int",
            "nest" => array("node", "edge_media_to_comment", "count")
        ),
        "like_count" => array(
            "type" => "int",
            "nest" => array("node", "edge_media_preview_like", "count")
        ),
        "view_count" => array(
            "type" => "int",
            "nest" => array("node", "video_view_count")
        ),
        "caption" => array(
            "type" => "string",
            "nest" => array("node", "edge_media_to_caption", "edges", 0, "node", "text")
        ),
        "shortcode" => array(
            "type" => "string",
            "nest" => array("node", "shortcode")
        ),
        "is_video" => array(
            "type" => "bool",
            "nest" => array("node", "is_video")
        ),
        "timestamp" => array(
            "type" => "string|int",
            "nest" => array("node", "taken_at_timestamp")
        ),
        "mid" => array(
            "type" => "string",
            "nest" => array("node", "id")
        ),
        "location" => array(
            "type" => "array",
            "nest" => array("node", "location")
        ),
        "display_url" => array(
            "type" => "string",
            "nest" => array("node", "display_url")
        ),
        "pinned" => array(
            "type" => "array",
            "nest" => array("node", "pinned_for_users")
        ),
    );



    public const MEDIA_DATA_VIDEO_SCRAPE = array(
        "comments_count" => array(
            "type" => "int",
            "nest" => array("node", "edge_media_to_comment", "count")
        ),
        "like_count" => array(
            "type" => "int",
            "nest" => array("node", "edge_media_preview_like", "count")
        ),
        "view_count" => array(
            "type" => "int",
            "nest" => array("node", "video_view_count")
        ),
        "caption" => array(
            "type" => "string",
            "nest" => array("node", "edge_media_to_caption", "edges", 0, "node", "text")
        ),
        "shortcode" => array(
            "type" => "string",
            "nest" => array("node", "shortcode")
        ),
        "is_video" => array(
            "type" => "int",
            "nest" => array("node", "is_video")
        ),
        "timestamp" => array(
            "type" => "string|int",
            "nest" => array("node", "taken_at_timestamp")
        ),
        "mid" => array(
            "type" => "string",
            "nest" => array("node", "id")
        ),
        "location" => array(
            "type" => "array",
            "nest" => array("node", "location")
        ),
        "display_url" => array(
            "type" => "string",
            "nest" => array("node", "thumbnail_src")
        ),
        "video_url" => array(
            "type" => "string",
            "nest" => array("node", "video_url")
        ),
        "pinned" => array(
            "type" => "array",
            "nest" => array("node", "pinned_for_users")
        ),
    );



    public const HASHTAG_EXPLORE_RECENT_SECTIONS = array(
        "medias" => array(
            "type" => "array",
            "nest" => array("layout_content", "medias")
        ),
    );


    public const HASHTAG_EXPLORE_MEDIA_DATA = array(
        "mid" => array(
            "type" => "string",
            "nest" => array("media", "id")
        ),
        "timestamp" => array(
            "type" => "int",
            "nest" => array("media", "taken_at")
        ),
        "media_type" => array(
            "type" => "int",
            "nest" => array("media", "media_type")
        ),
        "shortcode" => array(
            "type" => "string",
            "nest" => array("media", "code")
        ),
        "username" => array(
            "type" => "string",
            "nest" => array("media", "user", "username"),
        ),
        "caption" => array(
            "type" => "string",
            "nest" => array("media", "caption", "text"),
        ),
        "display_url" => array(
            "type" => "string",
            "nest" => array("media", "image_versions2", "candidates", 0, "url"),
        ),
        "video_url" => array(
            "type" => "string",
            "nest" => array("media", "video_versions", 0, "url"),
        ),
        "comments_count" => array(
            "type" => "int",
            "nest" => array("media", "comment_count"),
        ),
        "like_count" => array(
            "type" => "int",
            "nest" => array("media", "like_count"),
        ),
        "play_count" => array(
            "type" => "int",
            "nest" => array("media", "play_count"),
        ),
        "has_audio" => array(
            "type" => "int",
            "nest" => array("media", "has_audio"),
        ),
    );

    public const HASHTAG_MEDIA_EXTRACTING = array(
        "image_url" => array(
            "type" => "string",
            "nest" => array("image_versions2", "candidates", 0, "url")
        ),
    );

    public const HASHTAG_EXPLORE = array(
        "id" => array(
            "type" => "string",
            "nest" => array("id")
        ),
        "media_count" => array(
            "type" => "int",
            "nest" => array("media_count")
        ),
        "sections" => array(
            "type" => "array",
            "nest" => array("top", "sections")
        )
    );


    public const HASHTAG_LOOKUP = array(
        "name" => array(
            "type" => "string",
            "nest" => array("hashtag", "name")
        ),
        "media_count" => array(
            "type" => "int",
            "nest" => array("hashtag", "media_count")
        ),
        "picture" => array(
            "type" => "string",
            "nest" => array("hashtag", "profile_pic_url")
        ),
        "subtitle" => array(
            "type" => "string",
            "nest" => array("hashtag", "search_result_subtitle")
        )
    );





    public const STORY_BASE_DATA = array(
        "id" => array(
            "type" => "string",
            "nest" => array(0, "id")
        ),
        "expiring_at" => array(
            "type" => "int",
            "nest" => array(0, "expiring_at")
        ),
        "ig_id" => array(
            "type" => "string",
            "nest" => array(0, "user", "pk"),
        ),
        "username" => array(
            "type" => "string",
            "nest" => array(0, "user", "username"),
        ),
        "full_name" => array(
            "type" => "string",
            "nest" => array(0, "user", "full_name"),
        ),
        "private_account" => array(
            "type" => "int",
            "nest" => array(0, "user", "is_private"),
        ),
        "profile_picture" => array(
            "type" => "string",
            "nest" => array(0, "user", "profile_pic_url"),
        ),
        "media" => array(
            "type" => "array",
            "nest" => array(0, "items"),
        ),
    );



    public const STORY_MEDIA_DATA = array(
        "timestamp" => array(
            "type" => "int",
            "nest" => array("taken_at")
        ),
        "image_url" => array(
            "type" => "string",
            "nest" => array("image_versions2", "candidates", 0, "url")
        ),
        "video_url" => array(
            "type" => "string",
            "nest" => array("video_versions", 0, "url")
        ),
        "mentions" => array(
            "type" => "array",
            "nest" => array("story_bloks_stickers")
        ),
    );



    public const TAGGED_PAGE = array(
        "count" => array(
            "type" => "int",
            "nest" => array("count")
        ),
        "edges" => array(
            "type" => "array",
            "nest" => array("edges")
        ),
    );

    public const TAGGED_PAGE_EDGE = array(
        "shortcode" => array(
            "type" => "string",
            "nest" => array("node", "shortcode")
        ),
        "comments_count" => array(
            "type" => "int",
            "nest" => array("node", "edge_media_to_comment", "count")
        ),
        "like_count" => array(
            "type" => "int",
            "nest" => array("node", "edge_liked_by", "count")
        ),
        "timestamp" => array(
            "type" => "int",
            "nest" => array("node", "taken_at_timestamp")
        ),
        "mid" => array(
            "type" => "string",
            "nest" => array("node", "id")
        ),
        "username" => array(
            "type" => "string",
            "nest" => array("node", "owner", "username")
        ),
        "is_video" => array(
            "type" => "bool",
            "nest" => array("node", "is_video")
        ),
        "view_count" => array(
            "type" => "int",
            "nest" => array("node", "video_view_count")
        ),
        "display_url" => array(
            "type" => "string",
            "nest" => array("node", "thumbnail_src")
        ),
        "caption" => array(
            "type" => "string",
            "nest" => array("node", "edge_media_to_caption", "edges", 0, "node", "text")
        ),
    );



    public const POST_PAGE_MEDIA_INFO = array(
        "timestamp" => array(
            "type" => "int",
            "nest" => array("taken_at")
        ),
        "mid" => array(
            "type" => "string",
            "nest" => array("pk")
        ),
        "media_type" => array(
            "type" => "int",
            "nest" => array("media_type")
        ),
        "shortcode" => array(
            "type" => "string",
            "nest" => array("code")
        ),
        "location" => array(
            "type" => "array",
            "nest" => array("location")
        ),
        "username" => array(
            "type" => "string",
            "nest" => array("user", "username")
        ),
        "private_account" => array(
            "type" => "int",
            "nest" => array("user", "is_private"),
        ),
        "like_count" => array(
            "type" => "int",
            "nest" => array("like_count"),
        ),
        "comments_count" => array(
            "type" => "int",
            "nest" => array("comment_count"),
        ),
        "display_url" => array(
            "type" => "string",
            "nest" => array("image_versions2", "candidates", 0, "url"),
        ),
        "video_url" => array(
            "type" => "string",
            "nest" => array("video_versions", 0, "url"),
        ),
        "carousel" => array(
            "type" => "array",
            "nest" => array("carousel_media"),
        ),
        "caption" => array(
            "type" => "string",
            "nest" => array("caption", "text")
        ),
        "has_audio" => array(
            "type" => "int",
            "nest" => array("has_audio"),
        ),
        "view_count" => array(
            "type" => "int",
            "nest" => array("view_count"),
        ),
        "play_count" => array(
            "type" => "int",
            "nest" => array("play_count"),
        ),
    );




    const POST_PAGE_MEDIA_TO_MEDIA = [
        "shortcode" => array(
            "type" => "string",
            "nest" => array("shortcode"),
        ),
        "mid" => array(
            "type" => "string",
            "nest" => array("mid"),
        ),
        "like_count" => array(
            "type" => "int",
            "nest" => array("like_count"),
        ),
        "comments_count" => array(
            "type" => "int",
            "nest" => array("comments_count"),
        ),
        "timestamp" => array(
            "type" => "int",
            "nest" => array("timestamp"),
        ),
        "caption" => array(
            "type" => "string",
            "nest" => array("caption")
        ),
        "username" => array(
            "type" => "string",
            "nest" => array("username")
        ),
        "has_audio" => array(
            "type" => "int",
            "nest" => array("has_audio"),
        ),
        "view_count" => array(
            "type" => "int",
            "nest" => array("view_count"),
        ),
        "play_count" => array(
            "type" => "int",
            "nest" => array("play_count"),
        ),
        "display_url" => array(
            "type" => "string",
            "nest" => array("display_url"),
        ),
        "video_url" => array(
            "type" => "string",
            "nest" => array("video_url"),
        ),
        "media_type" => array(
            "type" => "string",
            "nest" => array("media_type")
        ),
        "carousel" => array(
            "type" => "array",
            "nest" => array("carousel")
        ),
    ];



}