<?php
declare(strict_types=1);

/**
 * PHP
 *
 * This is the page. Everything below `$body` is what a reader sees; the shell
 * around it - doctype, head, navigation, footer - is in tools/sitelib.php, so
 * it exists once for all fifteen pages.
 */

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = 'docs';
$file        = 'php.php';
$title       = 'PHP';
$description = 'How PHP is used in the VortaForge docs build, what it may do, and what it is not.';
$crumbs      = '<a href="../index.php">Home</a> / <a href="api.php">Docs</a> / PHP';

ob_start();
?>
<p class="crumbs"><?= $crumbs ?></p>
<div class="hero">
  <h1>PHP</h1>
  <p class="lede">Every page on this site is a PHP script. This page explains
     why, what that changes on the server, and the one thing PHP here is still
     not allowed to be.</p>
</div>

<div class="banner">
  <strong>The pages are <code>.php</code>, and they execute.</strong> Each one
  sets a title, a description and a section, writes its body, and includes the
  shared shell that wraps it. Nothing is generated ahead of time - the server
  renders the page when you ask for it.
</div>

<div class="toc">
  <strong>On this page</strong>
  <ol>
    <li><a href="#why">Why PHP, and why now</a></li>
    <li><a href="#how">How a page is built</a></li>
    <li><a href="#hosting">What this needs from a host</a></li>
    <li><a href="#static">The static snapshot, for hosts that cannot do this</a></li>
    <li><a href="#tools">The tools</a></li>
    <li><a href="#not">What PHP here must never be</a></li>
    <li><a href="#checks">What enforces it</a></li>
    <li><a href="#archived">The archived script</a></li>
  </ol>
</div>

<h2 id="why">Why PHP, and why now</h2>
<p>Three things were true of the previous layout and all three were annoying:</p>

<ul>
  <li><strong>The head was duplicated fifteen times.</strong> Doctype, meta,
      stylesheet, navigation, footer - the same block, copied, with a
      <code>../</code> or two changed per file. A change to the navigation meant
      fifteen edits.</li>
  <li><strong>It drifted.</strong> Seven different stylesheets had accumulated
      across thirteen pages before the last cleanup. A tool that compares copies
      treats the symptom; one shared source removes the disease.</li>
  <li><strong>Correct links were a matter of care.</strong> Every page spelled
      out its own relative paths. Nothing checked them at render time because
      nothing rendered.</li>
</ul>

<p>With the shell in one include, the head exists once, the stylesheet is read
   from <code>assets/style.css</code> at render time, and the navigation is
   computed from one array instead of typed fifteen times. The change is not
   cosmetic: the class of bug where two pages disagree about the site is gone.</p>

<h2 id="how">How a page is built</h2>
<p>Every file under <code>pages/</code> has the same four parts.</p>

<pre><code>&lt;?php
declare(strict_types=1);

require_once __DIR__ . '/../../tools/sitelib.php';

$section     = "docs";
$file        = "api.php";
$title       = "API";
$description = "…";
$crumbs      = '<a href="../index.php">Home</a> / API';

ob_start();
?&gt;
&lt;h2&gt;The real content&lt;/h2&gt;
&lt;p&gt;Ordinary HTML.&lt;/p&gt;
&lt;?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);</code></pre>

<p>The <code>ob_start()</code> pair is what lets the body stay readable: it is
   HTML, not a string literal with escapes in it. <code>vf_render()</code> wraps
   it in the shell and emits it.</p>

<table>
  <thead><tr><th>Piece</th><th>Lives in</th><th>What it decides</th></tr></thead>
  <tbody>
    <tr><td><code>vf_page()</code></td><td><code>tools/sitelib.php</code></td>
        <td>doctype, head, inline stylesheet, wrapper</td></tr>
    <tr><td><code>vf_nav_html()</code></td><td><code>tools/sitelib.php</code></td>
        <td>the ten navigation links, and which one is <code>here</code></td></tr>
    <tr><td><code>vf_footer_html()</code></td><td><code>tools/sitelib.php</code></td>
        <td>the footer, once</td></tr>
    <tr><td><code>vf_style()</code></td><td><code>assets/style.css</code></td>
        <td>the stylesheet, read at render time</td></tr>
    <tr><td>the body</td><td><code>pages/**/*.php</code></td>
        <td>everything a reader came for</td></tr>
  </tbody>
</table>

<p>The stylesheet is still inlined rather than linked. That is not an oversight:
   the page has to render with no network at all, and a linked stylesheet is a
   network request. Inlining it costs a few kilobytes per page and removes a
   request that can fail.</p>

<h2 id="hosting">What this needs from a host</h2>
<p>A PHP 8.1+ interpreter. Nothing else - no extensions beyond what a stock
   <code>php-cli</code> / <code>php-fpm</code> provides, since the pages read one
   file and compute some strings.</p>

<div class="warnbox">
  <strong>GitHub Pages cannot serve this.</strong> Pages serves static files
  only; a <code>.php</code> file there is downloaded as text, so a visitor sees
  the source. The site is therefore hosted where PHP runs, and the Pages
  deployment described below is the static snapshot, not the primary artifact.
  If you are looking at this page on the Pages deployment, it is a rendered copy.
</div>

<h2 id="static">The static snapshot, for hosts that cannot do this</h2>
<p><code>tools/render_pages.php --out=build</code> renders all sixteen pages and
   writes the result as <code>.html</code>. Because the pages are stateless and
   their links are relative, the output is a complete static site - the same
   thing GitHub Pages publishes.</p>

<pre><code>php tools/render_pages.php --out=build</code></pre>

<p>Two properties make this safe to offer. The rendered snapshot cannot drift
   from the PHP pages, because it is produced from them. And a page that fails
   to render fails here, in the build, rather than in front of a reader - the
   tool refuses to write a snapshot containing a page that threw.</p>

<h2 id="tools">The tools</h2>
<table>
  <thead><tr><th>Tool</th><th>Does</th><th>Fails when</th></tr></thead>
  <tbody>
    <tr>
      <td><code>tools/sitelib.php</code></td>
      <td>The shell: navigation, stylesheet, footer, <code>vf_render()</code></td>
      <td>A page includes a missing stylesheet</td>
    </tr>
    <tr>
      <td><code>tools/render_pages.php</code></td>
      <td>Renders all 16 pages in-process; checks the stylesheet and navigation
          reached each one; can write the static snapshot</td>
      <td>Any page throws or warns, or is missing the stylesheet</td>
    </tr>
    <tr>
      <td><code>tools/build_docs_index.php</code></td>
      <td>Writes <code>_site/docs-index.json</code>: path, title, section, word
          and heading count per page</td>
      <td>A page fails to render while being indexed</td>
    </tr>
    <tr>
      <td><code>tools/test_php_safety.php</code></td>
      <td>The bans, the CLI guard on <code>tools/</code>, and a check that no
          page reads request input</td>
      <td>Any of the above is violated</td>
    </tr>
    <tr>
      <td><code>tools/cli.php</code></td>
      <td>The shared guard: non-CLI SAPI exits 404, PHP below 8.1 exits with a
          message</td>
      <td>Required by every build script</td>
    </tr>
  </tbody>
</table>

<p>The index builder renders each page instead of reading its source, and that
   is deliberate: a page that cannot be rendered cannot be indexed, so the index
   doubles as a smoke test over all sixteen.</p>

<h2 id="not">What PHP here must never be</h2>
<p>PHP has a well-known failure mode where a small helper grows into a web
   shell: it starts evaluating request input as code, or shelling out, or editing
   files on behalf of whoever asked. That is the class of tooling this repository
   refuses to contain, and the refusal is enforced rather than promised.</p>

<p>The distinction that matters now that pages execute:</p>

<table>
  <thead><tr><th></th><th>Pages (<code>pages/</code>)</th><th>Build scripts (<code>tools/</code>)</th></tr></thead>
  <tbody>
    <tr><td>Runs on a server</td><td>yes - that is the point</td><td>no</td></tr>
    <tr><td>Reads request input</td><td><span class="no">never</span></td>
        <td><span class="no">never</span></td></tr>
    <tr><td>CLI guard</td><td>n/a - must accept a web SAPI</td>
        <td><span class="yes">required</span>, 404 otherwise</td></tr>
    <tr><td>Writes files</td><td>no</td><td>yes, under <code>_site/</code></td></tr>
    <tr><td>Executes a shell</td><td><span class="no">never</span></td>
        <td><span class="no">never</span></td></tr>
  </tbody>
</table>

<p>A page that cannot read request input cannot be told to do anything. That
   single restriction is why dynamic pages did not trade away the safety the
   static site had.</p>

<h2 id="checks">What enforces it</h2>
<p><code>php tools/test_php_safety.php</code> scans <strong>executable
   PHP</strong> - comments, inline HTML and string literals are stripped first,
   because a paragraph explaining why <code>eval</code> is banned must not be
   read as a call to it.</p>

<table>
  <thead><tr><th>Check</th><th>Scope</th><th>Catches</th></tr></thead>
  <tbody>
    <tr><td>Banned constructs</td><td>every <code>.php</code></td>
        <td><code>eval</code>, <code>assert($var)</code>,
            <code>create_function</code>, <code>/e</code>,
            <code>call_user_func($var)</code>, <code>system</code> and the
            <code>exec</code> family, <code>pcntl_exec</code>,
            request superglobals, <code>filter_input</code>,
            <code>base64_decode($_…)</code>, request-driven file writes</td></tr>
    <tr><td>CLI guard</td><td><code>tools/</code> only</td>
        <td>A build script a web server could execute</td></tr>
    <tr><td>No request input</td><td><code>pages/</code></td>
        <td>A page reading <code>$_GET</code>, <code>$_POST</code>,
            <code>$_SERVER</code> or the rest</td></tr>
    <tr><td>Shell, not hand-rolled</td><td><code>pages/</code></td>
        <td>A page that bypasses <code>sitelib.php</code></td></tr>
    <tr><td>Renders clean</td><td><code>pages/</code></td>
        <td>A page that throws, warns, or emits something that is not HTML</td></tr>
  </tbody>
</table>

<p>The guard is exempted for <code>pages/</code> and for
   <code>sitelib.php</code> precisely because those must run under a web server.
   Scoping the rule by directory is what lets "pages execute" and "scripts must
   not" both be true at once.</p>

<h2 id="archived">The archived script</h2>
<p>Before this site was PHP, an unrelated generator lived in the
   <a href="https://github.com/vortaforge/core">core repository</a>:
   <code>archived/site/build_site_data.php</code>. It is kept there as readable
   history and never runs.</p>

<p>It is worth reading as a counter-example that was done correctly: it refuses
   any SAPI but CLI, never prints the token it reads, pins
   <code>CURLOPT_PROTOCOLS</code> to HTTPS, disables redirects, and validates
   every directory name it writes. Those are the same instincts this repository's
   tools apply, written years apart.</p>

<div class="note">
  <strong>Provenance was left alone.</strong> When the script was given a PHP
  8.1 floor and a header note saying it is superseded, the description and the
  AI-involvement credit were not touched. A status note is not a reason to
  rewrite history, and <code>archived/patches/</code> still has to apply.
</div>
<?php
$body = ob_get_clean();

vf_render($section, $file, $title, $description, $body, $crumbs);
