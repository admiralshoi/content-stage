<?php

namespace classes\src\Media;

use classes\src\AbstractCrudObject;
use classes\src\Media\Facebook;
use classes\src\Media\Instagram;

class Medias extends AbstractMedias {
    protected string $mediaType = "";
    private Facebook|Instagram $media;
    private const CLASSES = array(
        "facebook" => "classes\src\Media\Facebook",
        "instagram" => "classes\src\Media\Instagram",
    );

    function __construct() {parent::__construct();}

    public function init(string $authType): bool {
        if(empty($authType)) return false;
        if(!in_array($authType, parent::AuthTypes)) return false;

        $className = self::CLASSES[$authType];
        if(!class_exists($className)) return false;

        $this->media = new $className;
        $this->mediaType = $authType;
        return true;
    }

    public function isInit(string $authType = null): bool {
        if(empty($authType)) return !empty($this->mediaType);
        return $this->mediaType === $authType;
    }



    /*
     * Shared insights methods
     */
    public function getAccounts(array|string $item): array { return $this->media->getAccounts($item); }
    public function getInsights(string $accessToken, string|int $accountId, array $metrics, string $period, array $timeInterval = array() ): array {
        return $this->media->getInsights($accessToken, $accountId, $metrics, $period, $timeInterval);
    }
    public function businessDiscovery(string $username): array { return $this->media->businessDiscovery($username); }

    /*
     * Facebook post creation methods
     */
    public function createFbPhotoPost(string|int $pageId, string $accessToken, string $caption, string $imageUrl): array {
        if($this->mediaType !== "facebook") return array("error" => "This is only supported for Facebook");
        return $this->media->createPhotoPost($pageId, $accessToken, $caption, $imageUrl);
    }
    public function createFbLinkPost(string|int $pageId, string $accessToken, string $message, string $link, array $cta = array()): array {
        if($this->mediaType !== "facebook") return array("error" => "This is only supported for Facebook");
        return $this->media->createLinkPost($pageId, $accessToken, $message, $link, $cta);
    }
    public function createFbVideoPost(string|int $pageId, string $accessToken, string $title, string $description, string $videoUrl): array {
        if($this->mediaType !== "facebook") return array("error" => "This is only supported for Facebook");
        return $this->media->createVideoPost($pageId, $accessToken, $title, $description, $videoUrl);
    }



    /*
     * Instagram post creation methods
     */
    public function createIgPost(string|int $accountId, string $accessToken, string $mediaUrl, string $caption, bool $isImage = true): array {
        if($this->mediaType !== "instagram") return array("error" => "This is only supported for Instagram");
        return $this->media->createPost($accountId, $accessToken, $mediaUrl, $caption, $isImage);
    }
    public function createIgCarouselPost(string|int $accountId, string $accessToken, array $mediaUrls, string $caption): array {
        if($this->mediaType !== "instagram") return array("error" => "This is only supported for Instagram");
        return $this->media->createCarouselPost($accountId, $accessToken, $mediaUrls, $caption);
    }

    /*
     * Instagram insight methods
     */
    public function reelsMediaInsight(string|int $mediaId, string $accessToken): array {
        if($this->mediaType !== "instagram") return array("error" => "This is only supported for Instagram");
        return $this->media->reelsMediaInsight($mediaId, $accessToken);
    }
    public function orderMediaInsights(array $data): array {
        if($this->mediaType !== "instagram") return array("error" => "This is only supported for Instagram");
        return $this->media->orderMediaInsights($data);
    }
    public function mediaMetaData(string|int $mediaId, string $accessToken): array {
        if($this->mediaType !== "instagram") return array("error" => "This is only supported for Instagram");
        return $this->media->mediaMetaData($mediaId, $accessToken);
    }
    public function mediaDiscovery(string|int $accountId, string $accessToken, int $mediaCapId = 0): array {
        (new AbstractCrudObject())->multiArrayLog([$accountId, $accessToken, $this->mediaType], "stuff-2");
        if($this->mediaType !== "instagram") return array("error" => "This is only supported for Instagram");
        return $this->media->mediaDiscovery($accountId, $accessToken, $mediaCapId);
    }
    public function taggedPage(string|int $accountId, string $accessToken, int $timeCap = 0): array {
        if($this->mediaType !== "instagram") return array("error" => "This is only supported for Instagram");
        return $this->media->taggedPage($accountId, $accessToken, $timeCap);
    }
    public function accountInsight(string|int $accountId, string $accessToken): array {
        if($this->mediaType !== "instagram") return array("error" => "This is only supported for Instagram");
        return $this->media->accountInsight($accountId, $accessToken);
    }
    public function accountReachInsight(string|int $accountId, string $accessToken): array {
        if($this->mediaType !== "instagram") return array("error" => "This is only supported for Instagram");
        return $this->media->accountReachInsight($accountId, $accessToken);
    }
    public function accountDemographicInsight(string|int $accountId, string $accessToken): array {
        if($this->mediaType !== "instagram") return array("error" => "This is only supported for Instagram");
        return $this->media->accountDemographicInsight($accountId, $accessToken);
    }

    public function queryMention(array $args, string $type): array {
        if($this->mediaType !== "instagram") return array("error" => "This is only supported for Instagram");
        return $this->media->queryMention($args, $type);
    }
    public function queryMessage(string $pageToken, string|int $igId): array {
        if($this->mediaType !== "instagram") return array("error" => "This is only supported for Instagram");
        return $this->media->queryMessage($pageToken, $igId);
    }
    public function sendMessage(string $pageToken, string|int $igId, string $textMessage): array {
        if($this->mediaType !== "instagram") return array("error" => "This is only supported for Instagram");
        return $this->media->sendMessage($pageToken, $igId, $textMessage);
    }


}