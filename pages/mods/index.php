<?php
declare(strict_types=1);

/**
 * Mods
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php, so
 * it exists once for all fifteen pages.
 */

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = 'mods';
$file        = 'index.php';
$title       = 'Mods';
$description = 'The two VortaForge Fabric mods for Minecraft 26.3: afk-manager and join-throttle.';
$crumbs      = '<a href="../index.php">Home</a> / Mods';

ob_start();
?>
<p class="crumbs"><a href="../index.php">Home</a> / Mods</p>
<div class="hero">
  <h1>Mods</h1>
  <p class="lede">Two Fabric mods, both for Minecraft 26.3, both small enough to
     read in an afternoon. Neither needs Fabric API.</p>
</div>

<div class="grid">
  <div class="card">
    <h3><a href="afk-manager.php">afk-manager</a></h3>
    <p>Marks players AFK after a period without movement, announces it, and
       optionally kicks them.</p>
    <p><span class="tag">5 settings</span> <span class="tag">/afk</span>
       <span class="tag">no Fabric API</span></p>
  </div>
  <div class="card">
    <h3><a href="join-throttle.php">join-throttle</a></h3>
    <p>Rate-limits connection attempts per address: five in thirty seconds by
       default, counted before login finishes.</p>
    <p><span class="tag">3 settings</span> <span class="tag">no commands</span>
       <span class="tag">no Fabric API</span></p>
  </div>
</div>

<h2>At a glance</h2>
<table>
  <thead><tr><th></th><th>afk-manager</th><th>join-throttle</th></tr></thead>
  <tbody>
    <tr><td>Mod id</td><td><code>afk-manager</code></td><td><code>join-throttle</code></td></tr>
    <tr><td>Requires</td><td>Fabric Loader</td><td>Fabric Loader</td></tr>
    <tr><td>Fabric API</td><td>not needed</td><td>not needed</td></tr>
    <tr><td>Java</td><td>25</td><td>25</td></tr>
    <tr><td>Minecraft</td><td>26.3</td><td>26.3</td></tr>
    <tr><td>Config file</td><td><code>config/afk-manager.json</code></td>
        <td><code>config/join-throttle.json</code></td></tr>
    <tr><td>Commands</td><td><code>/afk</code></td><td>none</td></tr>
    <tr><td>Works client-side</td><td><span class="no">no</span> - server only</td>
        <td><span class="no">no</span> - server only</td></tr>
    <tr><td>Mixins</td><td>none</td><td>one (player manager)</td></tr>
  </tbody>
</table>

<h2>Installing either mod</h2>
<ol>
  <li>Install Fabric Loader for Minecraft 26.3. Java 25 is required, not 21 -
      Mojang's version metadata for 26.3 names 25, and a 21 runtime will not
      start.</li>
  <li>Put the jar in <code>mods/</code>, next to the loader.</li>
  <li>Start the server once. The config file appears under <code>config/</code>
      with the defaults filled in.</li>
  <li>Edit and restart. Neither mod reloads its config live; a restart is the
      only way to apply a change.</li>
</ol>

<div class="note">
  <strong>Fabric API is not required.</strong> Both mods are deliberately
  self-contained. If you already run Fabric API for something else, that is fine
  - it just is not a dependency, and removing it from <code>mods/</code> will not
  break either one.
</div>

<h2>Reading a settings file</h2>
<p>Both files are flat JSON: one object, no nesting, keys as shown on each mod's
   page. Parsing rules that apply to both:</p>
<ul>
  <li><strong>A missing key gets its default.</strong> Deleting a line is a valid
      way to restore one value.</li>
  <li><strong>An out-of-range value is clamped, not rejected.</strong> The server
      starts, and the effective value is what the clamp produced. Each page lists
      the clamp.</li>
  <li><strong>A malformed file is not silently ignored.</strong> If the JSON does
      not parse, the mod says so in the log rather than quietly running on
      defaults - a typo should be visible.</li>
  <li><strong>Nothing is written back.</strong> The mod reads the file; it does
      not rewrite it with your values clamped, so what you wrote stays what you
      wrote.</li>
</ul>

<h2>Neither mod is a security boundary</h2>
<p>join-throttle slows a reconnection loop; it does not stop a distributed one.
   afk-manager frees a slot; it does not authenticate anyone. Both are
   convenience for a server you already trust, and neither should be the reason
   an untrusted client is allowed in.</p>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
