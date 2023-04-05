<?php declare(strict_types=1);

namespace Bref\Runner;

interface RunnerInterface
{
    public static function run(callable $fn): void;
}
