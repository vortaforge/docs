<?php
declare(strict_types=1);

/**
 * Module list
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php, so
 * it exists once for all fifteen pages.
 */

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = 'macroengine';
$file        = 'modules.php';
$title       = 'Module list';
$description = 'All 34 macroEngine artifacts: mod id, function count and what each module is for.';
$crumbs      = '<a href="../index.php">Home</a> / <a href="index.php">macroEngine</a> / Modules';

ob_start();
?>
<p class="crumbs"><a href="../index.php">Home</a> / <a href="index.php">macroEngine</a> / Modules</p>
<div class="hero">
  <h1>Module list</h1>
  <p class="lede">All 34 artifacts - one core and 33 modules. Install the core, then whichever of the rest you need.</p>
</div>
<div class="note">
    <strong>Reading this table.</strong> <em>Functions</em> counts the
    <code>.mcfunction</code> files the pack ships, including its internals.
    A module&rsquo;s callable surface is smaller than its file count; the public ids are
    in the <a href="reference.php">function reference</a>.
  </div>

  <h2>All packs</h2>
  <table>
    <thead><tr><th>Pack</th><th>Mod id</th><th>Functions</th><th>What it is for</th></tr></thead>
    <tbody>
      <tr><td><code>me_core_module</code> <span class="no">core</span><br><span class="sub" style="font-size:13px">Engine kernel</span></td><td><code>macroengine-core</code></td><td>771</td><td>Dispatch, the deferred queue, state, config, gates, load and tick, plus the kernel services the engine calls at boot &mdash; <code>math</code>, <code>rng</code>, <code>log</code>, <code>string</code>, <code>color</code>, <code>validate</code>. Also owns every extension point: the hook slots and the <code>events/on_*</code> tags.</td></tr>
      <tr><td><code>me_cmd_module</code><br><span class="sub" style="font-size:13px">Commands</span></td><td><code>macroengine-cmd</code></td><td>124</td><td>The largest module: every command the engine registers, from <code>freeze</code> to <code>effect_give</code>, plus the multi-command condition and loop machinery behind them.</td></tr>
      <tr><td><code>me_player_module</code><br><span class="sub" style="font-size:13px">Player state</span></td><td><code>macroengine-player</code></td><td>58</td><td>Per-player variables, health and armour reads, gamemode, position, XP, and the name/pid resolution the rest of the engine uses.</td></tr>
      <tr><td><code>me_store_module</code><br><span class="sub" style="font-size:13px">Storage helpers</span></td><td><code>macroengine-store</code></td><td>33</td><td><code>get</code>/<code>set</code>/<code>has</code>/<code>add</code> over NBT storage paths, with defaults and conversions.</td></tr>
      <tr><td><code>me_input_module</code><br><span class="sub" style="font-size:13px">Input capture</span></td><td><code>macroengine-input</code></td><td>31</td><td>Anvil, name tag, writable book and command-block-minecart input, turned into a value the engine can read.</td></tr>
      <tr><td><code>me_world_module</code><br><span class="sub" style="font-size:13px">World queries</span></td><td><code>macroengine-world</code></td><td>27</td><td>Block tests, entity counts and lookups, clone and teleport helpers, region and sphere membership.</td></tr>
      <tr><td><code>me_hook_module</code><br><span class="sub" style="font-size:13px">Hook surface</span></td><td><code>macroengine-hook</code></td><td>26</td><td>Binds engine events &mdash; block break, advancement, fishing, trade, dimension change &mdash; to a handler you register.</td></tr>
      <tr><td><code>me_toggle_module</code><br><span class="sub" style="font-size:13px">Toggles</span></td><td><code>macroengine-toggle</code></td><td>25</td><td>Per-feature on/off switches backed by scoreboard flags, with a default state and an enforcement path.</td></tr>
      <tr><td><code>me_perm_module</code><br><span class="sub" style="font-size:13px">Permissions</span></td><td><code>macroengine-perm</code></td><td>20</td><td>Grants, groups, checks and command-level enforcement. Off by config, in which case everyone passes.</td></tr>
      <tr><td><code>me_title_module</code><br><span class="sub" style="font-size:13px">Titles and actionbars</span></td><td><code>macroengine-title</code></td><td>16</td><td>Send titles, subtitles and actionbars to one player, an area, a team, a tag, or everyone.</td></tr>
      <tr><td><code>me_events_module</code><br><span class="sub" style="font-size:13px">Event bus</span></td><td><code>macroengine-events</code></td><td>13</td><td>Register, fire, list and unregister events, including deferred and per-player firing. Core drains its queue into this module through a hook slot.</td></tr>
      <tr><td><code>me_flag_module</code><br><span class="sub" style="font-size:13px">Flags</span></td><td><code>macroengine-flag</code></td><td>13</td><td>Named boolean and value flags in engine storage: read, write, copy, list, clear.</td></tr>
      <tr><td><code>me_wand_module</code><br><span class="sub" style="font-size:13px">Wand items</span></td><td><code>macroengine-wand</code></td><td>12</td><td>The engine&rsquo;s selection wand: give it, attach data and lore, and check its cooldown.</td></tr>
      <tr><td><code>me_color_module</code><br><span class="sub" style="font-size:13px">Colour</span></td><td><code>macroengine-color</code></td><td>10</td><td>Palettes, gradients, lerp and score-to-colour conversion for text output.</td></tr>
      <tr><td><code>me_logic_module</code><br><span class="sub" style="font-size:13px">Conditions</span></td><td><code>macroengine-logic</code></td><td>10</td><td>Comparison and boolean combinators &mdash; <code>if_eq</code>, <code>if_gt</code>, <code>if_and</code> and friends &mdash; as callable functions.</td></tr>
      <tr><td><code>me_geo_module</code><br><span class="sub" style="font-size:13px">Geometry</span></td><td><code>macroengine-geo</code></td><td>9</td><td>Region and sphere membership, nearby-entity and nearby-player tests. Not reachable from core: this module is only what you call.</td></tr>
      <tr><td><code>me_container_module</code><br><span class="sub" style="font-size:13px">Containers</span></td><td><code>macroengine-container</code></td><td>9</td><td>Slot reads and clears, empty/full tests, and container info for the block you are looking at.</td></tr>
      <tr><td><code>me_cb_module</code><br><span class="sub" style="font-size:13px">Command blocks</span></td><td><code>macroengine-cb</code></td><td>9</td><td>Run a command string through a temporary command block, immediately, next tick, or on a delay &mdash; with a cancellable queue.</td></tr>
      <tr><td><code>me_block_module</code><br><span class="sub" style="font-size:13px">Block registry</span></td><td><code>macroengine-block</code></td><td>8</td><td>Register blocks the engine knows about, insert and take items from them, and a hopper in/out bridge.</td></tr>
      <tr><td><code>me_interaction_module</code><br><span class="sub" style="font-size:13px">Interactions</span></td><td><code>macroengine-interaction</code></td><td>7</td><td>Bind and unbind right- and left-click handlers on an entity or block, then spawn and manage them.</td></tr>
      <tr><td><code>me_trigger_module</code><br><span class="sub" style="font-size:13px">Triggers</span></td><td><code>macroengine-trigger</code></td><td>7</td><td>Bind a command to a trigger, enable, disable, list and unbind, with a per-tick dispatch path.</td></tr>
      <tr><td><code>me_rate_limit_module</code><br><span class="sub" style="font-size:13px">Rate limiting</span></td><td><code>macroengine-rate-limit</code></td><td>7</td><td>Global, per-player and per-channel throttles with independent configuration.</td></tr>
      <tr><td><code>me_item_module</code><br><span class="sub" style="font-size:13px">Item helpers</span></td><td><code>macroengine-item</code></td><td>6</td><td>Owner tags, hidden flags, durability overlays and per-item use cooldowns.</td></tr>
      <tr><td><code>me_text_module</code><br><span class="sub" style="font-size:13px">Text presentation</span></td><td><code>macroengine-text</code></td><td>5</td><td>Render a value as a chat component, show it to a player or a target, label it, explain it. A presentation layer over core&rsquo;s string library.</td></tr>
      <tr><td><code>me_macro_module</code><br><span class="sub" style="font-size:13px">Macro dispatch</span></td><td><code>macroengine-macro</code></td><td>5</td><td>Call a function with arguments built from a score, a tag, a predicate, an advancement or another function.</td></tr>
      <tr><td><code>me_tui_module</code><br><span class="sub" style="font-size:13px">Text UI</span></td><td><code>macroengine-tui</code></td><td>4</td><td>Header, divider, line and button primitives for building clickable text interfaces.</td></tr>
      <tr><td><code>me_gamerule_module</code><br><span class="sub" style="font-size:13px">Gamerules</span></td><td><code>macroengine-gamerule</code></td><td>3</td><td>Read, set and reset the gamerules the engine exposes.</td></tr>
      <tr><td><code>me_sound_module</code><br><span class="sub" style="font-size:13px">Sound</span></td><td><code>macroengine-sound</code></td><td>3</td><td>Play a sound at the executor, at a position, or stop one.</td></tr>
      <tr><td><code>me_diagnostics_module</code><br><span class="sub" style="font-size:13px">Diagnostics</span></td><td><code>macroengine-diagnostics</code></td><td>2</td><td>A status line and a full report of engine state.</td></tr>
    </tbody>
  </table>

  <p class="sub">1,529 <code>.mcfunction</code> files across the 34 artifacts - 911 of them in
     <code>me_core_module</code> alone. A module's public surface is the subset its
     hook tag names; the rest is implementation, and it lives in core.</p>

  <h2>Which ones you actually need</h2>
  <p><code>me_core_module</code> is the only required pack. Everything else is
     optional and independent: installing <code>me_cb_module</code> without
     <code>me_events_module</code> is a valid install, and so is the reverse.</p>

  <p>The two that are easiest to assume are required, and are not:</p>
  <table>
    <thead><tr><th>Pack</th><th>If you leave it out</th></tr></thead>
    <tbody>
      <tr><td><code>me_events_module</code></td>
          <td>Core&rsquo;s deferred queue keeps filling and nothing drains it. Hooks that
              fire engine events still fire their own handlers; the bus is what you
              lose.</td></tr>
      <tr><td><code>me_text_module</code></td><td>Nothing. Its five functions are not
          called from anywhere in the engine.</td></tr>
    </tbody>
  </table>

  <div class="note">
    <strong>Why <code>math</code>, <code>rng</code>, <code>log</code>,
    <code>string</code>, <code>color</code> and <code>validate</code> are not
    modules.</strong> They were candidates. Each is called by the core at boot or on
    tick, so making them modules would make the core depend on a module &mdash; the one
    thing the layout forbids. A pack the engine cannot boot without is part of the
    engine. They live in <code>me_core_module</code>.
  </div>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
