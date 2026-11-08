<?php
declare(strict_types=1);

/**
 * Shared page shell for the VortaForge docs site.
 *
 * The pages are real PHP now: each one under pages/ starts with a small front
 * block that sets $title, $description, $section and $crumbs, and ends with a
 * one-line include of this file. Nothing about a page's content is generated -
 * what changes is that the chrome (doctype, head, stylesheet, navigation,
 * footer) exists once instead of fifteen times.
 *
 * This mirrors tools/sitelib.py, which is kept as the reference implementation
 * and as the tool that renders the static snapshot in build/ for hosts that
 * cannot run PHP. assets/style.css remains the single source for the stylesheet
 * and is inlined into every page, because the published site has to survive a
 * renderer with no network access.
 *
 * CLI use: `php tools/sitelib.php` prints the site map, which is what the
 * navigation is built from, so the two can be compared without opening a page.
 */

// Relative prefix from a section directory back to the site root.
function vf_prefix(string $section): string
{
    return $section === '.' ? '' : '../';
}

/**
 * The site map, in navigation order: [section, file, label].
 *
 * Section '.' means the site root; every other section is a directory under it.
 * A page is marked "here" when its own path matches, and its whole section is
 * marked when the page is the section index.
 */
function vf_nav(): array
{
    return [
        ['.',            'index.php',              'Home'],
        ['mods',         'index.php',              'Mods'],
        ['macroengine',  'index.php',              'macroEngine'],
        ['datapacks',    'index.php',              'Datapacks'],
        ['docs',         'api.php',                'API'],
        ['docs',         'versions.php',           'Versions'],
        ['docs',         'php.php',                'PHP'],
        ['docs',         'warnings.php',           'Warnings'],
        ['docs',         'archive.php',            'Archive'],
        ['docs',         'maintainers.php',        'Maintainers'],
    ];
}

/**
 * Resolve a nav entry to a URL from the page currently being rendered.
 *
 * $section is the current page's section, $file its filename. The result is
 * relative so the site works from a file:// path and from a subdirectory
 * deployment without either knowing about the other.
 */
function vf_href(string $fromSection, string $toSection, string $file): string
{
    $up = $fromSection === '.' ? '' : '../';
    $down = $toSection === '.' ? '' : $toSection . '/';
    return $up . $down . vf_asset_name($file);
}

/**
 * What a link to this file should say.
 *
 * Normally the file's own name. When the site is being rendered to a static
 * snapshot the extension becomes .html, because that is what exists on disk
 * afterwards - a snapshot full of links to .php would link to files it does not
 * contain, and on a static host it would link to source.
 *
 * tools/render_pages.php --out=... defines VF_SNAPSHOT before rendering. Doing
 * it with a constant rather than rewriting the output means the substitution
 * happens where the URLs are made, not by a regex over finished HTML.
 */
function vf_asset_name(string $file): string
{
    if (defined('VF_SNAPSHOT') && VF_SNAPSHOT === true && str_ends_with($file, '.php')) {
        return substr($file, 0, -4) . '.html';
    }
    return $file;
}

/** Rewrite in-page links for the snapshot. Used on the body, not the shell. */
function vf_snapshot_body(string $html): string
{
    if (!defined('VF_SNAPSHOT') || VF_SNAPSHOT !== true) {
        return $html;
    }
    // The fragment has to survive: archive links point at a row id on another
    // page, so "archive.php#dailybonus" must become "archive.html#dailybonus".
    return preg_replace(
        '/href="([^"]*?)\.php(#[^"]*)?"/',
        'href="$1.html$2"',
        $html
    ) ?? $html;
}

function vf_nav_html(string $section, string $file): string
{
    $items = '';
    foreach (vf_nav() as [$toSection, $toFile, $label]) {
        $here = ($toSection === $section && $toFile === $file);
        $cls = $here ? ' class="here"' : '';
        $items .= sprintf(
            '<a href="%s"%s>%s</a>',
            htmlspecialchars(vf_href($section, $toSection, $toFile), ENT_QUOTES),
            $cls,
            htmlspecialchars($label, ENT_QUOTES)
        );
    }
    return '<nav class="nav">' . $items . '</nav>';
}

function vf_style(): string
{
    $css = @file_get_contents(__DIR__ . '/../assets/style.css');
    if ($css === false) {
        // A missing stylesheet is a build error, not something to paper over:
        // every page would render unstyled and nobody would notice until a
        // visitor did.
        throw new RuntimeException('assets/style.css is missing');
    }
    return $css;
}

function vf_footer_html(): string
{
    return '<footer class="foot">'
        . '<p><strong>VortaForge documentation</strong> &mdash; published from '
        . '<a href="https://github.com/vortaforge/docs">vortaforge/docs</a>. '
        . 'Corrections go there, not here.</p>'
        . '<p>The datapacks under <code>archived/</code> are kept as readable '
        . 'history. They are archived and disarmed: not maintained, not '
        . 'supported, and not recommended for a live server.</p>'
        . '</footer>';
}

/**
 * Render a page.
 *
 * $body is the page's HTML, already escaped by the author where needed. This
 * function does not sanitise it - the pages are ours, and a template that
 * pretends to sanitise HTML invites the belief that it does.
 */
function vf_page(
    string $section,
    string $file,
    string $title,
    string $description,
    string $body,
    string $crumbs
): string {
    $body = vf_snapshot_body($body);
    $t = htmlspecialchars($title, ENT_QUOTES);
    $d = htmlspecialchars($description, ENT_QUOTES);
    $style = vf_style();
    $nav = vf_nav_html($section, $file);
    $foot = vf_footer_html();

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$t} &mdash; VortaForge documentation</title>
<meta name="description" content="{$d}">
<style>
{$style}
</style>
</head>
<body>
<div class="wrap">
{$nav}
{$body}
{$foot}
</div>
</body>
</html>

HTML;
}

/**
 * Emit a rendered page. Used by every page's last line.
 *
 * The Content-Type header is set only when a web server is answering. Under
 * `cli` - which includes tools/render_pages.php rendering the whole site to
 * check it - headers do not exist, and calling header() there emits a warning
 * that would be indistinguishable from a real fault in a page.
 */
function vf_render(
    string $section,
    string $file,
    string $title,
    string $description,
    string $body,
    string $crumbs
): void {
    if (PHP_SAPI !== 'cli' && !headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    echo vf_page($section, $file, $title, $description, $body, $crumbs);
}

// --- CLI: print the map -----------------------------------------------------

/*
 * Only when this file is the script that was invoked. Pages require it, and a
 * page must never print the site map instead of itself - which is exactly what
 * happened the first time this was guarded on PHP_SAPI alone, because a page
 * rendered from the command line is also SAPI `cli`.
 */
/*
 * `debug_backtrace()` is asked instead of `$_SERVER['SCRIPT_FILENAME']` because
 * this file must not read a request superglobal at all: tools/test_php_safety.php
 * bans them outright, and exempting one file "because it is ours" is how that
 * ban starts to erode. An empty trace means nothing required us, so we are the
 * entry point.
 */
$vf_direct = PHP_SAPI === 'cli' && count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)) === 0;

if ($vf_direct) {
    require_once __DIR__ . '/cli.php';
    $width = 0;
    foreach (vf_nav() as [$s, $f]) {
        $width = max($width, strlen($s === '.' ? $f : $s . '/' . $f));
    }
    foreach (vf_nav() as [$s, $f, $label]) {
        $path = $s === '.' ? $f : $s . '/' . $f;
        printf("%-{$width}s  %s\n", $path, $label);
    }
    exit(0);
}
