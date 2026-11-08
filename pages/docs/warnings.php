<?php
declare(strict_types=1);

/**
 * Warnings
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php, so
 * it exists once for all fifteen pages.
 */

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = 'docs';
$file        = 'warnings.php';
$title       = 'Warnings';
$description = 'What the archived-pack notice means, what disarmed means, and why the runtime gates were removed rather than fixed.';
$crumbs      = '<a href="../index.php">Home</a> / <a href="archive.php">Docs</a> / Warnings';

ob_start();
?>
<p class="crumbs"><a href="../index.php">Home</a> / <a href="archive.php">Docs</a> / Warnings</p>
<div class="hero">
  <h1>Warnings</h1>
  <p class="lede">What the notice on an archived pack means, what "disarmed"
     means, and why there is no longer anything stopping you from loading one.</p>
</div>

<div class="toc">
  <strong>On this page</strong>
  <ol>
    <li><a href="#notice">What you clicked</a></li>
    <li><a href="#disarmed">What "disarmed" means</a></li>
    <li><a href="#gate">Gates, and why they were removed</a></li>
    <li><a href="#version">Why some packs cannot show the warning</a></li>
    <li><a href="#you-should">If you still want to run a pack</a></li>
    <li><a href="#trust">What a warning is not</a></li>
    <li><a href="#other-docs">Other documentation</a></li>
  </ol>
</div>

<h2 id="notice">1. What you clicked</h2>
<p>An archived project's page carries a notice. It says the project is retired,
   names the last version that worked and points at whatever replaced it, if
   anything did. It is generated from <code>archived/archive.json</code>, so it
   cannot drift from the archive table on
   <a href="archive.php">the archive page</a>.</p>

<p>The notice exists because a retired pack looks identical to a live one from
   the outside. Same jar, same <code>pack.mcmeta</code>, same function ids. The
   only difference is that nobody has tested it against the release you are
   running.</p>

<h2 id="disarmed">2. What "disarmed" means</h2>
<p>Disarmed means the project is excluded from the build. It is still in the
   repository, still readable, still historically accurate - but
   <code>settings.gradle</code> does not scan <code>archived/</code>, so it never
   becomes a subproject and never produces an artifact.</p>

<pre><code>// settings.gradle, verbatim
// 'archived/' is deliberately NOT a scan root: everything there is disarmed
// (see archived/archive.json) and must never become a buildable subproject
// again.
def SCAN_ROOTS = ['mods', 'packs', 'examples']</code></pre>

<p>That is a stronger claim than "we do not build it". It means an archived
   pack cannot re-enter a release by accident: no glob picks it up, no
   <code>gradlew dist</code> emits it, and the archive JSON is the only place
   that knows it exists.</p>

<h2 id="gate">3. Gates, and why they were removed</h2>

<h3>What a gate was</h3>
<p>A gate was a runtime check inside the engine that refused to work until a
   condition was met. Two existed:</p>

<table>
  <thead><tr><th>Gate</th><th>Where</th><th>What it did</th></tr></thead>
  <tbody>
    <tr><td>In-pack load gate</td><td><code>macroengine:gate/is_open</code></td>
        <td>Aborted the engine's own load if the pack had not been
            acknowledged.</td></tr>
    <tr><td>Repository guard</td><td>the build</td>
        <td>Refused to package a retired pack unless an override was set.</td></tr>
  </tbody>
</table>

<h3>Why both went</h3>
<p>A gate is a defect, not a safeguard. Three reasons, in the order they became
   obvious:</p>

<ol>
  <li><strong>It blocked the wrong person.</strong> Someone running an archived
      pack inside their own world was stopped by a check they had no way to
      satisfy. Someone determined to run it deleted one line.</li>
  <li><strong>It failed in the dark.</strong> A gated pack did not crash; it
      loaded and then did nothing, which looks exactly like a broken install.
      The failure mode was worse than the thing it prevented.</li>
  <li><strong>It cost the modules.</strong> Every module had to prove it was
      allowed to run, which meant every module carried the gate's assumptions.
      Removing the gate removed that dependency from 34 packs at once.</li>
</ol>

<p>The id <code>macroengine:gate/is_open</code> no longer resolves. If you are
   reading an old function that calls it, delete the call - nothing replaces
   it, because nothing needs to.</p>

<div class="warnbox">
  <strong>A warning is not a gate.</strong> It never was. A warning tells the
  truth about a pack's status; a gate refused to run it. The warning survived
  because it is information. The gate did not, because it was enforcement with a
  bypass and no upside.
</div>

<h2 id="version">4. Why some packs cannot show the warning</h2>
<p>The warning is a function wired into the <code>load</code> tag. Packs that
   predate that wiring - anything written before the warning existed, and packs
   whose own load path never runs - have no way to display it.</p>

<p>Two cases in the archive:</p>
<ul>
  <li><strong>Older packs (pack format 6&ndash;41).</strong> Written before the
      load hook existed. They work or they do not; there is nothing to hook.</li>
  <li><strong>Packs with no load function at all.</strong> A pack that only
      defines tags for other packs to call has no entry point of its own, so
      there is nowhere to mount a message.</li>
</ul>

<p>This is a limitation of where the message can be attached, not a statement
   about those packs being safer. If a pack cannot warn you, that is the reason
   to read <a href="archive.php">the archive</a> instead.</p>

<h2 id="you-should">5. If you still want to run a pack</h2>
<p>Reasonable - the archived packs are small, readable, and some of them still
   work. Do these four things:</p>

<ol>
  <li><strong>Back up the world first.</strong> Not because the pack is
      malicious; because the first symptom of a version mismatch is often a
      half-applied change to a scoreboard objective.</li>
  <li><strong>Check the pack format.</strong> Compare the format column on
      <a href="versions.php">Versions</a> with the one your server reads. A
      mismatch is the single most common reason an old pack does nothing.</li>
  <li><strong>Read its <code>load</code> function.</strong> If it sets
      objectives, restore them before you remove the pack, or the objective
      stays and the values stop updating.</li>
  <li><strong>Do not run it next to macroEngine.</strong> Both may claim the
      same scoreboard holders. The engine's are documented; an old pack's are
      not.</li>
</ol>

<h2 id="trust">6. What a warning is not</h2>
<div class="note">
  <strong>It is not an audit.</strong> It does not say the pack is safe to run,
  and it never gated anything. It says the pack is retired - which is a fact
  about maintenance, not about behaviour.
</div>

<p>That distinction is the whole point of removing the gates. A warning you can
   ignore and a gate you cannot were being confused for each other, and the
   gate was making the confusion worse by pretending to be a safety feature.</p>

<h2 id="other-docs">7. Other documentation</h2>
<table>
  <thead><tr><th>Page</th><th>What it answers</th></tr></thead>
  <tbody>
    <tr><td><a href="archive.php">Archive</a></td>
        <td>All 32 retired projects and why each stopped</td></tr>
    <tr><td><a href="versions.php">Versions</a></td>
        <td>Which build targets which release; pack format decoder</td></tr>
    <tr><td><a href="api.php">API</a></td>
        <td>The 22 hook slots and the module contract</td></tr>
    <tr><td><a href="maintainers.php">Maintainer guide</a></td>
        <td>Release flow, archive rules and what this project will not claim</td></tr>
    <tr><td><a href="php.php">PHP tooling</a></td>
        <td>The build-time index generator and the safety test</td></tr>
    <tr><td><a href="../datapacks/index.php">Datapacks</a></td>
        <td>The archived packs, their formats and the successor question</td></tr>
  </tbody>
</table>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
