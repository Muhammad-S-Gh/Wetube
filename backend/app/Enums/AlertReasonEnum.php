<?php

namespace App\Enums;

enum AlertReasonEnum: string
{
    case SPAM = 'spam';
    case ABUSE = 'abuse';
    case COPYRIGHT = 'copyright';
    case INAPPROPRIATE = 'inappropriate';
    case OTHER = 'other';

    /**
     * Return enum values as array of strings (useful for migrations)
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(fn(self $r) => $r->value, self::cases());
    }
}
