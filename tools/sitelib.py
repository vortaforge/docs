"""Shared page shell and navigation for the docs site.

This is not a static-site generator: the pages are still written by hand. What
this does is keep the chrome - doctype, head, stylesheet, navigation, footer -
in one place, so thirteen pages cannot drift apart in the way they had before
(seven different stylesheets, no navigation anywhere, and a "Home" link that
pointed at three different things).

`page()` returns the full HTML for a page. The body is passed in as HTML text.
"""

from __future__ import annotations

import pathlib
from html import escape

ROOT = pathlib.Path(__file__).resolve().parent.parent
STYLE = ROOT / "assets" / "style.css"

# The site map, in navigation order. `key` is what callers pass as `here`.
NAV = [
    ("home",         "index.html",                  "Home"),
    ("mods",         "mods/index.html",             "Mods"),
    ("engine",       "macroengine/index.html",      "macroEngine"),
    ("datapacks",    "datapacks/index.html",        "Datapacks"),
    ("api",          "docs/api.html",               "API"),
    ("versions",     "docs/versions.html",          "Versions"),
    ("php",          "docs/php.html",               "PHP"),
    ("warnings",     "docs/warnings.html",          "Warnings"),
    ("archive",      "docs/archive.html",           "Archive"),
    ("maintainers",  "docs/maintainers.html",       "Maintainers"),
]

TAGLINE = "VortaForge documentation"


def _rel_prefix(path: str) -> str:
    """How to get from a page back to the site root."""
    depth = path.count("/")
    return "../" * depth if depth else ""


def _nav_html(here_path: str) -> str:
    prefix = _rel_prefix(here_path)
    items = []
    for key, href, label in NAV:
        current = href == here_path
        # A section is "here" for every page inside it.
        if not current and here_path != "index.html":
            section = href.rsplit("/", 1)[0] if "/" in href else href
            if section and here_path.startswith(section + "/"):
                current = True
        cls = ' class="here"' if current else ""
        items.append(f'<a href="{prefix}{href}"{cls}>{escape(label)}</a>')
    return '<nav class="nav">' + "".join(items) + "</nav>"


def _footer_html(here_path: str) -> str:
    prefix = _rel_prefix(here_path)
    return (
        '<footer>\n'
        f'  <p><strong>{TAGLINE}</strong> &mdash; published from '
        '<a href="https://github.com/vortaforge/docs">vortaforge/docs</a>. '
        'Corrections go there, not here.</p>\n'
        '  <p>The datapacks under <code>archived/</code> are kept as readable '
        'history. They are archived and disarmed: not maintained, not supported, '
        'and not recommended for a live server.</p>\n'
        '</footer>'
    )


def page(
    *,
    path: str,
    title: str,
    description: str,
    body: str,
    crumbs: str | None = None,
) -> str:
    """Build a complete page.

    path        the page's own path from the site root, e.g. "mods/index.html"
    title       the <title>, without the site suffix
    description the meta description
    body        the page's HTML body
    crumbs      optional breadcrumb HTML
    """
    css = STYLE.read_text(encoding="utf-8").rstrip("\n")
    prefix = _rel_prefix(path)
    crumb_html = f'<p class="crumbs">{crumbs}</p>\n' if crumbs else ""
    return f"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{escape(title)} &mdash; {TAGLINE}</title>
<meta name="description" content="{escape(description)}">
<style>
{css}
</style>
</head>
<body>
<div class="wrap">
{_nav_html(path)}
{crumb_html}{body.strip()}
{_footer_html(path)}
</div>
</body>
</html>
"""
