<?php declare(strict_types=1);

namespace Bref\Runner;

use Swoole\ExitException;
use function Swoole\Coroutine\run;

/**
 * This class is used to debug the Swoole runner. It should not be considered as a part of the draft patch.
 */
class SwooleDebugger implements RunnerInterface
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
                    echo $e->getMessage(), "(exception: ", get_class($e), "; error code: {$e->getStatus()}; flag: {$e->getFlags()})";
                }
                echo "Reaching the end of the Swoole runner. ",
                     "Note that there could be coroutines keep running. ",
                     "Swoole runner quits only when all coroutines finish execution.\n";
            }
        });

        echo "Finish executing the Bref bootstrap script (the PHP one, not the shell one).\n";
        exit(0);
        echo "This line will never be executed.\n";
    }
}
