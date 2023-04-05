<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

// This function takes about 5 seconds to return a response.
return function (): array {
    for ($i = 0; $i < 5; $i++) {
        sleep(1);
    }

    return [
        'status' => true,
    ];
};
