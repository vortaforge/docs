<?php
declare(strict_types=1);

/**
 * Datapacks
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php, so
 * it exists once for all fifteen pages.
 */

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = 'datapacks';
$file        = 'index.php';
$title       = 'Datapacks';
$description = 'The twelve archived VortaForge packs - eleven datapacks and one resource pack: what they were, what they ran on, and the one rebuilt as Fabric mods.';
$crumbs      = '<a href="../index.php">Home</a> / Datapacks';

ob_start();
?>
<p class="crumbs"><a href="../index.php">Home</a> / Datapacks</p>
<div class="hero">
  <h1>Datapacks</h1>
  <p class="lede">Twelve datapacks, all archived. What they were, what they ran on,
     and the one that was rebuilt as Fabric mods.</p>
</div>

<div class="banner">
  <strong>Every pack on this page is archived and disarmed.</strong>
  Nothing here is maintained, supported or recommended for a live server.
  They are kept as readable history. See <a href="../docs/warnings.php">warnings</a>
  for what "archived and disarmed" actually means in a world.
</div>

<h2>Why they were retired</h2>
<p>A datapack was the wrong delivery format for what these did, and the archive
   records the reasons rather than a summary. The same four problems recur in
   every entry:</p>

<div class="grid">
  <div class="card">
    <h3>No type safety</h3>
    <p>Every value crossing a function call, a storage read, a scoreboard
       comparison or a macro expansion is an untyped string. Nothing validates
       it at load time or at run time, so a typo fails silently or, worse,
       succeeds with the wrong meaning.</p>
  </div>
  <div class="card">
    <h3>No enforcement boundary</h3>
    <p>Dynamic command execution runs at <code>function-permission-level</code>
       with no authentication boundary around it. A datapack is plain text that
       the server reads; anything it "gates" can be deleted by editing a file.</p>
  </div>
  <div class="card">
    <h3>No tooling</h3>
    <p>No debugger, no inspection, no error surface. A function that silently
       returns early looks exactly like a function that succeeded.</p>
  </div>
  <div class="card">
    <h3>No warning surface</h3>
    <p>The pack cannot tell a player it is abandoned. That had to be added
       afterwards, as a generated function wired into the load tag.</p>
  </div>
</div>

<h2>All twelve archived packs</h2>
<p>Pack format follows the Minecraft version the pack targeted; a two-part value
   (<code>121.0</code>) is the 26.x format, which carries a minor number.</p>

<table>
  <thead><tr><th>Pack</th><th>Minecraft</th><th>Pack format</th><th>Status</th></tr></thead>
  <tbody>
        <tr><td><a href="../docs/archive.php#cmdtunnel-datapack"><code>cmdTunnel-datapack</code></a></td><td>1.19.3-1.19.4</td><td>12</td><td><span class="no">archived</span></td></tr>
        <tr><td><a href="../docs/archive.php#dailybonus"><code>DailyBonus</code></a></td><td>Minecraft 26.x</td><td>[121, 0]</td><td><span class="no">archived</span></td></tr>
        <tr><td><a href="../docs/archive.php#guikitfw"><code>guikitfw</code></a></td><td>Minecraft 26.x</td><td>121</td><td><span class="no">archived</span></td></tr>
        <tr><td><a href="../docs/archive.php#interaction-click-detection"><code>InteractionClickDetection</code></a></td><td>1.20.x</td><td>26</td><td><span class="no">archived</span></td></tr>
        <tr><td><a href="../docs/archive.php#left-click-detection"><code>LeftClickDetection</code></a></td><td>1.20.5-1.21.x</td><td>41</td><td><span class="no">archived</span></td></tr>
        <tr><td><a href="../docs/archive.php#macroengine-datapack"><code>macroEngine-Datapack-v26.3</code></a></td><td>26.3</td><td>[121, 0]</td><td><span class="no">archived</span></td></tr>
        <tr><td><a href="../docs/archive.php#quickshare"><code>QuickShare</code></a></td><td>Minecraft 26.x</td><td>[121, 0]</td><td><span class="no">archived</span></td></tr>
        <tr><td><a href="../docs/archive.php#rtwrapper"><code>RTWrapper</code></a></td><td>26.3</td><td>[107, 1]</td><td><span class="no">archived</span></td></tr>
        <tr><td><a href="../docs/archive.php#rtwrapper-1-0-1"><code>RTWrapper-v1.0.1</code></a></td><td>26.3</td><td>[121, 0]</td><td><span class="no">archived</span></td></tr>
        <tr><td><a href="../docs/archive.php#macroengine-resourcepack"><code>macroEngine-Resourcepack-v26.3</code></a></td><td>26.3</td><td>[97, 1]</td><td><span class="no">archived</span></td></tr>
        <tr><td><a href="../docs/archive.php#tunnelscript"><code>TunnelScript</code></a></td><td>1.19.2</td><td>10</td><td><span class="no">archived</span></td></tr>
        <tr><td><a href="../docs/archive.php#vf-macro"><code>vf_macro</code></a></td><td>1.16-1.20</td><td>6</td><td><span class="no">archived</span></td></tr>
  </tbody>
</table>

<h2>What each one was</h2>

<h3>macroEngine-Datapack-v26.3</h3>
<p>A command engine: dispatch, deferred queue, state, config, permissions, and
   1,280 functions across the areas listed in the
   <a href="../macroengine/index.php">macroEngine section</a>. This is the only
   pack on this page with a successor - it was split into a core and 33 modules
   and repackaged as Fabric mods, which is what the module list documents.
   The datapack itself is still here, archived, because the mod layout is a
   different project and the original should stay readable.</p>

<h3>RTWrapper v1.0.1 and RTWrapper</h3>
<p>Two entries. <code>RTWrapper-v1.0.1</code> is the datapack at format
   <code>121.0</code>. <code>RTWrapper</code> is the container: a nested datapack
   at format <code>107.1</code> plus a Fabric implementation that was retired.
   Both carry the same 700-function command wrapper surface, and both had the
   gate layer that was removed from the tree in the same change as macroEngine's.</p>

<h3>The five older packs</h3>
<p>TunnelScript (1.19.2, format <code>10</code>),
   cmdTunnel-datapack (1.19.3-1.19.4, format <code>12</code>),
   vf_macro (1.16-1.20, format <code>6</code>),
   InteractionClickDetection (1.20.x, format <code>26</code>) and
   LeftClickDetection (1.20.5-1.21.x, format <code>41</code>).
   These predate the 26.x format entirely, which is why their pack formats are
   single numbers. They will not load on a 26.3 server without being rewritten.</p>

<h3>DailyBonus, QuickShare, guikitfw</h3>
<p>All three target 26.x at format <code>121.0</code>: a once-per-in-game-day
   login reward, a per-player item sharing helper, and a storage-authored GUI
   framework that drives a chest minecart as its runtime. guikitfw is the largest
   of the three at 192 functions.</p>

<h2>If you want to read one</h2>
<p>They are ordinary folder trees under <code>archived/packs/</code> in the
   <a href="https://github.com/vortaforge/core">core repository</a>. Nothing needs
   to be built. To inspect one without running it, read
   <code>data/&lt;namespace&gt;/function/</code>; the load order is whatever the
   <code>#minecraft:load</code> tag names.</p>

<div class="warnbox">
  <strong>Do not install one on a server you care about.</strong>
  Not because they are malicious, but because they are unfinished: they were
  abandoned mid-development, the security model they assumed does not exist, and
  no one has run them against a modern server. The archive entry for each says
  the same thing in more detail.
</div>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
