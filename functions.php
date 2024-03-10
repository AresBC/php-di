<?php declare(strict_types=1);


use App\Debug\Debug;
use JetBrains\PhpStorm\NoReturn;

function dump(...$items): void
{
    Debug::dump(...$items);
}

#[NoReturn]
function dd(...$items): void
{
    Debug::dd(...$items);
}

function mark($marker, ...$items): void
{
    Debug::mark($marker, ...$items);
}
