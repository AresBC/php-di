<?php

namespace App\Logger;

interface ILogger
{
    function alert(?string $message, array $context = []): void;

    function debug(?string $message, array $context = []): void;

    function info(?string $message, array $context = []): void;

    function notice(?string $message, array $context = []): void;

    function warning(?string $message, array $context = []): void;

    function error(?string $message, array $context = []): void;

    function critical(?string $message, array $context = []): void;

    function emergency(?string $message, array $context = []): void;

}