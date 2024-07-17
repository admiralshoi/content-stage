<?php

namespace classes\src\Enum;

use JetBrains\PhpStorm\Pure;
use classes\src\Enum\PushTypes as PUSHTYPE;

class NotificationTypes {
    private static string $currentType;
    private const existingTypes = array(

    );

    public static function setType(string $type): void {self::$currentType = $type;}
    public static function typeIsValid(): bool {return in_array(self::$currentType, self::existingTypes);}

    private static function minimumDelayPlatform(): int {
        return match (self::$currentType) {
            default => 0,
            "order_new_message" => (3600 / 60 * 10), // 1 minute
            "order_late" => (3600 * 24), // 1 minute
        };
    }
    private static function minimumDelayEmail(): int {
        return match (self::$currentType) {
            default => 0,
            "order_new_message" => (3600 * 2), // 2 hours
            "order_late" => (3600 * 24 * 4), // 2 hours
        };
    }

    #[Pure] public static function delayIsOk(int $pushType, int $lastSent = 0, int $nextSent = 0): bool {
        return match($pushType) {
            default => false,
            PUSHTYPE::PLATFORM => ((self::minimumDelayPlatform() + $lastSent) < $nextSent),
            PUSHTYPE::EMAIL => ((self::minimumDelayEmail() + $lastSent) < $nextSent),
            PUSHTYPE::BOTH => (((self::minimumDelayEmail() + $lastSent) < $nextSent) || ((self::minimumDelayPlatform() + $lastSent) < $nextSent)),
        };
    }

}