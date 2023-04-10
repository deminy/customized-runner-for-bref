This repository includes a patch allowing to use customized PHP runners in Bref v2. It also includes a customized PHP
runner based on Swoole, along with a few examples.

It's a starting point to use customized PHP runners in Bref v2, and to support asynchronous IO operations in Bref using
Swoole (and other PHP extensions/frameworks).

## The Patch

There are three classes added or updated in this patch:

* A patched class [Bref\FunctionRuntime\Main][1] to support customized PHP runners. It's better to patch it in script `bootstrap.php` instead of `Bref\FunctionRuntime\*` classes. A patched script [bootstrap.php][2] is included in this repository, although it's not used in the examples.
* New interface [Bref\Runner\RunnerInterface][3] to support customized PHP runners.
* New class [Bref\Runner\Swoole][4] is a customized PHP runner based on Swoole.

To use Swoole in Bref v2, we don't have to use the customized PHP runner in this repository. In some cases, Swoole can be
used directly in Bref v2. e.g., if environment variable `BREF_LOOP_MAX` is not set or set to 1. In this case, we can
manually bootstrap Swoole in the Lambda function, and use Swoole to handle asynchronous IO operations.

Swoole doesn't work in PHP-FPM mode. The patch included is only for event-driven functions.

## List of Improvements

* Allow to use customized PHP runners, e.g., [Bref\Runner\Swoole][4].
* Support asynchronous/concurrent IO operations in Bref (using customized PHP runners).
* Save costs in AWS. The 3rd example (function `demo-3-use-customized-runner`) shows how we could have billing duration decreased in AWS Lambda.

## List of Examples

Here we have four Lambda functions created. Each performs five IO operations, and each IO operation takes some time to finish.

An IO operation could be a REST API call, a database query, a file read/write, etc. In the examples, we use the PHP
function `sleep()` to simulate IO operations.

Before deploying any of the following functions, please run Composer command `composer update --no-dev` first to install PHP packages.

**`1`. Lambda function `demo-1-no-swoole`**

This one shows how to handle blocking IOs is handled in PHP.

**`2`. Lambda function `demo-2-with-swoole`**

This one shows how to handle blocking IOs is handled in PHP. It has Swoole enabled, but still takes same time as the
previous one.

The reason is that Swoole is not properly bootstrapped in the Lambda function. To support asynchronous IO operations in
Bref using Swoole, and to allow Swoole to handle multiple Lambda invocations using the same execution environment, we
need to use a customized PHP runner in Bref v2, as you can see in the next example.

**`3`. Lambda function `demo-3-use-customized-runner`**

This example shows how to handle IOs concurrently using Swoole, with customized PHP runner in use. It takes about 0
second to finish without waiting asynchronous IO operations to complete.

By using a customized PHP runner, we can have the Lambda function return a response immediately and let Swoole handle
the rest. Swoole can keep working in the same execution environment after the response is returned.

The function used in the example takes 60 seconds to finish (or 300 seconds if Swoole is not in use). If we invoke the
Lambda function multiple times consecutively, you will notice that all the invocation requests can be handled by the same
execution environment. Furthermore, the billing duration is not 300 seconds, nor 60 seconds, but something less than 1 second.

Please be aware that this example primarily serves as a demonstration and should not be considered a production-ready
solution. Before implementing this approach in AWS Lambda, it is essential to evaluate the various limitations and adapt
the code accordingly. There are some interesting discussions about this topic under
[this GitHub issue](https://github.com/brefphp/bref/issues/1499). Please kindly check it out before using this approach
in your project.

**`4`. Lambda function `demo-4-use-customized-runner`**

This example shows how to wait concurrent IO operations to complete before sending a response back.

**`5`. Lambda function `demo-5-use-customized-runner`**

Similar to example 3, but with the following differences:

* The coroutines complete more quickly.
* Some output is included upon completion.
* A customized PHP runner ([SwooleDebugger][5]) is used to print out debugging messages.

Note that the messages are printed out after the response is sent back, and they could be printed out in any order.
Also, the [SwooleDebugger][5] class is used only to debug the Swoole runner; it should not be considered as a part of
the draft patch.

**`6`. Lambda function `demo-6-use-customized-runner`**

Exactly the same as example 5, except that it runs in different execution environments.

[1]: https://github.com/deminy/customized-runner-for-bref/blob/master/src/FunctionRuntime/Main.php
[2]: https://github.com/deminy/customized-runner-for-bref/blob/master/bootstrap.php
[3]: https://github.com/deminy/customized-runner-for-bref/blob/master/src/Runner/RunnerInterface.php
[4]: https://github.com/deminy/customized-runner-for-bref/blob/master/src/Runner/Swoole.php
[5]: https://github.com/deminy/customized-runner-for-bref/blob/master/src/Runner/SwooleDebugger.php
