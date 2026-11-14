<?php
declare(strict_types=1);

/**
 * Vortex dialog API
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php.
 */

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = 'docs';
$file        = 'dialog.php';
$title       = 'Vortex dialog API';
$description = 'The dialog module of the vortex datapack: build a dialog at runtime, route non-OP clicks back to server functions, keep UI state per player.';
$crumbs      = '<a href="../index.php">Home</a> / <a href="api.php">Docs</a> / Dialog';

ob_start();
?>
<p class="crumbs"><a href="../index.php">Home</a> / <a href="api.php">Docs</a> / Dialog</p>
<div class="hero">
  <h1>Vortex dialog API</h1>
  <p class="lede">Build a native dialog at runtime from a datapack, let any player
     click it, and get the click back as a server function call. Ported from
     Modalith 2.2.0 into the <code>vortex</code> datapack (0.3.0).</p>
</div>

<div class="banner">
  <strong>Compatibility is not verified.</strong> Modalith targets Minecraft 26.3
  (data pack version <code>121.0</code>); vortex targets 1.21.7-1.21.8
  (<code>pack_format</code> 81). The port was checked statically: JSON is valid,
  every <code>function</code> reference and function tag resolves, macro lines
  carry <code>$(...)</code>. It has not been run in a 1.21.8 game, and Modalith's
  test suite and demo pack were not ported.
</div>

<h2>What it does</h2>
<p>The module serializes an NBT compound from storage into an inline dialog
   definition and opens it with <code>dialog show</code>. It does not register
   dialog ids and does not bypass vanilla permissions or codecs. Buttons run
   <code>/trigger</code> with a numeric ticket; the server looks the ticket up in
   a per-player allowlist and calls the function you named. The client never
   sends a function name or a payload.</p>

<h2>Where it lives</h2>
<table>
  <thead><tr><th>Modalith</th><th>vortex</th></tr></thead>
  <tbody>
    <tr><td><code>mdl:api/*</code></td><td><code>vortex:api/dialog/*</code> (public)</td></tr>
    <tr><td><code>mdl:action/*</code></td><td><code>vortex:api/dialog/native_action/*</code> (client-side button actions)</td></tr>
    <tr><td><code>mdl:internal/*</code></td><td><code>vortex:core/internal/dialog/*</code> (not API)</td></tr>
    <tr><td><code>#mdl:&lt;hook&gt;</code></td><td><code>#vortex:hook/dialog_&lt;hook&gt;</code></td></tr>
    <tr><td>storage <code>mdl:&lt;x&gt;</code></td><td>storage <code>vortex:dialog_&lt;x&gt;</code></td></tr>
    <tr><td>objective or tag <code>mdl.&lt;x&gt;</code></td><td><code>vortex.dialog_&lt;x&gt;</code></td></tr>
  </tbody>
</table>
<p><code>vortex:core/internal/dialog/load</code> runs from <code>vortex:setup</code>.
   <code>vortex:core/internal/dialog/tick</code> is registered in
   <code>#minecraft:tick</code> directly, so ticket lifetimes and cooldowns count
   real game ticks and are not slowed by the engine's <code>tick.rate</code>.</p>

<h2>Conventions</h2>
<ul>
  <li>Builder calls run <strong>as a player</strong>, in one synchronous
      <code>begin</code> &rarr; setters &rarr; <code>show</code> chain.</li>
  <li><code>text</code>, <code>title</code> and <code>label</code> take compound
      text components (<code>{text:"Hello"}</code>), never bare strings.</li>
  <li><code>callback</code> is an array of trusted typed action objects, never
      user-supplied text. <code>guard</code> is a trusted predicate id.</li>
  <li>Defaults: <code>pause:false</code>, <code>after_action:"wait_for_response"</code>,
      Escape enabled, two columns.</li>
</ul>

<h2>Core builder</h2>
<table>
  <thead><tr><th>Function (under <code>vortex:api/dialog/</code>)</th><th>Arguments / behaviour</th></tr></thead>
  <tbody>
    <tr><td><code>begin</code></td><td>Creates a default multi-action draft for <code>@s</code>. Does not consume the current session until <code>show</code> commits.</td></tr>
    <tr><td><code>title</code></td><td><code>{text:{text:"Title",color:"gold"}}</code></td></tr>
    <tr><td><code>body</code></td><td><code>{text:{text:"Message"},width:300}</code></td></tr>
    <tr><td><code>item</code></td><td><code>{item:{id:"minecraft:diamond",count:1},text:{text:"Diamond"}}</code></td></tr>
    <tr><td><code>options</code></td><td><code>{columns:2,escape:1b}</code></td></tr>
    <tr><td><code>input</code></td><td><code>{input:{type:"minecraft:boolean",key:"value",label:"Enabled"}}</code></td></tr>
    <tr><td><code>button</code></td><td><code>{label:{text:"Choose"},callback:[...],payload:{id:4}}</code></td></tr>
    <tr><td><code>exit</code></td><td>Same parameters as <code>button</code>; the footer / Escape action.</td></tr>
    <tr><td><code>raw_button</code></td><td>A native button, for example a copy-to-clipboard action; gets no server callback.</td></tr>
    <tr><td><code>view</code></td><td>Defines the renderer used by <code>back</code> and <code>refresh</code>.</td></tr>
    <tr><td><code>notice</code>, <code>confirmation</code></td><td>Move the first one or two buttons into native notice or yes/no fields. Call after all buttons, before <code>show</code>.</td></tr>
    <tr><td><code>show</code></td><td>Renders the compound and commits on success. Returns 1 on success, 0 on a guarded failure.</td></tr>
    <tr><td><code>close</code>, <code>forget</code></td><td>Clear the UI and invalidate the session; <code>forget</code> also resets the player's library state.</td></tr>
  </tbody>
</table>

<h2>A first dialog</h2>
<pre><code>function vortex:api/dialog/begin
function vortex:api/dialog/title {text:{text:"Shop",color:"gold"}}
function vortex:api/dialog/body {text:{text:"Buy a diamond?"},width:300}
function vortex:api/dialog/button {label:{text:"Buy"},callback:[{type:"function",function:"shop:purchase"}],payload:{sku:12}}
function vortex:api/dialog/show</code></pre>
<p>When the player clicks <em>Buy</em>, the router runs
   <code>function shop:purchase {sku:12}</code> as that player with server-function
   permission. The session is consumed and the dialog cleared before hooks and the
   callback run, so the callback may open a new dialog.</p>

<h2>Hooks</h2>
<p>Seven function tags exist, all empty by default:
   <code>#vortex:hook/dialog_on_show</code>, <code>_on_close</code>,
   <code>_on_timeout</code>, <code>_on_reject</code>, <code>_on_event</code>,
   <code>_before_action</code> and <code>_after_action</code>. During
   <code>_on_event</code>, <code>storage vortex:dialog_event</code> holds
   <code>type</code> (<code>"click"</code> or <code>"input"</code>), the player
   and the trusted route. It is shared scratch: the next lifecycle event, including
   <code>show</code>, overwrites it, so copy what you need.</p>

<h2>Other features</h2>
<p>All under <code>vortex:api/dialog/</code>. Argument shapes are those of
   Modalith 2.2.0; the original reference is <code>docs/API.md</code> in
   <a href="https://github.com/tickwarden/modalith">tickwarden/modalith</a>.</p>
<table>
  <thead><tr><th>Area</th><th>Functions</th></tr></thead>
  <tbody>
    <tr><td>Navigation</td><td><code>push</code>, <code>back</code>, <code>refresh</code> (history depth 16)</td></tr>
    <tr><td>Catalogs</td><td><code>catalog</code>, <code>catalog_page</code>, <code>catalog_filter</code>, <code>catalog_sorted</code>, <code>catalog_favorites</code>, <code>catalog_reopen</code></td></tr>
    <tr><td>Numeric forms</td><td><code>form_digit</code>, <code>form_code</code>, <code>form_rules</code>, <code>form_guard</code></td></tr>
    <tr><td>Templates</td><td><code>template_save</code>, <code>template_load</code>, <code>template_delete</code></td></tr>
    <tr><td>Guards and cooldown</td><td><code>guarded_button</code>, <code>form_guard</code>, <code>cooldown</code></td></tr>
    <tr><td>Actions</td><td><code>actions</code>, <code>action_conditional</code>, <code>action_delay</code>, <code>action_delay_cancel</code></td></tr>
    <tr><td>Button state</td><td><code>button_enabled</code>, <code>button_label</code>, <code>button_tooltip</code>, <code>button_width</code></td></tr>
    <tr><td>Live views</td><td><code>auto_refresh</code>, <code>auto_refresh_stop</code>, <code>progress</code>, <code>progress_style</code></td></tr>
    <tr><td>Themes and locale</td><td><code>theme_register</code>, <code>theme_apply</code>, <code>locale_set</code>, <code>translation_register</code>, <code>translate</code></td></tr>
    <tr><td>Player state</td><td><code>state_set</code>, <code>state_get</code>, <code>state_remove</code>, <code>state_clear</code>, <code>favorite_set</code>, <code>favorite_get</code>, <code>favorite_remove</code>, <code>favorites_clear</code></td></tr>
    <tr><td>Notifications and stats</td><td><code>notify</code>, <code>notifications_show</code>, <code>notifications_clear</code>, <code>stats</code>, <code>stats_reset</code></td></tr>
  </tbody>
</table>

<h2>Rules that matter</h2>
<ul>
  <li>Check stock, balance and state inside the privileged callback too. A guard
      predicate protects the button; it does not make the callback safe.</li>
  <li>An extra route stays in the server allowlist even when its button is no
      longer visible. Never use visibility as authorization.</li>
  <li>Ticket values are signed 32-bit integers with a global ceiling of
      200,000,000. The API refuses overflow instead of recycling tickets.</li>
  <li>Template keys and contents are trusted developer data. There is no key
      validation and no eviction.</li>
  <li>The module creates its own scoreboard objectives (<code>vortex.dialog_*</code>)
      and storage (<code>vortex:dialog_*</code>); it does not use
      <code>vortex.meta</code> or <code>vortex:engine</code>.</li>
</ul>

<h2>Licence</h2>
<p>The dialog files are derived from Modalith (MIT, copyright Modalith
   contributors). The vortex repository is otherwise public domain; the MIT
   notice is kept in <code>NOTICE-modalith.md</code> at the repository root.</p>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
