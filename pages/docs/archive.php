<?php
declare(strict_types=1);

/**
 * Archive
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php, so
 * it exists once for all fifteen pages.
 */

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = 'docs';
$file        = 'archive.php';
$title       = 'Archive';
$description = 'All 32 retired VortaForge projects: last version, target Minecraft release, and the reason each was archived.';
$crumbs      = '<a href="../index.php">Home</a> / <a href="archive.php">Archive</a>';

ob_start();
?>
<p class="crumbs"><a href="../index.php">Home</a> / <a href="archive.php">Archive</a></p>
<div class="hero">
  <h1>Archive</h1>
  <p class="lede">Every project VortaForge has retired, with its last version,
     the Minecraft release it targeted and the reason it stopped.</p>
</div>

<div class="banner">
  <strong>32 projects are registered here and all of them are
  retired.</strong> 11 datapacks, one resource pack,
  16 scripts and 1 mod form the rest.
  This page exists so that "what happened to X" has an answer, not so that X is
  worth installing.
</div>

<h2>The rule behind the table</h2>
<p>Retirement is recorded, not deleted. Deleting a project removes the only
   evidence that it existed, which makes an old world's missing
   <code>scoreboard</code> holder impossible to explain a year later. So each
   project gets a row: what it was, what it targeted, and what replaced it if
   anything did.</p>

<p>Two consequences worth stating plainly:</p>
<ul>
  <li><strong>Archived does not mean broken.</strong> Several of these still
      load on the version they targeted. They are unsupported, which is a
      different claim.</li>
  <li><strong>A row is not a recommendation.</strong> The
      <code>reason</code> field is the interesting one, and it is almost never
      "it was bad" - usually the Minecraft feature it patched around shipped
      natively, or the mod superseded it.</li>
</ul>

<h2>All 32 registered projects</h2>
<p>Grouped by kind. Ids are stable and match <code>archive.json</code>; the
   links from <a href="../datapacks/index.php">Datapacks</a> anchor to the row
   ids below.</p>

<h3>Datapacks <span class="pill">11</span></h3>
<table>
  <thead><tr><th>Id</th><th>Last version</th><th>Minecraft</th><th>License</th><th>Status</th></tr></thead>
  <tbody>
    <tr><td id="cmdtunnel-datapack"><code>cmdtunnel-datapack</code></td><td>main@2026-10-26</td><td>1.19.3-1.19.4</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="dailybonus"><code>dailybonus</code></td><td>main@2026-11-05</td><td>Minecraft 26.x</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="guikitfw"><code>guikitfw</code></td><td>main@2026-11-02</td><td>Minecraft 26.x</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="interaction-click-detection"><code>interaction-click-detection</code></td><td>1.0.0</td><td>1.20.x</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="left-click-detection"><code>left-click-detection</code></td><td>1.0.1</td><td>1.20.5-1.21.x</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="macroengine-datapack"><code>macroengine-datapack</code></td><td>26.3</td><td>26.3</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="quickshare"><code>quickshare</code></td><td>main@2026-10-31</td><td>Minecraft 26.x</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="rtwrapper"><code>rtwrapper</code></td><td>26.3</td><td>26.3</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="rtwrapper-1-0-1"><code>rtwrapper-1-0-1</code></td><td>1.0.1</td><td>26.3</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="tunnelscript"><code>tunnelscript</code></td><td>1.1.0</td><td>1.19.2</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="vf-macro"><code>vf-macro</code></td><td>1.9.1</td><td>1.16-1.20</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
  </tbody>
</table>

<h3>Resource pack <span class="pill">1</span></h3>
<table>
  <thead><tr><th>Id</th><th>Last version</th><th>Minecraft</th><th>License</th><th>Status</th></tr></thead>
  <tbody>
    <tr><td id="macroengine-resourcepack"><code>macroengine-resourcepack</code></td><td>26.3</td><td>26.3</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
  </tbody>
</table>

<h3>Scripts / tooling <span class="pill">16</span></h3>
<table>
  <thead><tr><th>Id</th><th>Last version</th><th>Minecraft</th><th>License</th><th>Status</th></tr></thead>
  <tbody>
    <tr><td id="build-check-packs"><code>build-check-packs</code></td><td>main@2026-11-05</td><td>&mdash;</td><td>Unlicense</td><td>archived</td></tr>
    <tr><td id="build-merge-datapacks"><code>build-merge-datapacks</code></td><td>main@2026-11-05</td><td>&mdash;</td><td>Unlicense</td><td>archived</td></tr>
    <tr><td id="build-origin-watermark-helpers"><code>build-origin-watermark-helpers</code></td><td>main@2026-11-05</td><td>&mdash;</td><td>Unlicense</td><td>archived</td></tr>
    <tr><td id="build-pack-packaging-helpers"><code>build-pack-packaging-helpers</code></td><td>main@2026-11-05</td><td>&mdash;</td><td>Unlicense</td><td>archived</td></tr>
    <tr><td id="build-pack-validation-helpers"><code>build-pack-validation-helpers</code></td><td>main@2026-11-05</td><td>&mdash;</td><td>Unlicense</td><td>archived</td></tr>
    <tr><td id="build-release"><code>build-release</code></td><td>main@2026-11-05</td><td>&mdash;</td><td>Unlicense</td><td>archived</td></tr>
    <tr><td id="build-site-data-php"><code>build-site-data-php</code></td><td>main@2026-11-05</td><td>&mdash;</td><td>Unlicense</td><td>superseded</td></tr>
    <tr><td id="build-zip-packs"><code>build-zip-packs</code></td><td>main@2026-11-05</td><td>&mdash;</td><td>Unlicense</td><td>archived</td></tr>
    <tr><td id="datapack-lint"><code>datapack-lint</code></td><td>1.0.0</td><td>&mdash;</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="dpgen"><code>dpgen</code></td><td>1.0.0</td><td>&mdash;</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="fix-text-components"><code>fix-text-components</code></td><td>n/a</td><td>&mdash;</td><td>Unlicense (repository LICENSE)</td><td>archived</td></tr>
    <tr><td id="gui-generator"><code>gui-generator</code></td><td>0.0.0</td><td>&mdash;</td><td>Unlicense (package LICENSE)</td><td>archived</td></tr>
    <tr><td id="lint-datapacks-sh"><code>lint-datapacks-sh</code></td><td>main@2026-10-30</td><td>&mdash;</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="patches-gh"><code>patches-gh</code></td><td>main@2026-11-05</td><td>&mdash;</td><td>Unlicense</td><td>archived</td></tr>
    <tr><td id="remove-handover"><code>remove-handover</code></td><td>n/a</td><td>&mdash;</td><td>Unlicense (repository LICENSE)</td><td>archived</td></tr>
    <tr><td id="ward"><code>ward</code></td><td>main@2026-10-29</td><td>&mdash;</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
  </tbody>
</table>

<h3>Mods <span class="pill">1</span></h3>
<table>
  <thead><tr><th>Id</th><th>Last version</th><th>Minecraft</th><th>License</th><th>Status</th></tr></thead>
  <tbody>
    <tr><td id="datapack-blocker-mod"><code>datapack-blocker-mod</code></td><td>1.1.0</td><td>&mdash;</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
  </tbody>
</table>

<h3>Other <span class="pill">3</span></h3>
<table>
  <thead><tr><th>Id</th><th>Last version</th><th>Minecraft</th><th>License</th><th>Status</th></tr></thead>
  <tbody>
    <tr><td id="dynamic-command-gate-doc"><code>dynamic-command-gate-doc</code></td><td>main@2026-10-29</td><td>&mdash;</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="static-analysis-findings"><code>static-analysis-findings</code></td><td>main@2026-10-30</td><td>&mdash;</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
    <tr><td id="template-datapack"><code>template-datapack</code></td><td>main@2026-10-16</td><td>&mdash;</td><td>Unlicense (pack LICENSE)</td><td>archived</td></tr>
  </tbody>
</table>

<h2>Why things were archived</h2>
<table>
  <thead><tr><th>Reason</th><th>What it means</th><th>Typical case</th></tr></thead>
  <tbody>
    <tr><td>Superseded by a mod</td>
        <td>A Fabric mod now does the same job with less maintenance, because a
            mod is not re-tested against every datapack format change.</td>
        <td>macroEngine the datapack &rarr; macroEngine the 34-module mod set</td></tr>
    <tr><td>Vanilla absorbed it</td>
        <td>Minecraft shipped the feature, so the workaround became a smaller,
            worse version of the thing itself.</td>
        <td>A pack that emulated a game rule the release then added</td></tr>
    <tr><td>Retired with its host</td>
        <td>The pack only made sense alongside another pack. Both stop, or the
            supported half becomes unreachable.</td>
        <td>macroEngine's resource pack (i18n, sounds, trim assets)</td></tr>
    <tr><td>Never a deliverable</td>
        <td>A test harness, a generator or a one-off that was useful during
            development and has no user.</td>
        <td>Scripts and tooling in the rows above</td></tr>
  </tbody>
</table>

<h2>What replaced them</h2>
<table>
  <thead><tr><th>Retired</th><th>Successor</th><th>Difference that mattered</th></tr></thead>
  <tbody>
    <tr><td>macroEngine-Datapack</td><td>macroEngine, 34 module jars</td>
        <td>Was one monolithic pack; now one core plus 33 modules where any
            module can be absent without breaking load or tick.</td></tr>
    <tr><td>RTWrapper, RTWrapper-v1.0.1</td><td>&mdash;</td>
        <td>Both were <strong>fully removed</strong>, not deprecated. The
            wrappers gated the engine at runtime; the gates were a defect and
            the modules absorbed the work.</td></tr>
    <tr><td>DailyBonus, QuickShare, guikitfw</td><td>&mdash;</td>
        <td>Stood alone. Nothing replaced them, and nothing needs to.</td></tr>
  </tbody>
</table>

<div class="note">
  <strong>The table is generated from <code>archive.json</code>, not typed by
  hand.</strong> 32 projects, 19 fields each. If a row here disagrees
  with the JSON, the JSON is right - and
  <a href="php.php">the build tooling</a> reads it the same way this page was
  written.
</div>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
