<?php

namespace App\Debug;

use JetBrains\PhpStorm\NoReturn;

class Debug
{
    public static string $marker = '#######################################';
    public static function dump(...$items): void
    {
        echo Debug::$marker . PHP_EOL;
        foreach ($items as $item) {
            print_r($item);
            echo PHP_EOL;
        }
    }

    #[NoReturn]
    public static function dd(...$items): void
    {
        Debug::dump(...$items);
        die;
    }

    public static function mark(mixed $marker, ...$items): void
    {
        $marker = is_object($marker) ? get_class($marker) : $marker;
        echo Debug::$marker . PHP_EOL;
        echo '### ' . $marker  . PHP_EOL;
        Debug::dump(...$items);
    }
}