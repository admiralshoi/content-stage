<?php

namespace classes\src\Enum;

use JetBrains\PhpStorm\Pure;

class EmailTypes {




    #[Pure] public static function getTemplate(string|int $type, string $roleName): ?string {return self::getByRoleName($type, $roleName); }

    private static function getByRoleName(string|int $type, string $roleName): ?string {
        return match ($roleName) {
            default => null,
        };
    }
}