<?php

namespace classes\src\Enum;

class Error {
    const MISSING_DATA_FIELD = ["status" => "error", "error" => ["message" => "Missing data-field", "code" => 8931]];
    const UNKNOWN_INTEGRATION = ["status" => "error", "error" => ["message" => "Failed to fetch the integration", "code" => 8937]];
}