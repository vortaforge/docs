<?php
declare(strict_types=1);

/**
 * afk-manager
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php, so
 * it exists once for all fifteen pages.
 */

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = 'mods';
$file        = 'afk-manager.php';
$title       = 'afk-manager';
$description = 'afk-manager: AFK detection, announcement and optional kick, with every setting and its clamp.';
$crumbs      = '<a href="../index.php">Home</a> / <a href="index.php">Mods</a> / afk-manager';

ob_start();
?>
<p class="crumbs"><a href="../index.php">Home</a> / <a href="index.php">Mods</a> / afk-manager</p>
<div class="hero">
  <h1>afk-manager</h1>
  <p class="lede">Marks a player as AFK after a period of no movement, announces
     it, and optionally kicks them. Every value has a clamp, and the clamps are
     listed here because the defaults alone do not tell you what the mod will do
     with a value you set yourself.</p>
</div>

<div class="toc">
  <strong>On this page</strong>
  <ol>
    <li><a href="#does">What it actually does</a></li>
    <li><a href="#install">Installation</a></li>
    <li><a href="#command">The <code>/afk</code> command</a></li>
    <li><a href="#settings">Settings, with their clamps</a></li>
    <li><a href="#choosing">Choosing values</a></li>
    <li><a href="#timing">How the timer works</a></li>
    <li><a href="#limits">What it does not do</a></li>
  </ol>
</div>

<h2 id="does">What it actually does</h2>
<p>On each server tick the mod samples each online player's position. When the
   samples stop changing for <code>afkAfterSeconds</code>, the player is treated
   as AFK. If <code>announce</code> is on, that is said in chat. If
   <code>kickAfterSeconds</code> is greater than zero and the player stays AFK
   that long, they are disconnected with <code>kickMessage</code>.</p>

<p>The timer resets on movement, not on any other activity. A player standing
   perfectly still with their inventory open is AFK by this definition; a player
   walking in a one-block square forever is not.</p>

<h2 id="install">Installation</h2>
<ol>
  <li>Drop the jar in <code>mods/</code>. It needs Fabric Loader and nothing
      else - Fabric API is not a dependency.</li>
  <li>Start the server once. <code>config/afk-manager.json</code> is created
      with the defaults below.</li>
  <li>Edit it and restart, or leave the defaults and use <code>/afk</code>.</li>
</ol>

<p>If a value is missing from the file it is filled from the default. If a value
   is present but out of range it is clamped, not rejected: the server still
   starts, and the effective value is what the clamp produced.</p>

<h2 id="command">The <code>/afk</code> command</h2>
<p>One command, no arguments:</p>
<pre><code>/afk</code></pre>
<p>It marks the sender as AFK immediately, without waiting for
   <code>afkAfterSeconds</code>. That is the whole surface - there is no
   <code>/afk status</code>, no target argument and no way to mark someone
   else.</p>

<h2 id="settings">Settings, with their clamps</h2>
<table>
  <thead><tr><th>Key</th><th>Default</th><th>Range / clamp</th><th>What it does</th></tr></thead>
  <tbody>
    <tr>
      <td><code>afkAfterSeconds</code></td><td><code>300</code></td>
      <td>10 &ndash; 86,400</td>
      <td>Seconds without movement before a player counts as AFK. Values below
          10 are raised to 10; above 86,400 are lowered to 86,400.</td>
    </tr>
    <tr>
      <td><code>kickAfterSeconds</code></td><td><code>0</code></td>
      <td>0, or at least <code>afkAfterSeconds</code> &ndash; 604,800</td>
      <td><code>0</code> means never kick. Any positive value below
          <code>afkAfterSeconds</code> is raised to it, because kicking someone
          before they are AFK is not a meaningful setting. The cap is seven days.</td>
    </tr>
    <tr>
      <td><code>announce</code></td><td><code>true</code></td>
      <td>boolean</td>
      <td>Say in chat when a player goes AFK. Turning this off does not affect
          the kick timer.</td>
    </tr>
    <tr>
      <td><code>exemptOperatorsFromKick</code></td><td><code>true</code></td>
      <td>boolean</td>
      <td>Operators are never kicked, but they are still marked and announced.
          Set to <code>false</code> if an idle operator should be removed.</td>
    </tr>
    <tr>
      <td><code>kickMessage</code></td><td><code>"Kicked for being AFK."</code></td>
      <td>max 200 characters</td>
      <td>The disconnect reason. A blank or missing value falls back to the
          default; longer values are cut at 200.</td>
    </tr>
  </tbody>
</table>

<h2 id="choosing">Choosing values</h2>

<div class="grid">
  <div class="card">
    <h3>Survival, no kicks</h3>
    <pre><code>{
  "afkAfterSeconds": 300,
  "kickAfterSeconds": 0,
  "announce": true
}</code></pre>
    <p>The default. Nobody is removed; the chat note is informational. Use this
       when the server does not need the slot back.</p>
  </div>
  <div class="card">
    <h3>Small server, reclaim slots</h3>
    <pre><code>{
  "afkAfterSeconds": 180,
  "kickAfterSeconds": 600,
  "announce": true,
  "exemptOperatorsFromKick": true
}</code></pre>
    <p>Three minutes to AFK, ten minutes to kick. Operators stay. This is the
       setting that stops one idle player from holding the last slot all night.</p>
  </div>
  <div class="card">
    <h3>Keep the tab list honest</h3>
    <pre><code>{
  "afkAfterSeconds": 60,
  "kickAfterSeconds": 0,
  "announce": true
}</code></pre>
    <p>One minute is short enough that the announcement is genuinely useful and
       long enough that reading a sign does not trigger it.</p>
  </div>
</div>

<h2 id="timing">How the timer works</h2>
<p>The check runs every server tick, not on a wall clock. That has three
   consequences worth knowing:</p>
<ul>
  <li>A tick that takes 200 ms ages the AFK timer by one tick, not by 200 ms.
      On a lagging server, players go AFK later than the configured time in real
      seconds.</li>
  <li>Stopping the server stops the timer. A restart does not resume an
      in-progress AFK period; the sample resets.</li>
  <li>Teleporting counts as movement. A player moved by a plugin or a portal is
      not AFK afterwards.</li>
</ul>

<h2 id="limits">What it does not do</h2>
<ul>
  <li><strong>It does not read chat.</strong> Talking while standing still stays
      AFK. That is deliberate - typing is not moving - but it surprises people.</li>
  <li><strong>It does not persist AFK state.</strong> Nothing is written to disk
      about who was AFK.</li>
  <li><strong>It does not change the player list.</strong> No <code>[AFK]</code>
      prefix, no tab-list colour, no scoreboard. Announcement is a chat line.</li>
  <li><strong>It has no permission node.</strong> <code>/afk</code> is available
      to everyone, which is the point of it.</li>
</ul>

<div class="note">
  <strong>This page describes the shipped defaults and clamps.</strong>
  They are read from <code>AfkSettings</code>: <code>afkAfterSeconds = 300</code>,
  <code>kickAfterSeconds = 0</code>, <code>announce = true</code>,
  <code>exemptOperatorsFromKick = true</code>, and a 200-character cap on the
  message. If a build you are running disagrees, the build is right and this page
  is out of date.
</div>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
