<?php

namespace App\Logger;

class Logger implements ILogger
{
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    private static array $handlers = [];


    function addHandler(string $level, callable $handler): void
    {
        self::$handlers[$level][] = $handler;
    }

    function getHandlersByLevel(string $level): array
    {
        if (!isset(self::$handlers[$level])) {
            return [];
        }
        return self::$handlers[$level];
    }

    function log(string $level, ?string $message, array $context = []): void
    {
        $handlers = $this->getHandlersByLevel($level);

        $backtrace = array_map(fn($x) => $x['file'] . ': ' . $x['line'], debug_backtrace());

        $record = [
            'message'  => $message,
            'caller'  => $backtrace[count($backtrace) - 1],
            'backtrace'  => $backtrace,
            'level'    => $level,
            'datetime' => date('Y-m-d H:i:s:') . explode('.', microtime(true))[1],
            'ram usage' => memory_get_usage(true),
        ];
        if (count($context) !== 0) $record['context'] =  $context;

        foreach ($handlers as $handler) {
            $handler($record);
        }
    }

    function alert(?string $message, array $context = []): void
    {
        $this->log(self::ALERT, $message, $context);
    }

    function debug(?string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    function info(?string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    function notice(?string $message, array $context = []): void
    {
        $this->log(self::NOTICE, $message, $context);
    }

    function warning(?string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    function error(?string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    function critical(?string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    function emergency(?string $message, array $context = []): void
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

}