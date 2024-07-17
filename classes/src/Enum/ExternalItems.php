<?php
namespace classes\src\Enum;

class ExternalItems {

    const ORIGIN_EXTERNAL = 0;
    const ORIGIN_INTERNAL = 1;
    const ORIGIN_AUTOMATED = 2;

    const INITIALIZED_DIRECT = 0;
    const INITIALIZED_HASHTAG = 1;
    const INITIALIZED_FOLLOWS = 2;
    const INITIALIZED_FOLLOWERS = 3;
    const INITIALIZED_IMPLICIT = 4; //such as hashtags used on a media, that is then stored for future hashtag-scrapes

}