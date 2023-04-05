<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

// By using a customized PHP runner, this function takes about 1 second to return a response.
return function (): array {
    $tasks = [];
    for ($i = 0; $i < 5; $i++) {
        $tasks[] = Swoole\Coroutine::create(function () {
            sleep(1);
        });
    }
    // Wait for all tasks to finish. Please note that there are different ways to wait for tasks to finish in Swoole.
    Swoole\Coroutine::join($tasks);

    return [
        'status' => true,
        'count'  => count(Swoole\Coroutine::listCoroutines()), # To see how many coroutines in the current PHP execution.
    ];
};
