<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

// A random string that should be unique (hopefully) across Lambda execution environments, but it remains the same
// across multiple invocations with the same execution environment.
$file = '/tmp/demo-5.rand';
if (!is_readable($file)) {
    file_put_contents($file, uniqid('demo-5-', true));
}
$rand = file_get_contents($file);

// Similar to example 3, but this time we have the coroutines complete more quickly and provide output upon completion.
// Note that the messages are printed out after the response is sent back, and they could be printed out in any order.
return function () use ($rand): array {
    for ($i = 0; $i < 5; $i++) {
        Swoole\Coroutine::create(function () {
            sleep(1);
            printf("Hello world! (coroutine ID: %d)\n", Swoole\Coroutine::getCid());
        });
    }

    return [
        'status' => true,
        'count'  => count(Swoole\Coroutine::listCoroutines()), # To see how many coroutines in the current PHP execution.
        'rand'   => $rand,
    ];
};
