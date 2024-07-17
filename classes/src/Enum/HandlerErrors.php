<?php

namespace classes\src\Enum;

class HandlerErrors {

    const SCRAPER_ERROR = ["status" => "error", "error" => ["code" => 1021, "message" => "The scraper returned an error. Try again later"]];
    const SCRAPER_USER_UNAVAILABLE = ["status" => "error", "error" => ["code" => 1091, "message" => "User is not able to be scraped. Try again later"]];



    const INSUFFICIENT_CREATOR_RELATIONS = ["status" => "error", "error" => ["code" => 391, "message" => "The user you're trying to assign this campaign to does not have sufficient access to view the current selected creators. Please have them add the creators first or remove creators they haven't looked up themselves"]];
    const NO_AVAILABLE_CREATORS = ["status" => "error", "error" => ["code" => 392, "message" => "No available creators given"]];


    const INVALID_EMAIL = ["status" => "error", "error" => ["code" => 381, "message" => "Invalid email"]];
    const INVALID_USERNAME = ["status" => "error", "error" => ["code" => 382, "message" => "Invalid username"]];
    const NO_INPUT = ["status" => "error", "error" => ["code" => 349, "message" => "Missing input"]];

    const EMAIL_RESET_FAILED = ["status" => "error", "error" => ["code" => 610, "message" => "Failed the attempt to reset password. Try again later"]];
    const EMAIL_RESET_INVALID_DIGITS = ["status" => "error", "error" => ["code" => 611, "message" => "The digits are either invalid or has expired. Try again"]];
    const EMPTY_INPUT = ["status" => "error", "error" => ["code" => 627, "message" => "The inputs cannot be empty"]];


    const CREATE_COOKIE_ERROR = ["status" => "error", "error" => ["code" => 701, "message" => "Failed to set new cookie. Try again later"]];
    const CREATE_COOKIE_ALREADY_EXIST = ["status" => "error", "error" => ["code" => 702, "message" => "This cookie already exists"]];


    const CAMPAIGN_CREATION_ERROR = ["status" => "error", "error" => ["code" => 313, "message" => "Failed to create campaign. Try again later"]];
    const CAMPAIGN_CREATION_TIME_RANGE_ERROR = ["status" => "error", "error" => ["code" => 314, "message" => "The end-time must be at earliest tomorrow, and greater than the start-time"]];


    const SCRAPER_STORY_ERROR = ["status" => "error", "error" => ["code" => 10241, "message" => "The user either has no stories or their profile is private"]];
    const FAILED_TO_GET_MEDIA = ["status" => "error", "error" => ["code" => 545, "message" => "Failed to get media"]];

    const INSUFFICIENT_PERMISSIONS = ["status" => "error", "error" => ["code" => 401, "message" => "You are not authorized to make this action"]];


}