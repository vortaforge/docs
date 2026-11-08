<?php
declare(strict_types=1);

/**
 * join-throttle
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php, so
 * it exists once for all fifteen pages.
 */

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = 'mods';
$file        = 'join-throttle.php';
$title       = 'join-throttle';
$description = 'join-throttle: per-address connection rate limiting, with every setting and how attempts are counted.';
$crumbs      = '<a href="../index.php">Home</a> / <a href="index.php">Mods</a> / join-throttle';

ob_start();
?>
<p class="crumbs"><a href="../index.php">Home</a> / <a href="index.php">Mods</a> / join-throttle</p>
<div class="hero">
  <h1>join-throttle</h1>
  <p class="lede">Rate-limits connection attempts per address. Five attempts in
     thirty seconds, per address, counted before the handshake completes.</p>
</div>

<div class="toc">
  <strong>On this page</strong>
  <ol>
    <li><a href="#does">What it actually does</a></li>
    <li><a href="#install">Installation</a></li>
    <li><a href="#settings">Settings</a></li>
    <li><a href="#choosing">Choosing values</a></li>
    <li><a href="#counting">How attempts are counted</a></li>
    <li><a href="#limits">What it does not do</a></li>
  </ol>
</div>

<h2 id="does">What it actually does</h2>
<p>It counts join attempts per client address inside a sliding window. When an
   address exceeds the limit, further attempts are refused during that window
   with <code>denyMessage</code> instead of reaching the server's own login
   handling.</p>

<p>It works at the connection layer, not in the game - the attempt is denied
   before a player entity exists. That is why it can stop a reconnect flood that
   the server would otherwise spend real work on.</p>

<h2 id="install">Installation</h2>
<ol>
  <li>Drop the jar in <code>mods/</code>. Fabric Loader only; no Fabric API.</li>
  <li>Start the server once to create <code>config/join-throttle.json</code>.</li>
  <li>Adjust and restart.</li>
</ol>

<div class="warnbox">
  <strong>Put it behind your proxy's real address.</strong> If the server sits
  behind Velocity or BungeeCord and does not forward the client address, every
  connection arrives from the proxy's address and one busy address throttles
  everyone. Enable address forwarding on the proxy first.
</div>

<h2 id="settings">Settings</h2>
<table>
  <thead><tr><th>Key</th><th>Default</th><th>What it does</th></tr></thead>
  <tbody>
    <tr><td><code>maxJoins</code></td><td><code>5</code></td>
        <td>How many attempts an address may make inside the window before
            further ones are refused.</td></tr>
    <tr><td><code>windowSeconds</code></td><td><code>30</code></td>
        <td>The length of the sliding window, in seconds. The count is not
            reset on a boundary; attempts older than the window stop
            counting.</td></tr>
    <tr><td><code>denyMessage</code></td>
        <td><code>"Too many connection attempts. Please wait a moment and try again."</code></td>
        <td>The disconnect reason shown to a refused client. Keep it vague on
            purpose - a precise message tells an attacker exactly how much room
            is left.</td></tr>
  </tbody>
</table>

<h2 id="choosing">Choosing values</h2>

<div class="grid">
  <div class="card">
    <h3>The default</h3>
    <pre><code>{
  "maxJoins": 5,
  "windowSeconds": 30
}</code></pre>
    <p>Five in thirty seconds. Generous for a person with a flaky connection -
       they can fail four times and still get in - and tight enough that a
       scripted flood is stopped almost immediately.</p>
  </div>
  <div class="card">
    <h3>Strict, public server</h3>
    <pre><code>{
  "maxJoins": 3,
  "windowSeconds": 60
}</code></pre>
    <p>Three in a minute. Catches slow-and-steady reconnectors. Raise it if you
       get complaints from players on mobile data, who legitimately drop and
       retry more than once a minute.</p>
  </div>
  <div class="card">
    <h3>Whitelisted, private</h3>
    <pre><code>{
  "maxJoins": 10,
  "windowSeconds": 15
}</code></pre>
    <p>Loose. The whitelist is the real boundary here; this setting only stops
       a runaway client, so it should never be the thing that locks out a
       legitimate player.</p>
  </div>
</div>

<h2 id="counting">How attempts are counted</h2>
<ul>
  <li><strong>Per address, not per name.</strong> Two players behind one home
      router share a count.</li>
  <li><strong>Refused attempts count.</strong> Being throttled does not reset
      the window, so hammering extends the refusal rather than shortening
      it.</li>
  <li><strong>A successful join also counts.</strong> An address that joins five
      times in thirty seconds is at the limit even if all five succeeded.</li>
  <li><strong>State is in memory.</strong> A restart clears every counter. This
      is not a ban list and does not survive a restart.</li>
  <li><strong>IPv4 and IPv6 are separate addresses.</strong> A client that can
      reach the server over both is counted separately on each.</li>
</ul>

<h2 id="limits">What it does not do</h2>
<ul>
  <li><strong>It does not ban.</strong> No permanent record, no allow-list, no
      staff command to lift a throttle - it clears on its own.</li>
  <li><strong>It does not distinguish a bot from a person.</strong> There is no
      CAPTCHA, no proof-of-work and no per-username logic.</li>
  <li><strong>It does not limit in-game actions.</strong> Once a player is
      connected, this mod has no further effect. Movement, chat and commands
      are not throttled.</li>
  <li><strong>It does not replace a firewall.</strong> A distributed flood from
      many addresses stays under the per-address limit by construction. This
      stops reconnection loops, not a botnet.</li>
</ul>

<div class="note">
  <strong>These are the shipped values.</strong>
  <code>maxJoins = 5</code>, <code>windowSeconds = 30</code> and the default
  <code>denyMessage</code> come from <code>ThrottleSettings</code>, and every
  value is clamped by <code>sanitized()</code> before use - a negative window
  becomes the minimum rather than an unbounded window.
</div>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
