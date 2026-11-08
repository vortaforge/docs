<?php
declare(strict_types=1);

/**
 * macroEngine
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php, so
 * it exists once for all fifteen pages.
 */

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = 'macroengine';
$file        = 'index.php';
$title       = 'macroEngine';
$description = 'macroEngine: a 34-artifact modular command engine for Minecraft 26.3, with 22 hook slots and no gates.';
$crumbs      = '<a href="../index.php">Home</a> / macroEngine';

ob_start();
?>
<p class="crumbs"><a href="../index.php">Home</a> / macroEngine</p>
<div class="hero">
  <h1>macroEngine</h1>
  <p class="lede">A command engine for Minecraft 26.3, split into one core pack
     and 33 modules. Install all of it, or install the parts you want - the
     engine does not notice which.</p>
</div>

<div class="grid">
  <div class="card">
    <h3>Numbers</h3>
    <ul>
      <li><strong>34</strong> artifacts: 1 core + 33 modules</li>
      <li><strong>1,529</strong> functions</li>
      <li><strong>22</strong> hook slots</li>
      <li><strong>0</strong> gates</li>
    </ul>
  </div>
  <div class="card">
    <h3>Runtime</h3>
    <ul>
      <li>Minecraft <strong>26.3</strong></li>
      <li>Fabric Loader <strong>0.20.1</strong></li>
      <li>Java <strong>25</strong></li>
      <li>Pack format <strong>121.0</strong></li>
    </ul>
  </div>
</div>

<h2>What it is</h2>
<p>macroEngine gives a server a command layer: conditional commands, loops,
   variables, permissions, macros, world edits, text output and a
   deferred-command queue, all built out of functions that Minecraft itself
   runs. There is no plugin, no server software fork and no client mod in the
   path - it is a datapack that happens to ship as Fabric mods.</p>

<p>The distinction matters when something goes wrong. A datapack failing is a
   refused <code>function</code> call in the log with the exact id that did not
   resolve. A plugin failing is a stack trace through four layers of an API. The
   first one can be debugged in a text editor.</p>

<h2>The one rule the layout rests on</h2>
<div class="note">
  <strong>A pack cannot call another pack.</strong> No import, no namespace
  visibility, no dependency resolution. So the core owns every seam - a function
  tag - and modules add themselves to a seam instead of calling each other.
</div>
<p>Everything else is downstream of that sentence, including why removing a
   module works and why no module knows what its siblings are called. The long
   version is on <a href="architecture.php">Architecture</a>.</p>

<h2>Install</h2>
<ol>
  <li>Install Fabric Loader 0.20.1 for Minecraft 26.3, with a Java 25
      runtime. A 21 runtime will refuse to start.</li>
  <li>Copy the jars you want into <code>mods/</code>.
      <strong><code>macroengine-core</code> is mandatory</strong>; every other
      jar is optional and each declares the core as a dependency.</li>
  <li>Start the server. No config file is required for the core.</li>
  <li>Check the log for <code>macroEngine</code> and the module count you
      installed. A module whose dependency is missing is refused by Fabric at
      startup rather than loading and failing later.</li>
</ol>

<h3>What to install first</h3>
<p>The core alone is a working engine with nothing to do. The modules that make
   it feel like an engine - by function count, because more functions means more
   command surface:</p>
<ul>
<li><code>macroengine-core</code> &mdash; 911 functions</li>
<li><code>macroengine-cmd</code> &mdash; 123 functions</li>
<li><code>macroengine-player</code> &mdash; 58 functions</li>
<li><code>macroengine-lib</code> &mdash; 53 functions</li>
<li><code>macroengine-input</code> &mdash; 41 functions</li>
<li><code>macroengine-hook</code> &mdash; 40 functions</li>
</ul>

<p>The smallest modules are the narrowest:</p>
<ul>
<li><code>macroengine-diagnostics</code> &mdash; 2 functions</li>
<li><code>macroengine-gamerule</code> &mdash; 3 functions</li>
<li><code>macroengine-sound</code> &mdash; 3 functions</li>
<li><code>macroengine-tui</code> &mdash; 4 functions</li>
</ul>

<h2>Or use it as a data pack</h2>
<p>Each jar bundles <code>pack.mcmeta</code> and <code>data/</code> at its root,
   so the same content works as a plain datapack in
   <code>world/datapacks/</code>. What you lose is Fabric's dependency check: a
   datapack has no <code>depends</code>, so an orphan module loads and its calls
   vanish silently instead of being refused at startup.</p>

<p>For a single-player world that is acceptable. For a server, install the mods
   - the refusal at startup is worth having.</p>

<h2>What is inside a jar</h2>
<pre><code>macroengine-wand.jar
├── fabric.mod.json                 ← what makes it a mod
├── pack.mcmeta                     ← what makes it a datapack
└── data/
    ├── me_wand_module/
    │   └── function/wand/...       ← the module's public surface
    └── me_core_module/
        └── tags/function/hook/
            └── wand_tick.json      ← its claim on a seam</code></pre>

<p>That last file is the whole integration. It is not code that runs; it is a
   declaration that this module should be in the
   <code>wand_tick</code> tag. The core never learns the module's name.</p>

<h2>Where to go next</h2>
<table>
  <thead><tr><th>Read</th><th>For</th></tr></thead>
  <tbody>
    <tr><td><a href="architecture.php">Architecture</a></td>
        <td>The 22 slots, what the core owns, why removal works</td></tr>
    <tr><td><a href="modules.php">Modules</a></td>
        <td>All 33, their mod ids and what each one is for</td></tr>
    <tr><td><a href="reference.php">Command reference</a></td>
        <td>Every command, grouped by module</td></tr>
    <tr><td><a href="../docs/api.php">API</a></td>
        <td>The contract for writing a new module</td></tr>
    <tr><td><a href="../docs/versions.php">Versions</a></td>
        <td>Pack formats and what each Minecraft release expects</td></tr>
  </tbody>
</table>

<h2>What it does not do</h2>
<ul>
  <li><strong>No client-side anything.</strong> <code>environment: "*"</code> is
      set so the pack loads in single-player, not because there is a HUD.</li>
  <li><strong>No persistence beyond scoreboards.</strong> State lives in
      scoreboard objectives, which means it lives in the world save and nowhere
      else.</li>
  <li><strong>No config GUI.</strong> Configuration is a
      <code>function</code> call.</li>
  <li><strong>No cross-version support.</strong> One Minecraft release at a
      time.</li>
</ul>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
