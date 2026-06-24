<?php

namespace App\Util;

class CVSUtil
{
    public static function arrayToCsvLine(array $values, string $delimiter = ','): string
    {
        $values = array_map(
            fn($v) => '"' . str_replace('"', '""', strval($v)) . '"',
            $values
        );

        return implode($delimiter, $values);
    }
}
