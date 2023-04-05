<?php declare(strict_types=1);

namespace Bref\Runner;

use Swoole\ExitException;
use function Swoole\Coroutine\run;

class Swoole implements RunnerInterface
{
    /**
     * @inheritDoc
     */
    public static function run(callable $fn): void
    {
        run(function () use ($fn) {
            try {
                $fn();
            } catch (ExitException $e) {
                // In the Runtime Classes (e.g., class Bref\FunctionRuntime\Main), it calles exit(0) to terminate the
                // PHP execution environment when an event is not handled successfully, or it has reached maximum #
                // of invocations allowed.
                //
                // Swoole doesn't allow exit()or die() to be called inside coroutines. When exit() or die() is called,
                // a \Swoole\ExitException exception will be thrown.
                if ($e->getStatus() !== 0) {
                    // If the exit code is not 0, there is some critical issue in the application where some errors not
                    // handled properly. It would be better to have those errors properly addressed in the application.
                    //
                    // Here we only print out the error message to help developers to debug the issue.
                    echo $e->getMessage(), "(exception: ", get_class($e), "; error code: {$e->getStatus()})";
                }
            }
        });

        exit(0);
    }
}
