<?php

namespace Oveleon\ProductInstaller\Import;

class Validator
{
    static ?array $validators = null;

    public static function getValidators($trigger = null): ?array
    {
        if(null === $trigger)
        {
            return self::$validators ?? null;
        }

        return self::$validators[$trigger] ?? null;
    }

    public static function addValidator($trigger, $fn): void
    {
        if(self::$validators[$trigger] ?? false)
        {
            self::$validators[$trigger][] = $fn;
            return;
        }

        self::$validators[$trigger] = [$fn];
    }
}
