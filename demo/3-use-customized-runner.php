<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

// By using a customized PHP runner, we can have the Lambda function return a response immediately and let Swoole handle
// the rest. The 5 sleep function calls takes 60 seconds to finish after the response is returned.
//
// If we invoke the Lambda function multiple times consecutively, you will notice that all the invocation requests are
// handled by the same execution environment. Furthermore, the billing duration is not 300 seconds, nor 60 seconds, but
// something less than 1 second.
return function (): array {
    for ($i = 0; $i < 5; $i++) {
        Swoole\Coroutine::create(function () {
            sleep(60);
        });
    }

    return [
        'status' => true,
        'count'  => count(Swoole\Coroutine::listCoroutines()), # To see how many coroutines in the current PHP execution.
    ];
};
