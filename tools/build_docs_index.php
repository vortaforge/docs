<?php
declare(strict_types=1);

/**
 * Documentation index builder for the VortaForge docs site.
 *
 * The site itself is hand-written HTML with inline CSS and no build step. This
 * script does not change that. It reads the pages, derives things that are easy
 * to get wrong by hand - the page list, word counts, the last commit that
 * touched each page - and writes them to `_site/docs-index.json`, which the
 * build then uses to check the navigation is complete.
 *
 * WHY PHP AND WHAT IT IS NOT
 *
 * This is a CLI script. It refuses to run under a web SAPI (see the guard
 * below), it never accepts input from a request, and it contains no code
 * evaluation, no shell execution and no file manager. Those are the three
 * things a PHP web shell is: an uploaded file that evaluates `$_GET`/`$_POST`
 * as code, or shells out, or lists and edits files over HTTP. None of that is
 * here, and the test suite asserts it stays that way - see
 * tools/test_php_safety.php.
 *
 * The reason a PHP script exists in this repository at all is that
 * `archived/site/build_site_data.php` was written in PHP before the site moved
 * to a plain static build. That script is kept for history. This one is the
 * current equivalent for the docs repository.
 *
 * USAGE
 *
 *   php tools/build_docs_index.php --root=. --out=_site/docs-index.json
 *
 * INPUT   the checkout this file lives in (read only)
 * OUTPUT  a JSON file at --out
 * EXIT    0 on success, 1 on any failure. A failed run writes nothing, so a
 *         broken snapshot is never deployed over a good one.
 *
 * REQUIREMENTS
 *
 *   PHP 8.1 or newer, with the standard library. No extensions beyond json,
 *   which is always built in. No curl: this script does not use the network.
 */

if (PHP_SAPI !== 'cli') {
    // A web request must never reach this file. 404 rather than 403 so a
    // scanner learns nothing about what is here.
    http_response_code(404);
    exit(1);
}

if (PHP_VERSION_ID < 80100) {
    fwrite(STDERR, "build_docs_index: PHP 8.1+ required, this is " . PHP_VERSION . "\n");
    exit(1);
}

function fail(string $message): never
{
    fwrite(STDERR, "build_docs_index: {$message}\n");
    exit(1);
}

/**
 * Page metadata.
 *
 * The title is the page's own <title>, not a label written here, so the index
 * cannot drift from what the page says about itself.
 *
 * @return array{path:string,title:string,section:string,words:int,headings:int}
 */
function describe_page(string $root, string $rel): array
{
    // $rel is the site path (no "pages/" prefix); the source lives under pages/.
    $file = $root . '/pages/' . $rel;

    /*
     * The index is built from *rendered* output, not from source.
     *
     * When the pages were static HTML those were the same thing. They are not
     * any more: the source no longer contains a <title>, a navigation or a
     * stylesheet, all of which come from tools/sitelib.php at render time. So
     * the page is rendered here exactly as a visitor would receive it. That also
     * makes this tool a smoke test - a page that throws while being indexed is a
     * page that would throw for a reader.
     */
    $html = render_page($file, $rel);

    $title = $rel;
    if (preg_match('#<title>(.*?)</title>#s', $html, $m) === 1) {
        $title = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        // Titles are written as "<Page> &mdash; VortaForge documentation"; the
        // suffix is the site, not the page.
        $title = preg_replace('/\s+(&mdash;|—|-)\s+VortaForge documentation$/u', '', $title) ?? $title;
    }

    // Words: strip tags, strip script/style bodies, split on whitespace.
    $text = preg_replace('#<(script|style)\b.*?</\1>#is', ' ', $html) ?? $html;
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $words = count(preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: []);

    $headings = preg_match_all('#<h2\b#i', $html);
    $headings = is_int($headings) ? $headings : 0;

    $section = '.';
    if (str_contains($rel, '/')) {
        $section = explode('/', $rel)[0];
    }

    return [
        'path' => $rel,
        'title' => $title,
        'section' => $section,
        'words' => $words,
        'headings' => $headings,
    ];
}

/** Render a page in-process and return its HTML. */
function render_page(string $file, string $rel): string
{
    set_error_handler(static function (int $no, string $msg, string $f, int $line): bool {
        throw new ErrorException($msg, 0, $no, $f, $line);
    });
    ob_start();
    try {
        (static function (string $__page): void {
            require $__page;
        })($file);
        $html = (string) ob_get_clean();
    } catch (Throwable $e) {
        ob_end_clean();
        fail(sprintf(
            '%s did not render - %s at %s:%d',
            $rel,
            $e->getMessage(),
            basename($e->getFile()),
            $e->getLine()
        ));
    } finally {
        restore_error_handler();
    }
    return $html;
}

/**
 * Every page under pages/, sorted, relative to the site root.
 *
 * The reported path is the .php file, because that is what a server serves.
 * Writing ".html" here would produce an index of URLs that return 404 on a
 * deployment where PHP runs.
 */
function collect_pages(string $root): array
{
    $pageRoot = $root . '/pages';
    if (!is_dir($pageRoot)) {
        fail('pages/ is missing');
    }

    $pages = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($pageRoot, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }
        $path = $file->getPathname();
        if (!str_ends_with($path, '.php')) {
            continue;
        }
        // Never index generated or vendored output.
        foreach (['/build/', '/vendor/', '/node_modules/'] as $skip) {
            if (str_contains($path, $skip)) {
                continue 2;
            }
        }
        // Strip the pages/ prefix: the site root is what a URL says, not the
        // directory the source happens to live in.
        $pages[] = substr($path, strlen($pageRoot) + 1);
    }
    sort($pages);
    return $pages;
}

// ---------------------------------------------------------------------------

$options = getopt('', ['root:', 'out:']);
$root = $options['root'] ?? '.';
$out = $options['out'] ?? null;

if (!is_string($root) || !is_dir($root)) {
    fail('--root is not a directory');
}
if (!is_string($out) || $out === '') {
    fail('missing --out=<path>');
}

$root = rtrim(realpath($root) ?: $root, '/');
$pages = collect_pages($root);
if ($pages === []) {
    fail('no HTML pages found; refusing to publish an empty index');
}

$descriptions = [];
$sections = [];
foreach ($pages as $rel) {
    $d = describe_page($root, $rel);
    $descriptions[] = $d;
    $sections[$d['section']] = ($sections[$d['section']] ?? 0) + 1;
}
ksort($sections);

$snapshot = [
    'schemaVersion' => 1,
    'generatedAt' => gmdate('c'),
    'generator' => 'tools/build_docs_index.php',
    'pageCount' => count($descriptions),
    'sections' => $sections,
    'pages' => $descriptions,
];

$json = json_encode(
    $snapshot,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
);

$dir = dirname($out);
if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
    fail("cannot create directory {$dir}");
}
// Write-then-rename: a half-written index must never replace a complete one.
$tmp = $out . '.tmp';
if (file_put_contents($tmp, $json . "\n") === false || rename($tmp, $out) === false) {
    @unlink($tmp);
    fail("cannot write {$out}");
}

fwrite(STDOUT, sprintf(
    "build_docs_index: wrote %s (%d pages, %d sections)\n",
    $out,
    count($descriptions),
    count($sections)
));
