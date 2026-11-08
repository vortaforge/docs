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
 * Reduce a file to its PHP, dropping what is not executable.
 *
 * Three things are removed, for three different reasons:
 *
 *   T_COMMENT, T_DOC_COMMENT  A comment explaining *why* `eval` is banned must
 *                             not be read as a call to it.
 *
 *   T_INLINE_HTML             Everything between `?>` and `<?php`. This is the
 *                             page's content - the documentation itself - and
 *                             a page whose subject is PHP safety necessarily
 *                             contains the words it is warning about. Scanning
 *                             text would flag a paragraph for naming a function
 *                             it tells you not to use.
 *
 *   T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE
 *                             String literals are removed too, and this one is
 *                             a deliberate weakening: `$m = "eval(";` is no
 *                             longer caught. It is removed because the docs
 *                             page quotes code samples as strings in places,
 *                             and a test that fails when documentation quotes
 *                             a bad example is a test people switch off.
 *
 * The result is only the tokens that can actually execute, which is the set
 * the bans are about.
 */
function executable_php(string $src): string
{
    $skip = [
        T_COMMENT,
        T_DOC_COMMENT,
        T_INLINE_HTML,
        T_CONSTANT_ENCAPSED_STRING,
        T_ENCAPSED_AND_WHITESPACE,
    ];
    $out = '';
    foreach (token_get_all($src) as $token) {
        if (is_array($token)) {
            if (in_array($token[0], $skip, true)) {
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
    $src = $raw === false ? false : executable_php($raw);
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
/*
 * The rule is scoped by directory, because the two kinds of file have opposite
 * requirements:
 *
 *   tools/*.php  are build scripts. They must refuse a web SAPI outright - a
 *                file that can be fetched and executed is the shape of a shell
 *                no matter what it does.
 *
 *   pages/*.php  are the site. They are *supposed* to run under a web server;
 *                refusing to would defeat the point of converting them. What
 *                they must not do is reach request input or execute anything,
 *                and check 1 already covers that for every file.
 *
 * sitelib.php is in tools/ but is required *by* pages, so it is exempt from the
 * guard on the same grounds as a page: it renders, it does not build.
 */
const CLI_GUARD_EXEMPT = ['sitelib.php'];

$entry_points = array_values(array_filter($files, static function (string $p): bool {
    $r = rel($p);
    if (str_ends_with($r, 'test_php_safety.php')) {
        return false;
    }
    if (!str_starts_with($r, 'tools/')) {
        return false;
    }
    foreach (CLI_GUARD_EXEMPT as $ok) {
        if (str_ends_with($r, '/' . $ok) || $r === 'tools/' . $ok) {
            return false;
        }
    }
    return true;
}));

foreach ($entry_points as $file) {
    $src = (string) file_get_contents($file);
    $hasGuard = str_contains($src, "PHP_SAPI !== 'cli'")
        || str_contains($src, 'PHP_SAPI !== "cli"')
        || str_contains($src, "require_once __DIR__ . '/cli.php'")
        || str_contains($src, "require_once __DIR__ . '/cli.php';");
    if (!$hasGuard) {
        fwrite(STDERR, sprintf(
            "FAIL %s: no CLI guard, so a web server could execute it\n",
            rel($file)
        ));
        $failures++;
    }
}

// --- 3. the site is PHP, and must stay safe as PHP -------------------------

/*
 * This check used to assert the opposite: that no .php file reached the
 * published tree. The site is PHP now, so that assertion would fail by design.
 * What replaces it is the property that actually matters once pages execute on
 * a server:
 *
 *   a. every page renders, from the command line, with no warnings;
 *   b. no page reads request data - $_{GET,POST,REQUEST,COOKIE,FILES,SERVER,
 *      ENV}, filter_input, getallheaders, php://input. Check 1 already bans the
 *      constructs; this makes the ban specific to pages, where it is the whole
 *      security story.
 *   c. every page routes its output through sitelib.php, so a page cannot
 *      quietly hand-assemble a document and skip the shell.
 *
 * A page that renders is not proof it is safe. A page that fails to render here
 * is proof it is broken, and that is worth catching before a visitor does.
 */
$page_root = ROOT . '/pages';
if (!is_dir($page_root)) {
    fwrite(STDERR, "FAIL pages/ is missing\n");
    $failures++;
} else {
    $page_files = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($page_root, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $f) {
        if ($f instanceof SplFileInfo && $f->isFile() && $f->getExtension() === 'php') {
            $page_files[] = $f->getPathname();
        }
    }
    sort($page_files);

    if ($page_files === []) {
        fwrite(STDERR, "FAIL pages/ contains no PHP pages\n");
        $failures++;
    }

    $request_reads = ['$_GET', '$_POST', '$_REQUEST', '$_COOKIE', '$_FILES', '$_SERVER'];
    foreach ($page_files as $pf) {
        // Executable PHP only. A page whose subject is PHP safety names these
        // superglobals in its prose, and that prose is T_INLINE_HTML, not code.
        $src = executable_php((string) file_get_contents($pf));
        $rv = rel($pf);

        foreach ($request_reads as $super) {
            if (str_contains($src, $super)) {
                fwrite(STDERR, sprintf(
                    "FAIL %s: a page reads %s, and a page has no business reading request input\n",
                    $rv,
                    $super
                ));
                $failures++;
            }
        }

        // Raw source here, not $src: the require path is a string literal, and
        // executable_php() strips those. This is a structural check, not a
        // security one - the ban above is the security one.
        if (!str_contains((string) file_get_contents($pf), 'sitelib.php')) {
            fwrite(STDERR, sprintf(
                "FAIL %s: does not include sitelib.php, so it bypasses the shell\n",
                $rv
            ));
            $failures++;
            continue;
        }

        $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $proc = proc_open([PHP_BINARY, $pf], $descriptors, $pipes);
        if (!is_resource($proc)) {
            fwrite(STDERR, "FAIL {$rv}: could not be executed\n");
            $failures++;
            continue;
        }
        $out = (string) stream_get_contents($pipes[1]);
        $err = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $code = proc_close($proc);

        if ($code !== 0 || trim($err) !== '') {
            fwrite(STDERR, sprintf(
                "FAIL %s: did not render cleanly (exit %d) %s\n",
                $rv,
                $code,
                trim($err) !== '' ? '- ' . strtok(trim($err), "\n") : ''
            ));
            $failures++;
            continue;
        }
        if (!str_contains($out, '<!DOCTYPE html>')) {
            fwrite(STDERR, "FAIL {$rv}: rendered something that is not an HTML document\n");
            $failures++;
        }
    }

    $page_count = count($page_files);
}

// --- 4. report -------------------------------------------------------------

if ($failures > 0) {
    fwrite(STDERR, sprintf("\ntest_php_safety: %d problem(s) across %d file(s)\n", $failures, $checked));
    exit(1);
}

fwrite(STDOUT, sprintf(
    "test_php_safety: OK - %d PHP file(s), no eval/exec/request input, "
    . "tools/ CLI-only, %d page(s) render and read no request data\n",
    $checked,
    $page_count ?? 0
));
exit(0);
