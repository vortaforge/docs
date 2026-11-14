<?php
declare(strict_types=1);

/**
 * Maintainer guide
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php, so
 * it exists once for all fifteen pages.
 */

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = 'docs';
$file        = 'maintainers.php';
$title       = 'Maintainer guide';
$description = 'Release flow, archive rules, the site build, and the claims this project explicitly refuses to make.';
$crumbs      = '<a href="../index.php">Home</a> / <a href="archive.php">Docs</a> / Maintainers';

ob_start();
?>
<p class="crumbs"><a href="../index.php">Home</a> / <a href="archive.php">Docs</a> / Maintainers</p>
<div class="hero">
  <h1>Maintainer guide</h1>
  <p class="lede">How a change gets from a branch to a release, what gets
     archived and how, and the claims this project refuses to make.</p>
</div>

<div class="toc">
  <strong>On this page</strong>
  <ol>
    <li><a href="#guard">The repository guard</a></li>
    <li><a href="#archive">Archive rules</a></li>
    <li><a href="#never">The rule that outranks the others</a></li>
    <li><a href="#release">Release flow</a></li>
    <li><a href="#site">Building this site</a></li>
    <li><a href="#backups">Backups</a></li>
    <li><a href="#honesty">What this project will not claim</a></li>
  </ol>
</div>

<h2 id="guard">The repository guard</h2>
<p>The guard used to be a build step that refused to package a retired pack
   unless an environment variable was set. It is gone, and the job moved to the
   build layout instead:</p>

<pre><code>def SCAN_ROOTS = ['mods', 'packs', 'examples']</code></pre>

<p><code>archived/</code> is not a scan root, so nothing underneath it can
   become a subproject. That is not a check that can be bypassed with a flag -
   there is no code path from <code>archived/</code> to <code>gradlew dist</code>
   at all. Removing the environment variable removed the bypass, not the
   protection.</p>

<h2 id="archive">Archive rules</h2>
<table>
  <thead><tr><th>Rule</th><th>Why</th></tr></thead>
  <tbody>
    <tr><td>A retired project gets a row in
            <code>archived/archive.json</code>.</td>
        <td>Nineteen fields, so a question six months later has an answer
            without a forensic walk through git.</td></tr>
    <tr><td>Its <code>reason</code> names what happened, not that it was
            bad.</td>
        <td>"A Fabric mod superseded it" is checkable. "It was old" is not.</td></tr>
    <tr><td>Its <code>original_path</code> is recorded if it moved.</td>
        <td>The move is the interesting part - it usually explains the
            retirement.</td></tr>
    <tr><td>The row is never deleted.</td>
        <td>Deleting it removes the only evidence the thing existed.</td></tr>
    <tr><td>Provenance prose and history files are not rewritten.</td>
        <td>An identifier change (a package rename, a namespace move) must not
            touch CREDITS-style text. That text is the record of what was.</td></tr>
    <tr><td>A patch series must still apply.</td>
        <td>If <code>archived/patches/*.patch</code> stops applying, the record
            of how a change was made is gone.</td></tr>
  </tbody>
</table>

<h2 id="never">The rule that outranks the others</h2>
<div class="note">
  <strong>Move identifiers, never history.</strong> When a namespace changes -
  <code>vortacraftmc</code> becoming <code>vortaforge</code>, say - code, paths,
  package names and scoreboard holders move. Comments, credits, changelog text
  and patches do not. A rename that also rewrites the past makes the past
  unverifiable.
</div>

<p>The practical test: after a rename, <code>git am</code> on the archived patch
   series has to still succeed. If it does not, the rename reached into history
   and needs to be undone.</p>

<h2 id="release">Release flow</h2>
<ol>
  <li><strong>Branch from <code>main</code>.</strong> Never commit a release
      directly - even though some repositories here allow it.</li>
  <li><strong>Run the gates that exist.</strong>
      <code>gradlew check</code>, <code>python3 tools/verify.py --pack packs</code>,
      <code>python3 -m pytest scripts/tests</code>. Verify prints
      <code>[verify] OK</code> or a list of findings; a non-empty list is a
      stop.</li>
  <li><strong>Build the artifacts.</strong> <code>gradlew dist</code>. Every
      module jar must contain <code>fabric.mod.json</code>,
      <code>pack.mcmeta</code> and its own <code>data/</code> tree - nothing
      more.</li>
  <li><strong>Open a pull request.</strong> A ruleset requires one approving
      review and three named checks; self-approval is rejected on purpose.</li>
  <li><strong>Wait for green, then merge.</strong> Squash to one commit.</li>
</ol>

<table>
  <thead><tr><th>Check</th><th>Fails when</th></tr></thead>
  <tbody>
    <tr><td><code>verify.py</code></td><td>a dangling reference, a module
        calling a module, a module shipping <code>core/internal/</code>, a
        cycle, a missing <code>pack.mcmeta</code></td></tr>
    <tr><td><code>test_module_removal</code></td><td>any of the 34 packs, built
        without one sibling, fails to complete load and tick</td></tr>
    <tr><td><code>test_split_guard</code></td><td>a module other than core
        claims a <code>core/internal/</code> prefix</td></tr>
    <tr><td>Datapack guard</td><td>a pack outside <code>mods/</code>,
        <code>packs/</code> or <code>examples/</code> tries to be buildable</td></tr>
  </tbody>
</table>

<h2 id="site">Building this site</h2>
<p>The documentation is PHP with no framework. Each page is a script that sets
   four variables and writes its body; the shell around it exists once.</p>

<table>
  <thead><tr><th>Tool</th><th>Runs</th><th>Produces</th></tr></thead>
  <tbody>
    <tr><td><code>tools/sitelib.php</code></td><td>required by every page</td>
        <td>the shell - doctype, head, nav, footer, inlined stylesheet</td></tr>
    <tr><td><code>tools/render_pages.php</code></td><td><code>php-cli</code></td>
        <td>a pass/fail over all 16 pages; with <code>--out=build</code> the
            static snapshot</td></tr>
    <tr><td><code>tools/build_docs_index.php</code></td><td><code>php-cli</code></td>
        <td><code>_site/docs-index.json</code> - every page, its section and
            its word count</td></tr>
    <tr><td><code>tools/test_php_safety.php</code></td><td><code>php-cli</code></td>
        <td>exit 0 or a list of violations</td></tr>
    <tr><td><code>tools/cli.php</code></td><td>required by the build tools</td>
        <td>the 404-on-web guard and the PHP 8.1 floor</td></tr>
  </tbody>
</table>

<p>Two different rules apply, and the safety test scopes them by directory:</p>

<table>
  <thead><tr><th>Directory</th><th>May run on a server</th><th>Must never</th></tr></thead>
  <tbody>
    <tr><td><code>pages/</code></td><td>yes - that is what it is for</td>
        <td>read request input, write files, shell out</td></tr>
    <tr><td><code>tools/</code></td>
        <td><span class="no">no</span> - exits 404 under any web SAPI</td>
        <td>read request input, shell out</td></tr>
  </tbody>
</table>

<p>The static site on GitHub Pages is generated from the same pages by
   <code>tools/render_pages.php --out=build</code>, so it cannot drift from the
   PHP source. Details on <a href="php.php">PHP tooling</a>.</p>

<h2 id="backups">Backups</h2>
<p>Two things are not in git and need to survive a machine loss:</p>
<ul>
  <li><strong>World saves containing scoreboard state.</strong> A macroEngine
      install writes its variables into objectives that live in the world. The
      repository cannot restore them.</li>
  <li><strong>Built artifacts</strong> if a release is pinned somewhere. They
      are reproducible from a tag, but only if the tag and the toolchain both
      still exist.</li>
</ul>

<h2 id="honesty">What this project will not claim</h2>
<table>
  <thead><tr><th>Will not claim</th><th>Because</th></tr></thead>
  <tbody>
    <tr><td>A gate makes a bad decision safe.</td>
        <td>A gate blocks the honest user and inconveniences the other one.
            Both gates were removed for this reason.</td></tr>
    <tr><td>Archived means broken - or means safe.</td>
        <td>It means unsupported. Those are three different things and only one
            of them is knowable from the metadata.</td></tr>
    <tr><td>Modules are independent by design.</td>
        <td>They are independent because a test builds each one without its
            siblings. The design is the seam; the test is the evidence.</td></tr>
    <tr><td>A warning is an audit.</td>
        <td>It reports status, not safety. Confusing the two is what made the
            gate look reasonable.</td></tr>
    <tr><td>Removing a module cannot change behaviour.</td>
        <td>It changes behaviour by definition - that feature stops. What it
            cannot do is stop the engine.</td></tr>
  </tbody>
</table>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
