<?php

namespace App\Services;

class OrderNumberGenerator
{
    public static function make(): string
    {
        $year = now()->format('Y');
        $seq = str_pad((string) (int) (microtime(true) * 1000) % 100000, 6, '0', STR_PAD_LEFT);
        return "CMD-$year-$seq";
    }
}
