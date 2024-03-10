<?php

namespace App\Debug;

use App\Logger\ILogger;
use Closure;
use JetBrains\PhpStorm\NoReturn;

class Debug
{
    public static ?ILogger $logger = null;

    private static function getLogger(): ILogger
    {
        if (self::$logger === null) {
            var_dump('No loggers have been set!');
            die;
        }

        return self::$logger;
    }


    public static function dump(...$items): void
    {
        self::print(null, ...$items);
    }


    private static function print(?string $marker = null, ...$items): void
    {
        self::getLogger()->debug($marker, $items);
    }


    #[NoReturn]
    public static function dd(...$items): void
    {
        self::print(null, ...$items);
    }


    public static function mark(mixed $marker, ...$items): void
    {
        $marker = is_object($marker) ? get_class($marker) : $marker;

        self::print($marker, ...$items);
    }

    public static function getDefaultLogHandler(): Closure
    {
        return function ($record) {
            ob_start();

            echo PHP_EOL;
            echo PHP_EOL;
            echo "[{$record['datetime']}] {$record['caller']}" . PHP_EOL;

            if ($record['message'] !== null) {
                echo "\t" . 'message: ' . $record['message'] . PHP_EOL;
            }

            echo "\t" . 'level: ' . $record['level'] . PHP_EOL;
            echo "\t" . 'ram usage: ' . $record['ram usage'] . PHP_EOL;

            echo "\t" . 'backtrace:' . PHP_EOL;
            foreach ($record['backtrace'] as $item) {
                echo "\t\t" . $item . PHP_EOL;
            }

            if (count($record['context']) > 0) {
                echo "\t" . 'context: ';
                foreach ($record['context'] as $item) {
                    echo PHP_EOL;
                    print_r($item);
                }
            }

            file_put_contents('test.txt', ob_get_clean(), FILE_APPEND);
        };
    }
}