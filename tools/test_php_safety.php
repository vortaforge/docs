<?php
declare(strict_types=1);

/**
 * Safety checks for every PHP file in this repository.
 *
 * The point of this file is narrow and deliberate. PHP has a well-known failure
 * mode where a helper script grows into a web shell: it starts evaluating
 * request input as code, or shelling out, or editing files over HTTP. That is
 * what "php3shell"-class tooling is. There is no requirement for it here and no
 * place for it in a documentation repository.
 *
 * So these tests assert the property directly rather than trusting review:
 * no PHP file may eval, execute a shell, read request superglobals, or act as a
 * file manager. If someone later adds one of those, this fails.
 *
 * Run: php tools/test_php_safety.php
 * Exit: 0 all clear, 1 at least one violation.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

const ROOT = __DIR__ . '/..';

/**
 * Constructs that must never appear in this repository's PHP.
 *
 * Each entry is [regex, why it is banned]. The regex is matched against the
 * source with comments and strings kept in place: a banned call mentioned in a
 * comment is still worth seeing during review, and this list is short enough
 * that false positives are cheap to fix.
 */
const BANNED = [
    // [pattern, modifiers, why]
    ['\beval\s*\(', 'i', 'eval(): turns data into code'],
    ['\bassert\s*\(\s*\$', 'i', 'assert() with a variable is eval() in disguise'],
    ['\bcreate_function\s*\(', 'i', 'create_function(): removed in PHP 8, eval by another name'],
    ['\b(preg_replace|preg_filter)\s*\(\s*[\'"]\S*/[a-z]*e[a-z]*[\'"]', 'i',
     'the /e preg modifier is eval()'],
    ['\b(call_user_func|call_user_func_array|forward_static_call(_array)?)\s*\(\s*\$', 'i',
     'indirect call on a variable: code selected by data'],
    ['\b(system|exec|shell_exec|passthru|proc_open|popen)\s*\(', 'i', 'shell execution'],
    ['\bpcntl_exec\s*\(', 'i', 'process execution'],
    ['\$_(GET|POST|REQUEST|COOKIE|FILES|SERVER|ENV)\b', '', 'request superglobal'],
    ['\bfilter_input\s*\(', 'i', 'reads request input'],
    ['\bphp_uname\s*\(', 'i', 'host fingerprinting, a webshell staple'],
    ['\b(get_current_user|posix_getpwuid|posix_geteuid)\s*\(', 'i', 'user fingerprinting'],
    ['\b(file_put_contents|unlink|rename|mkdir|rmdir)\s*\(\s*\$_(GET|POST|REQUEST)', 'i',
     'file operation driven by request input'],
    ['\bchmod\s*\(\s*\$_(GET|POST|REQUEST)', 'i', 'permission change driven by request input'],
    ['\bbase64_decode\s*\(\s*\$_', 'i', 'decoding request input'],
];


/**
 * Strip comments and docblocks, keeping string literals.
 *
 * A comment explaining *why* `eval` is banned must not be read as a call to it,
 * so the scan runs on tokens. String literals stay: `$m = "eval(";` is worth
 * flagging even though it is not a call.
 */
function without_comments(string $src): string
{
    $out = '';
    foreach (token_get_all($src) as $token) {
        if (is_array($token)) {
            if ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
                continue;
            }
            $out .= $token[1];
            continue;
        }
        $out .= $token;
    }
    return $out;
}

/** Every .php file in the tree, excluding vendor and this test. */
function php_files(): array
{
    $files = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(ROOT, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $f) {
        if (!$f instanceof SplFileInfo || !$f->isFile()) {
            continue;
        }
        if (!str_ends_with($f->getFilename(), '.php')) {
            continue;
        }
        $path = $f->getPathname();
        if (str_contains($path, '/vendor/') || str_contains($path, '/.git/')) {
            continue;
        }
        // This file names every banned construct on purpose; scanning itself
        // would report each one.
        if ($f->getFilename() === 'test_php_safety.php') {
            continue;
        }
        $files[] = $path;
    }
    sort($files);
    return $files;
}

function rel(string $p): string
{
    return ltrim(substr($p, strlen(ROOT)), '/');
}

$failures = 0;
$checked = 0;

$files = php_files();
if ($files === []) {
    fwrite(STDERR, "test_php_safety: no PHP files found; that is itself a problem if PHP "
        . "support is meant to exist\n");
    exit(1);
}

// --- 1. no banned construct ------------------------------------------------

foreach ($files as $file) {
    $raw = file_get_contents($file);
    $src = $raw === false ? false : without_comments($raw);
    if ($src === false) {
        fwrite(STDERR, "cannot read " . rel($file) . "\n");
        $failures++;
        continue;
    }
    $checked++;

    foreach (BANNED as [$pattern, $mods, $why]) {
        if (preg_match('#' . $pattern . '#' . $mods, $src) === 1) {
            fwrite(STDERR, sprintf("FAIL %s: banned construct (%s)\n", rel($file), $why));
            $failures++;
        }
    }
}

// --- 2. every script is CLI-only -------------------------------------------

/*
 * A file that can be fetched and executed by a web server is the shape of a
 * web shell regardless of what it does. Each entry point must refuse a web SAPI.
 */
$entry_points = array_values(array_filter($files, static function (string $p): bool {
    return !str_contains(rel($p), '/tests/')
        && !str_ends_with(rel($p), 'test_php_safety.php');
}));

foreach ($entry_points as $file) {
    $src = (string) file_get_contents($file);
    $hasGuard = str_contains($src, "PHP_SAPI !== 'cli'") || str_contains($src, 'PHP_SAPI !== "cli"');
    if (!$hasGuard) {
        fwrite(STDERR, sprintf(
            "FAIL %s: no CLI guard, so a web server could execute it\n",
            rel($file)
        ));
        $failures++;
    }
}

// --- 3. the docs site must stay static -------------------------------------

/*
 * PHP is build-time only. Nothing under the published site may be a script the
 * server would interpret, and no page may link to one.
 */
foreach (['index.html', 'docs', 'macroengine', 'mods', 'datapacks'] as $target) {
    $path = ROOT . '/' . $target;
    if (is_file($path)) {
        $candidates = [$path];
    } elseif (is_dir($path)) {
        $candidates = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if ($f instanceof SplFileInfo && $f->isFile()) {
                $candidates[] = $f->getPathname();
            }
        }
    } else {
        continue;
    }
    foreach ($candidates as $c) {
        if (preg_match('/\.(php|phtml|phar)$/i', $c) === 1) {
            fwrite(STDERR, "FAIL " . rel($c) . ": PHP inside the published site\n");
            $failures++;
        }
    }
}

// --- 4. report -------------------------------------------------------------

if ($failures > 0) {
    fwrite(STDERR, sprintf("\ntest_php_safety: %d problem(s) across %d file(s)\n", $failures, $checked));
    exit(1);
}

fwrite(STDOUT, sprintf(
    "test_php_safety: OK - %d PHP file(s), no eval/exec/request input, "
    . "all entry points CLI-only, no PHP in the published site\n",
    $checked
));
exit(0);
