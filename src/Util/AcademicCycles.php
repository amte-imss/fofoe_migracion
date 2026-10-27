<?php

namespace App\Util;

final class AcademicCycles
{
    /**
     * @return array<string, string> Cycle label => cycle value, newest first
     */
    public static function recent(int $count = 3): array
    {
        $cycles = [];
        $endYear = (int) date('Y') + 1;

        for ($i = 0; $i < $count; $i++, $endYear--) {
            $cycle = sprintf('%d - %d', $endYear - 1, $endYear);
            $cycles[$cycle] = $cycle;
        }

        return $cycles;
    }
}
