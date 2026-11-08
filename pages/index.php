<?php
declare(strict_types=1);

/**
 * VortaForge
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php, so
 * it exists once for all fifteen pages.
 */

require_once __DIR__ . '/../tools/sitelib.php';

$section     = '.';
$file        = 'index.php';
$title       = 'VortaForge';
$description = 'Datapack and mod engineering for Minecraft 26.3: macroEngine&#x27;s 34-pack modular engine and two small Fabric mods.';
$crumbs      = 'Home';

ob_start();
?>
<p class="crumbs">Home</p>
<div class="hero">
  <h1>VortaForge</h1>
  <p class="lede">Datapack engineering for Minecraft 26.3: a modular engine you
     can take apart, and two small server mods that do one thing each.</p>
</div>

<div class="grid">
  <div class="card">
    <h3><a href="macroengine/index.php">macroEngine</a></h3>
    <p>A command engine split into one core and 33 independently installable
       modules. Remove any module and the rest keeps running.</p>
    <p><span class="tag">34 packs</span> <span class="tag">22 hook slots</span>
       <span class="tag">1,529 functions</span></p>
  </div>
  <div class="card">
    <h3><a href="mods/index.php">Mods</a></h3>
    <p>afk-manager and join-throttle: two Fabric mods that solve a single server
       problem each, with no Fabric API dependency.</p>
    <p><span class="tag">Java 25</span> <span class="tag">26.3</span>
       <span class="tag">server-side</span></p>
  </div>
</div>

<h2>Start where you are</h2>
<table>
  <thead><tr><th>If you want to&hellip;</th><th>Read</th></tr></thead>
  <tbody>
    <tr><td>Install the engine and see it work</td>
        <td><a href="macroengine/index.php">macroEngine overview</a>, then
            <a href="docs/versions.php">versions</a></td></tr>
    <tr><td>Understand why it is split up</td>
        <td><a href="macroengine/architecture.php">Architecture</a></td></tr>
    <tr><td>Write a module</td>
        <td><a href="docs/api.php">API</a> &mdash; the 22 slots and the
            <code>core/internal/</code> rule</td></tr>
    <tr><td>Look up a command</td>
        <td><a href="macroengine/reference.php">Command reference</a> (3,700
            words, every command)</td></tr>
    <tr><td>Know which module does what</td>
        <td><a href="macroengine/modules.php">Modules</a></td></tr>
    <tr><td>Configure afk-manager or join-throttle</td>
        <td><a href="mods/index.php">Mods</a> &mdash; every key and its
            clamp</td></tr>
    <tr><td>Understand why old packs are retired</td>
        <td><a href="datapacks/index.php">Datapacks</a> and
            <a href="docs/warnings.php">Warnings</a></td></tr>
    <tr><td>Build the docs or the site yourself</td>
        <td><a href="docs/php.php">PHP tooling</a> and
            <a href="docs/maintainers.php">Maintainer guide</a></td></tr>
  </tbody>
</table>

<h2>What "independently installable" actually means</h2>
<p>A datapack cannot import another datapack. That single constraint shapes the
   whole project: instead of modules calling each other, every extension point is
   a function tag owned by the core, and modules add themselves to it.</p>

<p>The consequence is checkable, not aspirational. Each of the 34 packs is built
   once with exactly one pack absent, and the engine has to reach load and tick
   anyway. All 34 pass. If that sounds like a low bar, it is the bar that
   separates a modular engine from a monolith with folders.</p>

<h2>What is here</h2>
<table>
  <thead><tr><th>Area</th><th>Pages</th><th>What it covers</th></tr></thead>
  <tbody>
    <tr><td>macroEngine</td><td>4</td>
        <td>Overview, architecture, the 22-slot API, all 33 modules, the full
            command reference</td></tr>
    <tr><td>Mods</td><td>3</td>
        <td>Both Fabric mods, every setting, every clamp</td></tr>
    <tr><td>Datapacks</td><td>1</td>
        <td>The archived packs, their pack formats and why they are retired</td></tr>
    <tr><td>Docs</td><td>5</td>
        <td>Versions, warnings, maintainer guide, PHP tooling, archive table</td></tr>
  </tbody>
</table>

<h2>Honest limits</h2>
<ul>
  <li><strong>Everything archived is archived.</strong> The packs on
      <a href="datapacks/index.php">Datapacks</a> are listed so an old-world
      question has an answer. They are not maintained and not supported.</li>
  <li><strong>26.3 only.</strong> There is no multi-version branch. The engine
      targets one Minecraft release and moves when that release does.</li>
  <li><strong>The two mods are narrow on purpose.</strong> Neither has a
      permission system, a GUI or a database. A mod that grows a second
      responsibility is harder to remove than the thing it replaced.</li>
</ul>

<div class="note">
  <strong>Numbers on this site come from source.</strong> 34 artifacts and 1,529
  functions from <code>modules.json</code>, the clamps from
  <code>AfkSettings</code> / <code>ThrottleSettings</code>, the pack formats from
  <code>archive.json</code>. Where a page states a number, it was read, not
  estimated.
</div>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
