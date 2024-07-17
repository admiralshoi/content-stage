<?php

namespace classes\src\Enum;

use http\Url;

class API {

    const tiktokAppId = 87654;
    const tiktokClientId = "hggffdc";
    const tiktokClientSecret = "hggbfds";
    const tiktokScopes = array("user.info.basic");
    const tiktokOauthLink = "https://www.tiktok.com/auth/authorize/";
    const tiktokAccessTokenLink = "https://open-api.tiktok.com/oauth/access_token/";
    const tiktokUserInfoLink = "https://open.tiktokapis.com/v2/user/info/";
    const redirect_uri = HOST . "?page=settings&a=connections";


    const stripeAuthReturnUriParams = array("page" => "stripe_auth", "b" => "rt", "uid" => "__UID__", "hash" => "__HASH__");
    const stripeAuthRefreshUriParams = array("page" => "stripe_auth", "b" => "ref", "uid" => "__UID__");

}