<?php

namespace App\Enums;

enum UploadStateEnum: string // defining :string :int adds value that array_map can use
{
    case PROCESSING = "processing";
    case COMPLETED = "completed";
    case FAILED = "failed";

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function values()
    {
        return array_map(fn(self $state): string => $state->value, self::cases());
    }
}
