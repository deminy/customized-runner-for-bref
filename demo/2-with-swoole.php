<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

// This function takes about 5 seconds to return a response in Bref v2, even we have Swoole enabled in the environment.
//
// By using a customized PHP runner, it's possible to make it return a response immediately and let Swoole handle the
// rest. The 5 sleep function calls takes 1 second to finish after the response is returned. Please check the next
// Lambda function (function "demo-3-use-customized-runner") for details.
return function (): array {
    for ($i = 0; $i < 5; $i++) {
        Swoole\Coroutine::create(function () {
            sleep(1);
        });
    }

    return [
        'status' => true,
        'count'  => count(Swoole\Coroutine::listCoroutines()), # To see how many coroutines in the current PHP execution.
    ];
};
