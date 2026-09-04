<?php

namespace App\Helpers;

class LogistikStatus
{
    const TERPAKAI = 13;
    const TERSEIDA = 14;
    const RUSAK = 15;
    const MAINTENANCE = 4;

    const SERIALIZED_CATEGORIES = ['modem', 'tenda', 'sfp', 'olt', 'odp', 'odc', 'htb', 'splitter'];

    public static function isSerialized(?string $kategori): bool
    {
        return in_array(strtolower($kategori ?? ''), self::SERIALIZED_CATEGORIES);
    }

    public static function labelClass(int $stok): string
    {
        return $stok > 10 ? 'success' : ($stok > 5 ? 'warning' : 'danger');
    }
}
