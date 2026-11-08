<?php
declare(strict_types=1);

/**
 * Version matrix
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php, so
 * it exists once for all fifteen pages.
 */

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = 'docs';
$file        = 'versions.php';
$title       = 'Version matrix';
$description = 'Which VortaForge build targets which Minecraft version, and the pack format of every archived pack.';
$crumbs      = '<a href="../index.php">Home</a> / <a href="api.php">Docs</a> / Versions';

ob_start();
?>
<p class="crumbs"><a href="../index.php">Home</a> / <a href="api.php">Docs</a> / Versions</p>
<div class="hero">
  <h1>Version matrix</h1>
  <p class="lede">Which build targets which Minecraft version, and which pack
     format each archived pack was written for.</p>
</div>

<div class="banner">
  <strong>Only the current row is supported.</strong> Everything else on this
  page is an archived pack. It is listed so a version question has an answer,
  not so that installing it is a good idea.
</div>

<h2>Current</h2>
<table>
  <thead><tr><th>What</th><th>Minecraft</th><th>Runtime</th><th>Status</th></tr></thead>
  <tbody>
    <tr><td>macroEngine core + 33 modules</td><td>26.3</td>
        <td>Fabric Loader 0.20.1, Java 25, no mixins</td>
        <td><span class="yes">supported</span></td></tr>
    <tr><td>afk-manager</td><td>26.3</td>
        <td>Fabric Loader 0.20.1, Java 25, no Fabric API</td>
        <td><span class="yes">supported</span></td></tr>
    <tr><td>join-throttle</td><td>26.3</td>
        <td>Fabric Loader 0.20.1, Java 25, one mixin</td>
        <td><span class="yes">supported</span></td></tr>
  </tbody>
</table>

<p>Java 25 is not a preference. Mojang's version metadata for 26.3 names major
   version 25, and the runtime refuses an older JVM at startup rather than
   failing somewhere confusing later.</p>

<h2>The 34 module jars</h2>
<p>macroEngine ships as one core jar and 33 module jars - 34 artifacts in total,
   from <code>modules.json</code>. Every module declares
   <code>macroengine-core</code> as a dependency and every one installs alone
   next to the core; that is checked by a test, not assumed. They are Fabric mods
   that also work as plain data packs, at pack format <code>121.0</code>.</p>

<table>
  <thead><tr><th>Artifact</th><th>Pack format</th><th>Installable alone</th></tr></thead>
  <tbody>
    <tr><td>core</td><td>121.0</td><td>yes - it is the base</td></tr>
    <tr><td>each of the 33 modules</td><td>121.0</td>
        <td><span class="yes">yes</span> (verified 34/34)</td></tr>
  </tbody>
</table>

<h2>Archived packs</h2>
<p>A two-part pack format (<code>121.0</code>) is the 26.x format, which carries
   a minor number. Single numbers predate it. A pack whose format is well below
   the one your server expects will not load without being rewritten - the
   version column is what it was written against, not a compatibility claim.</p>

<table>
  <thead><tr><th>Pack</th><th>Minecraft</th><th>Pack format</th><th>Status</th></tr></thead>
  <tbody>
        <tr><td><code>cmdTunnel-datapack</code></td><td>1.19.3-1.19.4</td><td>12</td><td><span class="no">archived</span></td></tr>
        <tr><td><code>DailyBonus</code></td><td>Minecraft 26.x</td><td>[121, 0]</td><td><span class="no">archived</span></td></tr>
        <tr><td><code>guikitfw</code></td><td>Minecraft 26.x</td><td>121</td><td><span class="no">archived</span></td></tr>
        <tr><td><code>InteractionClickDetection</code></td><td>1.20.x</td><td>26</td><td><span class="no">archived</span></td></tr>
        <tr><td><code>LeftClickDetection</code></td><td>1.20.5-1.21.x</td><td>41</td><td><span class="no">archived</span></td></tr>
        <tr><td><code>macroEngine-Datapack-v26.3</code></td><td>26.3</td><td>[121, 0]</td><td><span class="no">archived</span></td></tr>
        <tr><td><code>macroEngine-Resourcepack-v26.3</code></td><td>26.3</td><td>[97, 1]</td><td><span class="no">archived</span></td></tr>
        <tr><td><code>QuickShare</code></td><td>Minecraft 26.x</td><td>[121, 0]</td><td><span class="no">archived</span></td></tr>
        <tr><td><code>RTWrapper</code></td><td>26.3</td><td>[107, 1]</td><td><span class="no">archived</span></td></tr>
        <tr><td><code>RTWrapper-v1.0.1</code></td><td>26.3</td><td>[121, 0]</td><td><span class="no">archived</span></td></tr>
        <tr><td><code>TunnelScript</code></td><td>1.19.2</td><td>10</td><td><span class="no">archived</span></td></tr>
        <tr><td><code>vf_macro</code></td><td>1.16-1.20</td><td>6</td><td><span class="no">archived</span></td></tr>
  </tbody>
</table>

<h2>Reading the pack format</h2>
<table>
  <thead><tr><th>Format</th><th>Minecraft</th></tr></thead>
  <tbody>
    <tr><td><code>6</code></td><td>1.16</td></tr>
    <tr><td><code>10</code></td><td>1.19.2</td></tr>
    <tr><td><code>12</code></td><td>1.19.3 - 1.19.4</td></tr>
    <tr><td><code>26</code></td><td>1.20.x</td></tr>
    <tr><td><code>41</code></td><td>1.20.5 - 1.21.x</td></tr>
    <tr><td><code>97.1</code></td><td>26.3 resource pack (<code>[97, 1]</code>)</td></tr>
    <tr><td><code>107.1</code></td><td>26.3 (nested container)</td></tr>
    <tr><td><code>121.0</code></td><td>26.3 datapack</td></tr>
  </tbody>
</table>

<p>The jump from <code>41</code> to <code>97</code> is not a missing row: the
   format numbering restarted its scheme when Minecraft's versioning changed.
   That is also why 26.x formats carry two parts.</p>

<h2>If a pack does not load</h2>
<p>The usual cause is the pack format, not the content. Change
   <code>pack_format</code> in <code>pack.mcmeta</code> to the number your server
   expects and it will usually load, because the function syntax changed far less
   than the number suggests. It is still an archived pack - loading is not the
   same as working, and <a href="warnings.php">the warning</a> is there for a
   reason.</p>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
