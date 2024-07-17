<?php

namespace classes\src\Object;

if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();

use classes\src\AbstractCrudObject;
use classes\src\Enum\AppSettings;
use classes\src\Media\Facebook;
use classes\src\Media\Medias;
use classes\src\Object\transformer\Titles;

class WebHook {
    private string $type;
    const CHALLENGE_PASS = 123456789;
    const DEBUG_LOG = "logs/debug_webhook.log";

    protected AbstractCrudObject $crud;

    function __construct(AbstractCrudObject $crud, $type) {
        $this->crud = $crud;
        $this->type = $type;
    }

    public function handle(array $request, bool $debugMode = false, string $debugFilename = ""): bool|string {
        return match ($this->type) {
            default => false,
            "hub_challenge" => $this->hubChallenge($request),
            "hook" => $this->hook($request,$debugMode,$debugFilename),
        };
    }


    public function simulateWebhook(array $content, ?int $stopAt = null): string {
        return $this->hook($content, false, "", $stopAt);
    }


    private function hubChallenge(array $request): string {
        $challengeKeys = ["hub_mode","hub_challenge","hub_verify_token"];

        return (!($request["hub_mode"] === "subscribe" && (int)$request["hub_verify_token"] === self::CHALLENGE_PASS &&
            empty(array_diff($challengeKeys,array_keys($request)))))
            ? json_encode(array("error" => "Corrupted hubChallenge received.")) : (string)$request["hub_challenge"];
    }



    private function hook(array $request, bool $debugMode = false, string $debugFilename = "", ?int $stopAt = null): string {
        try {
            if($debugMode) {
                $this->storeDebugLog("");
                $this->storeDebugLog($debugFilename);
            }

            $logDirname = TESTLOGS . "specialLogs/hooks/";
            if(!is_dir($logDirname)) mkdir($logDirname);
            $logName = $logDirname . "simulateHook.json";

            file_put_contents(TESTLOGS . "specialLogs/hook-" . time() . ".json", json_encode($request, JSON_PRETTY_PRINT));
            if($stopAt === 0) {
                file_put_contents($logName, json_encode($request, JSON_PRETTY_PRINT));
                return "";
            }


            $integrationHandler = $this->crud->integrations()->disableDepthCheck();
            $lookupHandler = $this->crud->lookupList()->disableDepthCheck();
            $igMedia = $this->crud->mediaLookup()->disableDepthCheck();
            $campaignHandler = $this->crud->campaigns()->disableDepthCheck();
            $dataHandler = $this->crud->dataHandler();
            $userHandler = $this->crud->user()->disableDepthCheck();
            $setMedia = false;
            $creatorId = 0;



            /**
             * Sometimes we'll see entries not containing a comment_id. Perhaps because it's a caption. For now- we filter that away
             */


            $expectedRawKeys = array("instagram", "entry", "id");
            foreach ($expectedRawKeys as $expectedRawKey) {
                if(!str_contains(json_encode($request),$expectedRawKey)) {
                    $error = "Expected the raw entry to contain key $expectedRawKey, but it did not.";
                    file_put_contents(TESTLOGS . "specialLogs/err.log", json_encode($error) . PHP_EOL . PHP_EOL, 8);

                }
            }

            //Ensures we always have a backup of raw data if for some reason the API fails

            $entry = $request["entry"][0];
            $mentionedAccountId = $entry["id"];

            if(array_key_exists("messaging", $entry)) {
                $content = $entry["messaging"][0];
                $contentType = "message";

                $messageAttachment = $this->crud->nestedArray($content, ["message", "attachments", 0]);
                if(!empty($messageAttachment) && $messageAttachment["type"] === "story_mention") $mediaType = "story_mention";
                elseif(!empty($messageAttachment) && $messageAttachment["type"] === "image") $mediaType = "attachment";
                elseif(!empty($this->crud->nestedArray($content, ["message", "reply_to", "story"]))) $mediaType = "story_reply";
                else $mediaType = "message";
            }
            else {
                $contentType = $mediaType = "post";
                $content = $entry["changes"][0];
            }

            $integration = $integrationHandler->getByIgId($mentionedAccountId);
            if($stopAt === 1) {
                file_put_contents($logName, json_encode([
                    "entry" => $entry, "content" => $content, "integration" => $integration
                ], JSON_PRETTY_PRINT));
                return "";
            }

            if(empty($integration)) { //Account is NOT in DB, but was received on the HOOK. Therefore, Page is not unsubscribed as it should be
                //Need to create a way to get PAGEID from only the instagram ID?
                $error = array(
                    "error_message" => "Received a mention entry for account " . $entry["id"] . ", but account is not integrated.",
                    "error_code" => 10001,
                );

                file_put_contents(TESTLOGS . "specialLogs/err.log", json_encode($error) . PHP_EOL . PHP_EOL, 8);
                return "";
            }


            $uid = (int)$integration["user_id"];
            $userAccessLevel = $userHandler->accessLevel($uid);



            $mediaHandler = new Medias();
            $mediaHandler->init("instagram");

            if($contentType === "post") {
                $accessToken = $integration["item_token"];
                $queryParams = [
                    "account_id" => $mentionedAccountId,
                    "media_id" => $content["value"]["media_id"],
                    "access_token" => $accessToken,
                ];
                $content = $mediaHandler->queryMention($queryParams, "post");
                file_put_contents(TESTLOGS . "specialLogs/mentionQuery-".time().".json", json_encode($content, JSON_PRETTY_PRINT));

                if($stopAt === 2) {
                    file_put_contents($logName, json_encode([
                        "entry" => $entry, "content" => $content, "query" => $queryParams
                    ], JSON_PRETTY_PRINT));
                    return "";
                }


                foreach (["mentioned_media"] as $key)
                    if(!array_key_exists($key, $content)) {
                        $error = "Missing $key from graphResponse";
                        file_put_contents(TESTLOGS . "specialLogs/err.log", json_encode($error) . PHP_EOL . PHP_EOL, 8);
                        return "";
                    }

                $content = is_array($content["mentioned_media"]) ? $content["mentioned_media"] : [];
                foreach (AppSettings::MENTIONS_QUERY["post"] as $key)
                    if(!array_key_exists($key, $content)) {
                        $error = "Missing $key from graphResponse";
                        file_put_contents(TESTLOGS . "specialLogs/err.log", json_encode($error) . PHP_EOL . PHP_EOL, 8);
                        return "";
                    }

                $content["timestamp"] = $this->ensureTimestamp($content["timestamp"]);
                $content["shortcode"] = $dataHandler->instagramUrlShortCode($content["permalink"]);
                $content["origin"] = "post_mention";
                $content["type"] = $contentType;
                $content["mid"] = $content["id"];

                if($this->crud->isBrandTester($userAccessLevel)) {
                    $content["origin"] = "special_mention";
                    $content["lookup_id"] = $integration["id"];
                    $content["display_url"] = $content["media_url"];

                    file_put_contents(TESTLOGS . "specialLogs/brandtesterWebhookFinalContent.json", json_encode($content, JSON_PRETTY_PRINT));
                    $igMedia->insertNewMedia($content);
                    return "";
                }

                if(!empty($igMedia->getByMid($content["id"]))) return "";
                $creator = $lookupHandler->getByUsername($content["username"]);
                if(empty($creator)) return "";
                $creatorId = (int)$creator["id"];



                if($stopAt === 3) {
                    file_put_contents($logName, json_encode([
                        "entry" => $entry, "content" => $content, "creator" => $creator
                    ], JSON_PRETTY_PRINT));
                    return "";
                }


//                if($content["media_type"] === "VIDEO") {
//                    if($this->crud->settings->use_scraper) {
//                        $this->crud->multiArrayLog($content, "video_before_scrape");
//                        $scraper = $this->crud->scraper();
//                        $content["video_url"] = $content["media_url"];
//                        $content["display_url"] = (string)$scraper->getPostPageThumbnail("", $scraper->postPageFirstScrape($content["permalink"]));
//                        $this->crud->multiArrayLog($content, "video_after_scrape");
//                    }
//                }
//                else $content["display_url"] = $content["media_url"];
                $content["display_url"] = $content["media_url"];
                unset($content["media_url"]);
                unset($content["id"]);
                if($this->crud->settings->download_media) {
                    $content = $dataHandler->downloadMediasAndUpdateUrl($content)[0];
                }

                $setMedia = true;


            }
            else { //Message
                $messageHandler = $this->crud->messages()->disableDepthCheck();
                $conversationHandler = $this->crud->conversations()->disableDepthCheck();

                $facebookIntegration = $integrationHandler->getRelatedIntegration($integration["item_id"]);
                $content["timestamp"] = $this->ensureTimestamp($content["timestamp"]);
                $accessToken = $facebookIntegration["item_token"];
                $messageId = $content["message"]["mid"];
                $senderId = (int)$content["sender"]["id"];
                $recipientId = (int)$content["recipient"]["id"];
                $messageDeleted = $this->crud->nestedArray($content, ["message","is_deleted"], false);
                $messageText = $this->crud->nestedArray($content, ["message","text"], "");
                $permalink = $this->crud->nestedArray($content, ["message", "attachments", 0, "payload", "url"], "");
                if(empty($permalink)) $permalink = $this->crud->nestedArray($content, ["message", "reply_to", "story", "url"], "");
                $messageTimestamp = $content["timestamp"];

                if($messageDeleted) $textShort = "Deleted message";
                elseif($mediaType === "story_mention") $textShort = Titles::truncateStr("Mentioned you in their story", 20);
                elseif($mediaType === "story_reply") $textShort = Titles::truncateStr("Replied to your story: $messageText", 20);
                elseif($mediaType === "attachment") $textShort = "Sent an image";
                elseif(!empty($messageText)) $textShort = Titles::truncateStr($messageText, 20);
                else $textShort = "";

                $existingMessage = $messageHandler->getByMessageId($messageId, ["id", "conversation_id"]);
                if($messageDeleted) { //Message was unsent
                    if($existingMessage) {
                        $messageHandler->update(["is_deleted" => 1], ["id" => $existingMessage["id"]]);
                        $conversationHandler->update([
                            "last_message_at" => $messageTimestamp,
                            "text_short" => $textShort,
                        ], ["id" => $existingMessage["conversation_id"]]);
                    }
                    return "";
                }
                if(!empty($existingMessage)) {
                    $conversationHandler->update([
                        "last_message_at" => $messageTimestamp,
                        "text_short" => $textShort,
                    ], ["id" => $existingMessage["conversation_id"]]);

                    return "";
                }

                $targetId = (int)$integration["item_id"] === $senderId ? $recipientId : $senderId;
                $messageContent = $mediaHandler->queryMessage($accessToken, $targetId);
                foreach (["name", "username"] as $expectedKey) if(!array_key_exists($expectedKey, $messageContent)) {
                    file_put_contents(
                        TESTLOGS . "specialLogs/WEBHOOK-LOG.log", "Message data did not include: $expectedKey; " . json_encode($messageContent)
                        . PHP_EOL . PHP_EOL, 8
                    );
                    return "";
                }



                $creator = $lookupHandler->getByUsername($messageContent["username"]);
                if(empty($creator)) {
                    if(!$this->crud->settings->allow_none_creator_messaging) {
                        file_put_contents(
                            TESTLOGS . "specialLogs/WEBHOOK-LOG.log", "Creator was not found; " . json_encode($messageContent)
                            . PHP_EOL . PHP_EOL, 8
                        );
                        return "";
                    }
                }
                else $creatorId = (int)$creator["id"];


                $outbound = (int)((int)$integration["item_id"] === $senderId);

                $conversationParams = [
                    "owner_user_id" => $uid,
                    "provider" => "instagram",
                    "participant_username" => $messageContent["username"],
                    "participant_name" => $messageContent["name"],
                    "participant_id" => $targetId,
                    "text_short" => $textShort,
                    "last_message_at" => $messageTimestamp,
                    "last_message_outbound" => $outbound
                ];
                if(!$outbound && array_key_exists("profile_pic", $messageContent)) $conversationParams["profile_picture"] = $messageContent["profile_pic"];
                $conversationId = $conversationHandler->createOrUpdate($conversationParams);
                if(empty($conversationId)) {
                    file_put_contents(
                        TESTLOGS . "specialLogs/WEBHOOK-LOG.log", "Failed to set conversation: $conversationId; " . json_encode($conversationParams)
                        . PHP_EOL . PHP_EOL, 8
                    );
                    return "";
                }


                if($mediaType === "story_mention") $messageText = $messageContent["username"] . " mentioned you in their story";
                elseif($mediaType === "story_reply") $messageText = "Replied to your story: $messageText";

                $messageParams = [
                    "mid" => $messageId,
                    "recipient_id" => $recipientId,
                    "sender_id" => $senderId,
                    "text" => $messageText,
                    "timestamp" => $messageTimestamp,
                    "outbound" => $outbound,
                    "conversation_id" => $conversationId,
                    "type" => $mediaType,
                    "attached_media" => $permalink,
                ];


                if($mediaType === "story_mention") {
                    $content = [
                        "username" => $messageContent["username"],
                        "display_url" => $permalink,
                        "mid" => $messageId,
                        "timestamp" => $messageTimestamp,
                        "permalink" => $permalink,
                        "lookup_id" => $creatorId,
                        "type" => "story",
                        "origin" => "story_mention",
                    ];


                    if($this->crud->isBrandTester($userAccessLevel)) {
                        $content["origin"] = "special_mention";
                        $content["lookup_id"] = $integration["id"];

                    }

//                    if($this->crud->settings->use_scraper) {
//                        $ownerStoryData = $this->crud->handler()->userStorySearch($creator["username"], $creator["ig_id"]);
//                        if(!empty($ownerStoryData) && array_key_exists("media", $ownerStoryData) && !empty($ownerStoryData["media"])) {
//                            file_put_contents(TESTLOGS . "specialLogs/storyitemwebhook.json", json_encode($ownerStoryData, JSON_PRETTY_PRINT));
//                            foreach (array_reverse($ownerStoryData["media"]) as $mediaItem) {
//                                if(empty($mediaItem)) continue;
//                                if(in_array($integration["item_name"], $mediaItem["mentions"])) {
//                                    $content["display_url"] = $mediaItem["display_urls"]["image_url"];
//                                    $content["video_url"] = $mediaItem["display_urls"]["video_url"];
//                                    break;
//                                }
//                            }
//                        }
//                    }


                    if($this->crud->settings->download_media) {
                        $downloadedContent = $dataHandler->downloadMediasAndUpdateUrl(
                            $content,
                            false,
                            "",
                            "",
                            "story-" . $messageContent["username"] . "-" . (round((time() / rand(0,99) + rand(0,999)) / 2 / 100)) . ".png",
                            true
                        )[0];
                        $displayUrlExt = pathinfo($downloadedContent["display_url"])["extension"];
                    }
                    else {
                        $downloadedContent = $this->crud->getFileAndHeaderFilename($content["display_url"]);
                        $responseHeaders = $downloadedContent["headers"];
                        $responseContentType = $this->crud->contentTypeFromHeaders($responseHeaders);
                        $displayUrlExt = $this->crud->extensionFromContentType($responseContentType);
                    }

                    $content["media_type"] = $downloadedContent["media_type"] = strtoupper($this->crud->extToMediaType($displayUrlExt));
                    if($this->crud->settings->download_media) $content = $downloadedContent;
                    $setMedia = true;
                }


                $messageHandler->create($messageParams);
            }


            $creatorActiveCampaign = $campaignHandler->creatorActiveCampaign($creatorId);
            if($campaignHandler->isCampaignActive($creatorActiveCampaign, (int)$content["timestamp"])) {
                if($creatorActiveCampaign["content_type"] === "mixed" || ($contentType === $creatorActiveCampaign["content_type"] || ($creatorActiveCampaign["content_type"] === "story" && $mediaType === "story_mention")))
                    $content["campaign_id"] = (int)$creatorActiveCampaign["id"];
                elseif($creatorActiveCampaign["content_type"] === "reel" && $this->crud->nestedArray($content, ["media_type"]) === "VIDEO")
                    $content["campaign_id"] = (int)$creatorActiveCampaign["id"];
            }

            file_put_contents(TESTLOGS . "specialLogs/webhookItem.json", json_encode([$creatorId, $content], JSON_PRETTY_PRINT));
            if($setMedia) $igMedia->insertNewMedia($content, $creatorId);
            return "";


        } catch (\Exception $e) {

            file_put_contents(TESTLOGS . "specialLogs/err.log", json_encode($e) . PHP_EOL . PHP_EOL, 8);

            return $e;
        }
    }

    private function storeDebugLog(string|array $str) {
        $text = date("d-m-Y H:i:s") . "  -  " . (is_array($str) ? json_encode($str) : $str) . PHP_EOL;
        file_put_contents(ROOT . self::DEBUG_LOG, $text, 8);
    }


    private function ensureTimestamp(string|int $timestamp): int {
        if(is_numeric($timestamp)) {
            if(strlen((string)$timestamp) > strlen((string)time()))
                $timestamp = floor((int)$timestamp / 1000);
        }
        if(!is_numeric($timestamp)) $timestamp = strtotime($timestamp);
        return $timestamp;
    }

}















