<?php declare(strict_types=1);

namespace Bref\FunctionRuntime;

use Bref\Bref;
use Bref\LazySecretsLoader;
use Bref\Runner\RunnerInterface;
use Bref\Runtime\LambdaRuntime;
use Throwable;

/**
 * @internal
 */
class Main
{
    public static function run(): void
    {
        LazySecretsLoader::loadSecretEnvironmentVariables();

        Bref::triggerHooks('beforeStartup');

        $lambdaRuntime = LambdaRuntime::fromEnvironmentVariable('function');

        $container = Bref::getContainer();

        try {
            $handler = $container->get(getenv('_HANDLER'));
        } catch (Throwable $e) {
            $lambdaRuntime->failInitialization($e->getMessage());
        }

        // Environment variable BREF_PHP_RUNNER specifies a customized PHP runner to handle Lambda invocations.
        $runner = getenv('BREF_PHP_RUNNER');
        if (empty($runner)) {
            $runner = new class implements RunnerInterface {
                public static function run(callable $fn): void {
                    $fn();
                }
            };
        } elseif (!isset(class_implements($runner)[RunnerInterface::class])) {
            $lambdaRuntime->failInitialization("PHP runner \"{$runner}\" should implement the interface " . RunnerInterface::class);
        }

        $loopMax = getenv('BREF_LOOP_MAX') ?: 1;
        $loops = 0;
        $runner::run(function () use ($loops, $loopMax, $lambdaRuntime, $handler) {
            while (true) {
                if (++$loops > $loopMax) {
                    exit(0);
                }
                $success = $lambdaRuntime->processNextEvent($handler);
                // In case the execution failed, we force starting a new process regardless of BREF_LOOP_MAX
                // Why: an exception could have left the application in a non-clean state, this is preventive
                if (! $success) {
                    exit(0);
                }
            }
        });
    }
}
