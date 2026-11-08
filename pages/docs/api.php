<?php
declare(strict_types=1);

/**
 * API
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php, so
 * it exists once for all fifteen pages.
 */

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = 'docs';
$file        = 'api.php';
$title       = 'API';
$description = 'macroEngine&#x27;s core contract: the 22 hook slots, how a module joins one, published services and what verify.py checks.';
$crumbs      = '<a href="../index.php">Home</a> / <a href="../macroengine/index.php">macroEngine</a> / API';

ob_start();
?>
<p class="crumbs"><a href="../index.php">Home</a> / <a href="../macroengine/index.php">macroEngine</a> / API</p>
<div class="hero">
  <h1>API</h1>
  <p class="lede">What the core promises a module, and what it expects back.
     Anything not on this page is not guaranteed - and is not reachable anyway,
     because a pack cannot see across a pack boundary.</p>
</div>

<div class="toc">
  <strong>On this page</strong>
  <ol>
    <li><a href="#rule">The one rule</a></li>
    <li><a href="#joining">Joining a hook</a></li>
    <li><a href="#slots">The 22 slots</a></li>
    <li><a href="#values">Slots that carry a value</a></li>
    <li><a href="#services">Services the core publishes</a></li>
    <li><a href="#internal">Where implementations live</a></li>
    <li><a href="#ids">Function ids and naming</a></li>
    <li><a href="#checks">What gets checked</a></li>
    <li><a href="#manifest">The Fabric manifest</a></li>
  </ol>
</div>

<h2 id="rule">The one rule</h2>
<p>The core never calls a module by name. When it needs a module's work it runs
   a <em>tag</em>:</p>

<pre><code>function #me_core_module:hook/trigger_dispatch</code></pre>

<p>Note the <code>#</code>. That is not a function, it is a tag, and the core
   does not know who is inside it. Everything else in this document exists to
   keep that sentence true.</p>

<h2 id="joining">Joining a hook</h2>
<p>You do not edit the core's tag file. You ship a file with the same tag id in
   your own pack:</p>

<pre><code>// me_trigger_module/data/me_core_module/tags/function/hook/trigger_dispatch.json
{
  "replace": false,
  "values": [
    { "id": "me_trigger_module:core/internal/trigger/dispatch", "required": false }
  ]
}</code></pre>

<p>Both files declare the same tag id. Because <code>replace: false</code> the
   game unions them - the core's list plus yours - so the core's call runs your
   function without either side naming the other.</p>

<p><code>required: false</code> is not cosmetic. It tells the game that this
   value may be absent. Remove the module and the tag still loads; the feature
   simply stops. With <code>required: true</code> a missing value is a load
   error, and the pack that was supposed to be removable takes the world with
   it.</p>

<div class="warnbox">
  <strong>Ship a new tag file, never an edited one.</strong> The path
  <code>data/me_core_module/tags/function/hook/&lt;slot&gt;.json</code> lives in
  your pack, but it must not <em>replace</em> the core's list. Two packs that
  both set <code>replace: true</code> on the same slot leave exactly one of them
  working, and neither of them knows it.
</div>

<h2 id="slots">The 22 slots</h2>
<table>
  <thead><tr><th>Slot</th><th>Carries a value</th><th>Tag</th></tr></thead>
  <tbody>
    <tr><td><code>cb_tick</code></td><td>side effect</td><td><code>me_core_module:hook/cb_tick</code></td></tr>
    <tr><td><code>cmd_freeze_tick</code></td><td>side effect</td><td><code>me_core_module:hook/cmd_freeze_tick</code></td></tr>
    <tr><td><code>config_apply</code></td><td>side effect</td><td><code>me_core_module:hook/config_apply</code></td></tr>
    <tr><td><code>config_ensure</code></td><td>side effect</td><td><code>me_core_module:hook/config_ensure</code></td></tr>
    <tr><td><code>config_init</code></td><td>side effect</td><td><code>me_core_module:hook/config_init</code></td></tr>
    <tr><td><code>events_fire</code></td><td>side effect</td><td><code>me_core_module:hook/events_fire</code></td></tr>
    <tr><td><code>hook_tick</code></td><td>side effect</td><td><code>me_core_module:hook/hook_tick</code></td></tr>
    <tr><td><code>interaction_tick</code></td><td>side effect</td><td><code>me_core_module:hook/interaction_tick</code></td></tr>
    <tr><td><code>load</code></td><td>side effect</td><td><code>me_core_module:hook/load</code></td></tr>
    <tr><td><code>perm_require</code></td><td><span class=yes>value</span></td><td><code>me_core_module:hook/perm_require</code></td></tr>
    <tr><td><code>perm_tick</code></td><td>side effect</td><td><code>me_core_module:hook/perm_tick</code></td></tr>
    <tr><td><code>player_enumerate</code></td><td>side effect</td><td><code>me_core_module:hook/player_enumerate</code></td></tr>
    <tr><td><code>player_get_name</code></td><td><span class=yes>value</span></td><td><code>me_core_module:hook/player_get_name</code></td></tr>
    <tr><td><code>player_init</code></td><td>side effect</td><td><code>me_core_module:hook/player_init</code></td></tr>
    <tr><td><code>player_init_from_name</code></td><td>side effect</td><td><code>me_core_module:hook/player_init_from_name</code></td></tr>
    <tr><td><code>player_init_online</code></td><td>side effect</td><td><code>me_core_module:hook/player_init_online</code></td></tr>
    <tr><td><code>player_resolve</code></td><td>side effect</td><td><code>me_core_module:hook/player_resolve</code></td></tr>
    <tr><td><code>player_tick</code></td><td>side effect</td><td><code>me_core_module:hook/player_tick</code></td></tr>
    <tr><td><code>queue_process</code></td><td>side effect</td><td><code>me_core_module:hook/queue_process</code></td></tr>
    <tr><td><code>tick</code></td><td>side effect</td><td><code>me_core_module:hook/tick</code></td></tr>
    <tr><td><code>trigger_dispatch</code></td><td>side effect</td><td><code>me_core_module:hook/trigger_dispatch</code></td></tr>
    <tr><td><code>wand_tick</code></td><td>side effect</td><td><code>me_core_module:hook/wand_tick</code></td></tr>
  </tbody>
</table>

<h2 id="values">Slots that carry a value</h2>
<p>A tag can run several functions, so there is no single return value. What the
   game does instead:</p>
<ul>
  <li><code>execute store result ... run function #tag</code> stores the
      <strong>sum</strong> of the contributors' return values.</li>
  <li><code>unless function #tag</code> succeeds when the whole tag returns
      non-zero.</li>
</ul>
<p>With one contributor this is exactly a scalar return, which is why
   <code>perm_require</code> and <code>player_get_name</code> can behave like
   queries today. Add a second contributor and the values sum - deliberately, or
   not at all.</p>

<h2 id="services">Services the core publishes</h2>
<p>These ids are a stable surface. A module may call them; their signatures do
   not change.</p>
<table>
  <thead><tr><th>Id</th><th>Purpose</th></tr></thead>
  <tbody>
    <tr><td><code>me_core_module:text/split</code></td><td>Split text on a separator, into <code>macroengine:output</code></td></tr>
    <tr><td><code>me_core_module:text/concat</code></td><td>Join text values</td></tr>
    <tr><td><code>me_core_module:text/safe</code></td><td>Is this string safe as a score/tag literal (1/0)</td></tr>
    <tr><td><code>me_core_module:text/ctl</code></td><td>Does it contain a control character</td></tr>
    <tr><td><code>me_core_module:text/lower</code></td><td>Lower-case</td></tr>
    <tr><td><code>me_core_module:text/upper</code></td><td>Upper-case</td></tr>
    <tr><td><code>me_core_module:text/replace</code></td><td>Replace text</td></tr>
    <tr><td><code>me_core_module:text/reset</code></td><td>Clear the text scratch space</td></tr>
    <tr><td><code>me_core_module:core/ext/perm_count</code></td><td>Permission counter</td></tr>
    <tr><td><code>me_core_module:core/ext/player/add_var_clamped</code></td><td>Clamped add to a player variable</td></tr>
    <tr><td><code>me_core_module:core/ext/player/toggle_var</code></td><td>Flip a player variable</td></tr>
    <tr><td><code>me_core_module:core/ext/world/count_near</code></td><td>Count nearby entities</td></tr>
  </tbody>
</table>

<h2 id="internal">Where implementations live</h2>
<p>This is the rule the split hinges on, and it is the one that took two
   attempts to get right:</p>

<div class="note">
  <strong>Every function that implements something lives in
  <code>me_core_module</code>, under <code>core/internal/</code>. A module ships
  its public surface and nothing else.</strong>
</div>

<p>A module does call <code>me_core_module:core/internal/...</code> - that is
   normal and intended. There is one implementation of a feature, it lives in the
   core, and every caller reaches it by fully-qualified id.</p>

<p>What must never exist is a module carrying its own copy:</p>

<pre><code>data/me_cb_module/function/core/internal/cb/apply_defaults.mcfunction   &lt;- wrong</code></pre>

<p>That path reads like core's tree, resolves like a real id and passes a
   reference check - but it is private to <code>me_cb_module</code>, it is a
   second copy of core's code, and deleting the module silently deletes it.
   <code>verify.py</code> reports it as <code>module-internal</code> and fails the
   build. <code>test_split_guard.py</code> refuses any table in which something
   other than core claims a <code>core/internal/</code> prefix, so the generator
   cannot produce the shape at all.</p>

<p>Three functions are shared between core and a module - the store renderer,
   the multi-command executor and the player initialiser. Those are
   infrastructure, so they live in core, and the feature keeps a thin public
   function at its own id that forwards to them.</p>

<h2 id="ids">Function ids and naming</h2>
<p>Namespace <code>me_&lt;name&gt;_module</code>, ids
   <code>me_&lt;name&gt;_module:&lt;path&gt;</code>, and no <code>api/</code> or
   <code>systems/</code> segment. A function is public because the tag names it,
   not because of the directory it sits in.</p>

<table>
  <thead><tr><th>Good</th><th>Why</th></tr></thead>
  <tbody>
    <tr><td><code>me_wand_module:wand/tick</code></td><td>namespace identifies the module</td></tr>
    <tr><td><code>me_perm_module:perm/require</code></td><td>one path segment for the area</td></tr>
    <tr><td><code>me_player_module:player/resolve</code></td><td>matches the slot it serves</td></tr>
  </tbody>
</table>
<p>Forbidden: <code>macroengine:api/wand/tick</code> (shared namespace),
   <code>wand:tick</code> (module not identified),
   <code>me_wand_module:systems/tick</code> (invented layer).</p>

<p>Reaching into <em>another module's</em> namespace is forbidden too - modules
   never call each other's public surface. Both call core.</p>

<h2 id="checks">What gets checked</h2>
<p><code>python3 tools/verify.py --pack packs</code> or <code>gradlew check</code>
   looks for:</p>
<table>
  <thead><tr><th>Finding</th><th>Meaning</th></tr></thead>
  <tbody>
    <tr><td><code>dangling</code></td><td>a function or tag reference that does not resolve</td></tr>
    <tr><td><code>old-namespace</code></td><td>a real call still pointing at the old <code>macroengine:</code> namespace</td></tr>
    <tr><td><code>module-internal</code></td><td>a module shipping a <code>core/internal/</code> tree of its own</td></tr>
    <tr><td><code>private-access</code></td><td>a module calling another module</td></tr>
    <tr><td><code>hooks</code></td><td>the core calling a module by name</td></tr>
    <tr><td><code>cycle</code></td><td>a cycle in the module dependency graph</td></tr>
    <tr><td><code>pack-layout</code></td><td>a missing <code>pack.mcmeta</code> or an empty pack</td></tr>
  </tbody>
</table>
<p>Zero problems prints <code>[verify] OK</code>.</p>

<h2 id="manifest">The Fabric manifest</h2>
<p>Each module is a Fabric mod that bundles a datapack: the jar is the mod, the
   <code>data/</code> tree at its root is what Minecraft loads.</p>

<pre><code>{
  "schemaVersion": 1,
  "id": "macroengine-wand",
  "version": "${version}",
  "environment": "*",
  "depends": {
    "fabricloader": "&gt;=0.20.1",
    "macroengine-core": "*"
  }
}</code></pre>

<p><code>depends.macroengine-core</code> makes Fabric refuse to start an orphan
   module instead of loading a pack whose calls go nowhere.
   <code>environment: "*"</code> matters because the pack has to load in
   single-player as well as on a server.</p>

<h2>Every module, its namespace and its slots</h2>
<table>
  <thead><tr><th>Mod id</th><th>Namespace</th><th>Functions</th><th>Slots it fills</th></tr></thead>
  <tbody>
    <tr><td><code>macroengine-block</code></td><td><code>me_block_module</code></td><td>8</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-cb</code></td><td><code>me_cb_module</code></td><td>9</td><td><code>cb_tick</code></td></tr>
    <tr><td><code>macroengine-cmd</code></td><td><code>me_cmd_module</code></td><td>123</td><td><code>cmd_freeze_tick</code></td></tr>
    <tr><td><code>macroengine-color</code></td><td><code>me_color_module</code></td><td>10</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-config</code></td><td><code>me_config_module</code></td><td>12</td><td><code>config_ensure</code>, <code>config_init</code>, <code>config_apply</code></td></tr>
    <tr><td><code>macroengine-container</code></td><td><code>me_container_module</code></td><td>9</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-cooldown</code></td><td><code>me_cooldown_module</code></td><td>12</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-core</code></td><td><code>me_core_module</code></td><td>911</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-diagnostics</code></td><td><code>me_diagnostics_module</code></td><td>2</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-events</code></td><td><code>me_events_module</code></td><td>13</td><td><code>events_fire</code></td></tr>
    <tr><td><code>macroengine-flag</code></td><td><code>me_flag_module</code></td><td>13</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-gamerule</code></td><td><code>me_gamerule_module</code></td><td>3</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-geo</code></td><td><code>me_geo_module</code></td><td>9</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-hook</code></td><td><code>me_hook_module</code></td><td>40</td><td><code>hook_tick</code></td></tr>
    <tr><td><code>macroengine-input</code></td><td><code>me_input_module</code></td><td>41</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-interaction</code></td><td><code>me_interaction_module</code></td><td>7</td><td><code>interaction_tick</code></td></tr>
    <tr><td><code>macroengine-item</code></td><td><code>me_item_module</code></td><td>6</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-lib</code></td><td><code>me_lib_module</code></td><td>53</td><td><code>queue_process</code></td></tr>
    <tr><td><code>macroengine-logic</code></td><td><code>me_logic_module</code></td><td>10</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-macro</code></td><td><code>me_macro_module</code></td><td>5</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-perm</code></td><td><code>me_perm_module</code></td><td>20</td><td><code>perm_tick</code>, <code>perm_require</code></td></tr>
    <tr><td><code>macroengine-player</code></td><td><code>me_player_module</code></td><td>58</td><td><code>player_enumerate</code>, <code>player_resolve</code>, <code>player_init</code>, <code>player_init_online</code>, <code>player_init_from_name</code>, <code>player_get_name</code></td></tr>
    <tr><td><code>macroengine-queue</code></td><td><code>me_queue_module</code></td><td>6</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-rate-limit</code></td><td><code>me_rate_limit_module</code></td><td>7</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-sound</code></td><td><code>me_sound_module</code></td><td>3</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-state</code></td><td><code>me_state_module</code></td><td>11</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-store</code></td><td><code>me_store_module</code></td><td>32</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-text</code></td><td><code>me_text_module</code></td><td>5</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-title</code></td><td><code>me_title_module</code></td><td>16</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-toggle</code></td><td><code>me_toggle_module</code></td><td>25</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-trigger</code></td><td><code>me_trigger_module</code></td><td>7</td><td><code>trigger_dispatch</code></td></tr>
    <tr><td><code>macroengine-tui</code></td><td><code>me_tui_module</code></td><td>4</td><td>&mdash;</td></tr>
    <tr><td><code>macroengine-wand</code></td><td><code>me_wand_module</code></td><td>12</td><td><code>wand_tick</code></td></tr>
    <tr><td><code>macroengine-world</code></td><td><code>me_world_module</code></td><td>27</td><td>&mdash;</td></tr>
  </tbody>
</table>

<div class="note">
  <strong>Source:</strong> <code>modules.json</code> and
  <code>docs/CORE.md</code> in <code>macroengine-modules</code> - 34 artifacts,
  22 slots, 1,529 functions. If a build disagrees with this page, the build is
  right.
</div>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
