#!/usr/bin/env python3
"""Keep every page's <style> block byte-identical to assets/style.css.

The site is hand-written HTML with inline CSS and no build step. That is
deliberate - a page has to render with no network at all - but it is how seven
near-duplicate stylesheets drifted apart in the first place. This script is the
cure and the check at the same time:

    python3 tools/sync_style.py --check   # exit 1 if any page is out of date
    python3 tools/sync_style.py --write   # replace every page's block

It only touches the <style> block. Everything else in a page is left exactly as
written.
"""

from __future__ import annotations

import argparse
import pathlib
import re
import sys

ROOT = pathlib.Path(__file__).resolve().parent.parent
STYLE = ROOT / "assets" / "style.css"
BLOCK = re.compile(r"<style>.*?</style>", re.S)

# Pages that are built output, not source.
SKIP_DIRS = {"_site", "_publish", ".git", "node_modules"}


def pages() -> list[pathlib.Path]:
    out = []
    for p in sorted(ROOT.rglob("*.html")):
        if any(part in SKIP_DIRS for part in p.parts):
            continue
        out.append(p)
    return out


def main() -> int:
    ap = argparse.ArgumentParser()
    g = ap.add_mutually_exclusive_group(required=True)
    g.add_argument("--check", action="store_true")
    g.add_argument("--write", action="store_true")
    a = ap.parse_args()

    css = STYLE.read_text(encoding="utf-8").rstrip("\n")
    block = f"<style>\n{css}\n</style>"

    stale, missing = [], []
    for p in pages():
        text = p.read_text(encoding="utf-8")
        if not BLOCK.search(text):
            missing.append(p)
            continue
        if BLOCK.search(text).group(0) != block:
            stale.append(p)
            if a.write:
                p.write_text(BLOCK.sub(lambda _m: block, text, count=1), encoding="utf-8")

    if missing:
        for p in missing:
            print(f"no <style> block: {p.relative_to(ROOT)}", file=sys.stderr)

    if a.write:
        print(f"style.css written into {len(stale)} page(s); "
              f"{len(pages()) - len(stale) - len(missing)} already matched")
        return 1 if missing else 0

    if stale:
        print(f"{len(stale)} page(s) do not match assets/style.css "
              f"- run: python3 tools/sync_style.py --write", file=sys.stderr)
        for p in stale:
            print(f"  {p.relative_to(ROOT)}", file=sys.stderr)
        return 1
    if missing:
        return 1
    print(f"style: {len(pages())} page(s) match assets/style.css")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
