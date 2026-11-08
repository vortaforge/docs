<?php
declare(strict_types=1);

/**
 * Guard for every CLI-only entry point in tools/.
 *
 * `require_once __DIR__ . '/cli.php';` is the first executable line of each
 * tool. Requiring it a second time does nothing, so a tool can require it
 * directly and also pick it up through sitelib.php without starting twice.
 *
 * Two checks, in this order:
 *
 *   1. The SAPI must be `cli`. Anything else - a web server, php-fpm, the
 *      built-in server - gets a 404 and exits 1 before a single line of tool
 *      code runs. A build script that writes files must not be reachable over
 *      HTTP, and "it is not in the document root" is a deployment assumption,
 *      not a guarantee.
 *
 *   2. PHP must be 8.1 or newer. `never` as a return type, which these tools
 *      use, is a parse error on 8.0 and below - a syntax error is a worse
 *      failure than a message naming the version.
 *
 * This file is deliberately a guard and nothing else. tools/sitelib.php is the
 * library the *pages* use, and it is reachable over HTTP by design.
 */

if (PHP_SAPI !== 'cli') {
    if (!headers_sent()) {
        http_response_code(404);
    }
    exit(1);
}

if (PHP_VERSION_ID < 80100) {
    fwrite(STDERR, sprintf(
        "PHP 8.1 or newer is required, running %s\n",
        PHP_VERSION
    ));
    exit(1);
}
