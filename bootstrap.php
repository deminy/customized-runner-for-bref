<?php declare(strict_types=1);

use Bref\Runner\RunnerInterface;

if (getenv('BREF_AUTOLOAD_PATH')) {
    require getenv('BREF_AUTOLOAD_PATH');
} else {
    $appRoot = getenv('LAMBDA_TASK_ROOT');

    require $appRoot . '/vendor/autoload.php';
}

$runtimeClass = getenv('RUNTIME_CLASS');

if (! class_exists($runtimeClass)) {
    throw new RuntimeException("Bref is not installed in your application (could not find the class \"$runtimeClass\" in Composer dependencies). Did you run \"composer require bref/bref\"?");
}

// Environment variable BREF_PHP_RUNNER is to specify a customized PHP runner to handle Lambda invocations.
$runner = getenv('BREF_PHP_RUNNER');
if (empty($runner)) {
    $runtimeClass::run();
} else {
    if (!isset(class_implements($runner)[RunnerInterface::class])) {
        throw new RuntimeException("PHP runner \"{$runner}\" should implement the interface " . RunnerInterface::class);
    }
    // The following statement can be simplified in one line: $runner::run([$runtimeClass, 'run']);
    $runner::run(function () use ($runtimeClass) {
        $runtimeClass::run();
    });
}
