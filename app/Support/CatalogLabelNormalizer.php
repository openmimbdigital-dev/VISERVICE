<?php

namespace App\Support;

use Illuminate\Support\Str;

class CatalogLabelNormalizer
{
    public static function fromName(string $name): string
    {
        $ascii = Str::ascii($name);

        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ascii) ?? '');
    }
}
