<?php
declare(strict_types=1);

/**
 * Architecture
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php, so
 * it exists once for all fifteen pages.
 */

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = 'macroengine';
$file        = 'architecture.php';
$title       = 'Architecture';
$description = 'How macroEngine keeps 33 module packs independent: the 22 hook slots, what the core owns, and why removal works.';
$crumbs      = '<a href="../index.php">Home</a> / <a href="index.php">macroEngine</a> / Architecture';

ob_start();
?>
<p class="crumbs"><a href="../index.php">Home</a> / <a href="index.php">macroEngine</a> / Architecture</p>
<div class="hero">
  <h1>Architecture</h1>
  <p class="lede">One core pack, 33 module packs, 22 hook slots and no module
     that knows another module exists. The last part is the design; everything
     else follows from it.</p>
</div>

<h2>The shape</h2>
<p>A datapack cannot call another datapack. There is no import, no dependency
   resolution and no namespace that can see across a pack boundary. So a
   modular engine cannot be built out of direct calls - the moment
   <code>me_player_module</code> calls <code>me_perm_module</code>, the two stop
   being independently installable.</p>

<p>macroEngine solves this the only way a datapack can: <strong>the core owns
   every seam, and modules only ever touch a seam.</strong> A seam is a
   <code>#tag</code> function tag. Modules add themselves to a tag; the core
   runs the tag. Neither side names the other.</p>

<pre><code>core tick
  └─ function #me_core_module:hook/wand_tick   ← the seam (a tag, note the #)
       └─ me_wand_module:core/internal/wand/tick ← the module's entry</code></pre>

<p>The module does not edit that tag. It ships a second file with the same tag
   id in its own pack, marked <code>replace: false</code>, and the game unions
   the two lists. Neither side names the other; both just declare the same tag.</p>

<p>An empty tag is a valid tick, and every contributed value is marked
   <code>required: false</code>. That is what makes a module removable: take the
   jar out and the tag has one fewer entry, no load error, no dangling
   reference. No module has to be told a sibling is gone, because no module was
   ever told a sibling existed.</p>

<h2>The 22 hook slots</h2>
<p>These are the entire public surface of the engine - 22 slots, each
   one a function tag owned by the core. A module that needs to run code at a
   particular moment adds itself to one of these and nothing else. The right
   column shows which modules currently attach to each slot; empty slots are not
   defects, they are extension points nobody has needed yet.</p>

<table>
  <thead><tr><th>Slot</th><th>Tag</th><th>Provided by</th></tr></thead>
  <tbody>
    <tr><td><code>cb_tick</code></td><td><code>me_core_module:hook/cb_tick</code></td><td><code>cb</code></td></tr>
    <tr><td><code>cmd_freeze_tick</code></td><td><code>me_core_module:hook/cmd_freeze_tick</code></td><td><code>cmd</code></td></tr>
    <tr><td><code>config_apply</code></td><td><code>me_core_module:hook/config_apply</code></td><td><code>config</code></td></tr>
    <tr><td><code>config_ensure</code></td><td><code>me_core_module:hook/config_ensure</code></td><td><code>config</code></td></tr>
    <tr><td><code>config_init</code></td><td><code>me_core_module:hook/config_init</code></td><td><code>config</code></td></tr>
    <tr><td><code>events_fire</code></td><td><code>me_core_module:hook/events_fire</code></td><td><code>events</code></td></tr>
    <tr><td><code>hook_tick</code></td><td><code>me_core_module:hook/hook_tick</code></td><td><code>hook</code></td></tr>
    <tr><td><code>interaction_tick</code></td><td><code>me_core_module:hook/interaction_tick</code></td><td><code>interaction</code></td></tr>
    <tr><td><code>load</code></td><td><code>me_core_module:hook/load</code></td><td><span class="no">nothing</span></td></tr>
    <tr><td><code>perm_require</code></td><td><code>me_core_module:hook/perm_require</code></td><td><code>perm</code></td></tr>
    <tr><td><code>perm_tick</code></td><td><code>me_core_module:hook/perm_tick</code></td><td><code>perm</code></td></tr>
    <tr><td><code>player_enumerate</code></td><td><code>me_core_module:hook/player_enumerate</code></td><td><code>player</code></td></tr>
    <tr><td><code>player_get_name</code></td><td><code>me_core_module:hook/player_get_name</code></td><td><code>player</code></td></tr>
    <tr><td><code>player_init</code></td><td><code>me_core_module:hook/player_init</code></td><td><code>player</code></td></tr>
    <tr><td><code>player_init_from_name</code></td><td><code>me_core_module:hook/player_init_from_name</code></td><td><code>player</code></td></tr>
    <tr><td><code>player_init_online</code></td><td><code>me_core_module:hook/player_init_online</code></td><td><code>player</code></td></tr>
    <tr><td><code>player_resolve</code></td><td><code>me_core_module:hook/player_resolve</code></td><td><code>player</code></td></tr>
    <tr><td><code>player_tick</code></td><td><code>me_core_module:hook/player_tick</code></td><td><span class="no">nothing</span></td></tr>
    <tr><td><code>queue_process</code></td><td><code>me_core_module:hook/queue_process</code></td><td><code>lib</code></td></tr>
    <tr><td><code>tick</code></td><td><code>me_core_module:hook/tick</code></td><td><span class="no">nothing</span></td></tr>
    <tr><td><code>trigger_dispatch</code></td><td><code>me_core_module:hook/trigger_dispatch</code></td><td><code>trigger</code></td></tr>
    <tr><td><code>wand_tick</code></td><td><code>me_core_module:hook/wand_tick</code></td><td><code>wand</code></td></tr>
  </tbody>
</table>

<h2>Two kinds of slot</h2>
<p>Twenty of the 22 slots are fire-and-forget: the core runs the tag and does not
   read anything back. Those are cheap to extend, because a module can add
   itself without agreeing with anyone about anything.</p>

<p>Two slots return a value - <code>perm_require</code> and
   <code>player_get_name</code> - and those are the only places where two
   modules must agree on a contract. A query slot answers into a scoreboard
   holder that the caller then reads, so the holder name, the encoding and the
   "no answer" representation are all part of the seam.</p>

<table>
  <thead><tr><th>Kind</th><th>Count</th><th>Cost to extend</th><th>Slots</th></tr></thead>
  <tbody>
    <tr><td>Side-effect</td><td>20</td>
        <td>Add yourself to the tag. Nothing else.</td>
        <td>all the rest</td></tr>
    <tr><td>Query (returns a value)</td><td>2</td>
        <td>Add yourself <em>and</em> honour the scoreboard contract the caller
            reads.</td>
        <td><code>perm_require</code>, <code>player_get_name</code></td></tr>
  </tbody>
</table>

<p>Keeping that ratio at 20:2 is the reason the module set is extensible. Every
   new query slot is a new contract that every future module must be written
   against; a new side-effect slot costs nothing.</p>

<h2>Where the files are</h2>
<p>1,529 functions across 34 packs. The distribution is lopsided on purpose: the
   core carries the dispatcher, the queue, state, config and the kernel services,
   so it is expected to be the largest pack by a wide margin. A module that
   grows past the core is a sign the split is wrong.</p>

<table>
  <thead><tr><th>Module</th><th>Mod id</th><th>Functions</th></tr></thead>
  <tbody>
    <tr><td><code>core</code></td><td><code>macroengine-core</code></td><td>911</td></tr>
    <tr><td><code>cmd</code></td><td><code>macroengine-cmd</code></td><td>123</td></tr>
    <tr><td><code>player</code></td><td><code>macroengine-player</code></td><td>58</td></tr>
    <tr><td><code>lib</code></td><td><code>macroengine-lib</code></td><td>53</td></tr>
    <tr><td><code>input</code></td><td><code>macroengine-input</code></td><td>41</td></tr>
    <tr><td><code>hook</code></td><td><code>macroengine-hook</code></td><td>40</td></tr>
    <tr><td><code>store</code></td><td><code>macroengine-store</code></td><td>32</td></tr>
    <tr><td><code>world</code></td><td><code>macroengine-world</code></td><td>27</td></tr>
    <tr><td><code>toggle</code></td><td><code>macroengine-toggle</code></td><td>25</td></tr>
    <tr><td><code>perm</code></td><td><code>macroengine-perm</code></td><td>20</td></tr>
  </tbody>
</table>

<p>Every module declares <code>me_core_module</code> as a hard requirement; the
   core declares nothing. There is no second-level dependency in the graph -
   <code>me_perm_module</code> does not require <code>me_player_module</code>
   even though it operates on players. It reaches the player API through a hook,
   which is the same mechanism it would use if the player module were not
   installed at all.</p>

<h2>What the core owns</h2>
<ul>
  <li><strong>Dispatch</strong> - the entry point every command and tick passes
      through.</li>
  <li><strong>The deferred queue</strong> - work that must happen after the
      current function returns, since a function cannot re-enter itself.</li>
  <li><strong>State and config</strong> - the scoreboard-backed storage every
      module reads and writes.</li>
  <li><strong>Load and tick</strong> - the two function tags Minecraft itself
      calls, wired to the slots above.</li>
  <li><strong>Kernel services</strong> - <code>math</code>, <code>rng</code>,
      <code>log</code>, <code>string</code>, <code>color</code>,
      <code>validate</code>. Small, stateless, and duplicated nowhere.</li>
  <li><strong>Every extension point</strong> - the 22 slots. A module may not
      invent one.</li>
</ul>

<h2>What a module owns</h2>
<p>Its own namespace, its own functions, its own config keys, and its own
   membership in the tags it needs. Nothing else. A module may not:</p>
<ul>
  <li>define a hook slot (<code>tools/test_module_removal.py</code> enforces
      it),</li>
  <li>ship a <code>core/internal/</code> tree,</li>
  <li>call another module's functions - the namespace is not visible across
      packs anyway, so this fails at build rather than at runtime,</li>
  <li>assume a sibling is installed. If <code>me_player_module</code> is absent,
      <code>me_perm_module</code> still loads and simply has no players to
      resolve.</li>
</ul>

<h2>Why this survives removal</h2>
<p>The test is not "does it load without the module". It is "does the engine
   still tick when the module was never there". The difference matters: a
   missing tag entry is invisible, while a missing function call is a silent
   no-op that Minecraft reports as an error in the log and otherwise ignores.</p>

<p>All 34 packs pass the removal test individually. That is 34 builds, each with
   exactly one pack absent, checking that load and tick still complete.</p>

<div class="note">
  <strong>The rule that keeps it honest:</strong> feature code never lands in
  core. Core grows only when a new seam is genuinely needed by more than one
  module - and a seam added for one module is a sign that module should own the
  work instead.
</div>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
