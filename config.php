<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW")) {
    require $_SERVER["DOCUMENT_ROOT"]."/includes/html/global_utility/404.php";
    exit;
}

date_default_timezone_set("Europe/Copenhagen"); //Very important. This sets the server-time



const LIVE_PRODUCTION = false;
//const LIVE_PRODUCTION = true;

if(LIVE_PRODUCTION) {
    define("ROOT",$_SERVER["DOCUMENT_ROOT"]."/"); //Root folder LIVE SERVER
    define("HOST","https://app.contentstage.de/");//host URL
    define("HOSTING", "LIVE");

    /**
     * DATABASE AUTH SETTINGS
     */
    define("DB_HOST","mysql");
    define("DB_NAME","db683902_1");
    define("DB_USER","db683902_1");
    define("DB_PASS","gyd2MEH8kmt4fng!trj");
}
else {
    define("ROOT",""); //Root folder for localhost
    define("HOST","https://localhost/goodbrandslove/"); //LOCAL HOST URL
    define("HOSTING", "localhost");

    /**
     * DATABASE AUTH SETTINGS
     */
    define("DB_HOST","localhost"); /* Define database (FRED LOCALHOST) */
    define("DB_NAME","goodbrandslove");
    define("DB_USER","root");
    define("DB_PASS","");
}





/**
 * SITE SETTINGS
 */
//define("ROOT",$_SERVER["DOCUMENT_ROOT"]."/goodbrandslove/"); //Root folder LIVE SERVER
//define("HOST","https://goodbrandslove.instaapi.com/");//host URL
//define("HOSTING", "LIVE");
define("SITE_NAME","Contentstage.de"); //Site name
define("FIRM_NAME","Content<span style='color:#727cf5;'>Stage</span>"); //Company name
define("BRAND_NAME","ContentStage"); //Brand
define("LOGO_HEADER", HOST . "images/logo-goodbrands-04.svg"); //Transparent version of the logo
define("LOGO_HEADER_WHITE", HOST . "images/logo_white.png"); //Transparent version of the logo
define("LOGO_ICON", HOST . "images/logo-icon.png"); //Transparent version of the logo
//define("LOGO_ICON", HOST . "images/logo-goodbrands_Zeichenfläche 1 Kopie 2.svg"); //Transparent version of the logo
define("LOGO_ICON_WHITE", HOST . "images/logo-icon.png"); //Transparent version of the logo
//define("LOGO_ICON_WHITE", HOST . "images/logo-icon-white.png"); //Transparent version of the logo
define("PLATFORM_VERSION", "1-0014"); //Version. is changed whenever an update is made to a css and or javascript file
define("GMT_TIME", "GMT+2 (CET)"); //Timezone that is visually displayed on graphs on most pages.


/**
 * Company legal
 */
DEFINE("COMPANY_NAME", "Goodbrands Love GmbH");
DEFINE("COMPANY_ALIAS", implode(" and ", ['Contentstage', 'GoodbrandsLove']));
DEFINE("COMPANY_STREET_ADDRESS", "Stegner Strasse 2a");
DEFINE("COMPANY_CITY", "Kirchzarten");
DEFINE("COMPANY_POSTAL", "79199");
DEFINE("COMPANY_COUNTRY", "United States");
DEFINE("COMPANY_ADDRESS_STRING", implode(" ", [COMPANY_NAME, COMPANY_STREET_ADDRESS, COMPANY_CITY, COMPANY_POSTAL, COMPANY_COUNTRY]));
DEFINE("COMPANY_EMAIL", "support@contentstage.de");
DEFINE("COMPANY_WEBSITE", "www.contentstage.de");


/**
 * Other setting
 */
define("WEEK_DAY_INTERVAL", array(1 => "monday", 2 => "tuesday", 3 => "wednesday", 4 => "thursday", 5 => "friday", 6 => "saturday", 7 => "sunday"));

/**
 * LOG PATHS
 */
define("ERR_LOG",ROOT."logs/error.log"); //ErrorLog
define("EXTERNAL_GRAPH_LOG", ROOT . "logs/external/graphRequests.log"); //external graph loh
define("HTTP_LOGS",ROOT."logs/debugging/httpLogs.log"); //http logs made from on-site
define("DEBUGGING_LOG",ROOT."logs/debugging/debug.log"); //minor debugging log
define("CRON_LOGS",ROOT . "logs/cronLogs/"); //Cronjob log-directory


/**
 * Libraries
 */
define("PARSER",ROOT."classes/lib/html_parser/autoload.php"); //HTML parser
define("HASHTAG_LIST",ROOT."includes/lib/hashtags/hashtags.json"); //Hashtag list
define("GENDER_LIB",ROOT."includes/lib/names/gender_and_origin.json"); //GenderLibrary
define("GENDER_TO_BE_ADDED",ROOT."includes/lib/names/toBeAdded.txt"); //GenderLibrary : TO BE ADDED
define("COUNTRY_SEARCH_LIB",ROOT."includes/lib/countries/countries_and_cities.json"); //CountryLibrary to search through
define("COUNTRY_NAME_BY_CODE",ROOT."includes/lib/countries/countrycode_to_country.json"); //CountryLibrary to get name by country code
define("DEBUG_COUNTRY",ROOT."includes/lib/countries/debug.json"); //Countries
define("BAD_FILTER_NAMES",ROOT."includes/lib/old-filters/names.json"); //Bad filter names
define("BAD_FILTER_COUNTRIES",ROOT."includes/lib/old-filters/whitelisted_countries.json"); //Bad filter countries



/**
 * CRONJOB - log-memory
 */
define("CRONLOG_MAX_ENTRIES",200); //Deletes log after it has run x times


/**
 * Other variables
 */
define("MODIFY_ACTION", 2); //Defining action-level for access-points as MODIFY ONLY
define("READ_ACTION", 1); //Defining action-level for access-points as READ ONLY


/**
 * File manager paths
 */
define("IMAGE_PATH",HOST."images/"); //Images directory
define("USER_NO_PB","nopp.png"); //Name of profile picture if user does not have one
define("USER_PB","profilePicture.png"); //Name of any users profile picture
define("USER_BASE_FILE","baseInfo.json"); //Contains basic info on the employee as well as "change-log" and "payout history"
define("USER_LOGS", "logs/"); //Basic log directory
define("TESTLOGS", ROOT . "testLogs/"); //Basic log directory
define("USER_CONNECTION_LOG", "connections.log"); //Employee login-log
define("ENTRY_PREFIX", array( //Report prefixes
    "user" => "user_",
));


/**
 * STRIPE
 */



