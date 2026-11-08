<?php
declare(strict_types=1);

/**
 * Render every page, and check that the stylesheet reached it.
 *
 * Two jobs, because they are the same job from different angles.
 *
 * The pages are PHP, so a page that throws only fails when someone opens it -
 * which is the worst time to find out. This renders all fifteen, in order, and
 * reports any that fail. Rendering happens in-process with output buffering
 * rather than by spawning `php page.php`, so there is no shell execution in the
 * tooling at all (tools/test_php_safety.php bans proc_open/exec/system for
 * exactly that reason, and a check that special-cases itself is not a check).
 *
 * The second job is the one the old tools/sync_style.py did. It compared
 * fifteen inlined <style> blocks against assets/style.css. There are no inlined
 * blocks any more - sitelib.php emits the stylesheet once per page - so the
 * thing to check is the rendered output: the stylesheet appears exactly once,
 * byte for byte. Zero means a page bypassed the shell; two means a page grew a
 * second copy, which is how seven near-duplicate stylesheets happened before.
 *
 * Usage:
 *   php tools/render_pages.php              # check, print a summary
 *   php tools/render_pages.php --verbose    # print a line per page
 *   php tools/render_pages.php --out=build  # also write the static snapshot
 *
 * The --out directory is the escape hatch for hosts that cannot run PHP: the
 * rendered output there is a complete static site, because the pages are
 * stateless and their navigation is relative. It is not the primary artifact -
 * the .php files are - but it means this repository is publishable anywhere.
 *
 * Exit 0 when every page renders and carries the stylesheet, 1 otherwise.
 */

require_once __DIR__ . '/cli.php';

const VF_SKIP_DIRS = ['build', 'vendor', 'node_modules'];

/** @return list<string> page paths relative to pages/, e.g. "docs/api.php" */
function vf_page_list(): array
{
    $root = dirname(__DIR__) . '/pages';
    if (!is_dir($root)) {
        fwrite(STDERR, "render_pages: pages/ is missing\n");
        exit(1);
    }

    $out = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $f) {
        if (!$f instanceof SplFileInfo || !$f->isFile() || $f->getExtension() !== 'php') {
            continue;
        }
        $rel = substr($f->getPathname(), strlen($root) + 1);
        foreach (VF_SKIP_DIRS as $skip) {
            if (str_starts_with($rel, $skip . '/')) {
                continue 2;
            }
        }
        $out[] = $rel;
    }
    sort($out);
    return $out;
}

/**
 * Render one page and return [html, warning].
 *
 * A warning is a PHP notice or deprecation raised while the page ran. They are
 * converted to exceptions so a page cannot "succeed" while printing a notice at
 * the top of the output - which is how a broken build looks like a working one.
 */
function vf_render_one(string $rel): array
{
    $path = dirname(__DIR__) . '/pages/' . $rel;
    $html = '';
    $warning = '';

    set_error_handler(static function (int $no, string $msg, string $file, int $line): bool {
        throw new ErrorException($msg, 0, $no, $file, $line);
    });

    ob_start();
    try {
        // Included in function scope, so a page's variables cannot collide with
        // the next page's. sitelib.php is require_once, so it loads once.
        (static function (string $__page): void {
            require $__page;
        })($path);
        $html = (string) ob_get_clean();
    } catch (Throwable $e) {
        ob_end_clean();
        $warning = sprintf(
            '%s at %s:%d',
            $e->getMessage(),
            basename($e->getFile()),
            $e->getLine()
        );
    } finally {
        restore_error_handler();
    }

    return [$html, $warning];
}

function main(array $argv): int
{
    $verbose = in_array('--verbose', $argv, true);

    $outDir = null;
    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--out=')) {
            $outDir = substr($arg, 6);
        }
    }

    /*
     * Snapshot mode tells tools/sitelib.php to emit .html links instead of .php
     * ones. It has to be set before the first page is rendered, and it has to be
     * a constant rather than a global so a page cannot forget to read it.
     */
    if ($outDir !== null && !defined('VF_SNAPSHOT')) {
        define('VF_SNAPSHOT', true);
    }

    $css = @file_get_contents(dirname(__DIR__) . '/assets/style.css');
    if ($css === false) {
        fwrite(STDERR, "render_pages: assets/style.css is missing\n");
        return 1;
    }
    $css = rtrim($css, "\n");

    $pages = vf_page_list();
    if ($pages === []) {
        fwrite(STDERR, "render_pages: no pages found under pages/\n");
        return 1;
    }

    $failed = 0;
    $rendered = 0;

    foreach ($pages as $rel) {
        [$html, $warning] = vf_render_one($rel);

        if ($warning !== '') {
            fwrite(STDERR, "FAIL {$rel}: {$warning}\n");
            $failed++;
            continue;
        }

        if (!str_contains($html, '<!DOCTYPE html>')) {
            fwrite(STDERR, "FAIL {$rel}: output is not an HTML document\n");
            $failed++;
            continue;
        }

        $n = substr_count($html, $css);
        if ($n !== 1) {
            fwrite(STDERR, sprintf(
                "FAIL %s: stylesheet appears %d time(s), expected 1\n",
                $rel,
                $n
            ));
            $failed++;
            continue;
        }

        // A page with no navigation is a page that bypassed the shell even if
        // it somehow carries the stylesheet.
        if (!str_contains($html, '<nav class="nav">')) {
            fwrite(STDERR, "FAIL {$rel}: no navigation\n");
            $failed++;
            continue;
        }

        if ($outDir !== null) {
            $target = $outDir . '/' . substr($rel, 0, -4) . '.html';
            $dir = dirname($target);
            if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
                fwrite(STDERR, "FAIL {$rel}: cannot create {$dir}\n");
                $failed++;
                continue;
            }
            if (file_put_contents($target, $html) === false) {
                fwrite(STDERR, "FAIL {$rel}: cannot write {$target}\n");
                $failed++;
                continue;
            }
        }

        $rendered++;
        if ($verbose) {
            printf("ok   %-32s %6d bytes\n", $rel, strlen($html));
        }
    }

    if ($failed > 0) {
        fwrite(STDERR, sprintf(
            "\nrender_pages: %d problem(s) across %d page(s)\n",
            $failed,
            count($pages)
        ));
        return 1;
    }

    printf(
        "render: %d page(s) rendered clean, stylesheet and navigation present%s\n",
        $rendered,
        $outDir !== null ? ", snapshot written to {$outDir}" : ''
    );
    return 0;
}

exit(main($argv));
