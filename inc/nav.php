
<nav class="demo">
  <a href="/" class="brand">
    <span><?= ((defined("SITE_TITLE")) ? SITE_TITLE : "A Bloggr Site") ?></span>
  </a>

  <!-- responsive-->
  <input id="bmenub" type="checkbox" class="show">
  <label for="bmenub" class="burger pseudo button">&#8801;</label>

  <div class="menu">
    <a href="/" class="pseudo button"">Startseite</a>
    <?php
    if ($auth->hasRole([ \Bloggr\Roles::ADMIN ])){
    ?>
    <a href="/users.php" class="pseudo button">Benutzer</a>
    <a href="/post.php?new" class="pseudo button">Neuer Post</a>
    <?php
    } elseif($auth->hasRole([ \Bloggr\Roles::AUTHOR ])) {
    ?>
    <a href="/post.php?new" class="pseudo button">Neuer Post</a>
    <?php
    }
    ?>
    <?php
    if (!$auth->isLoggedIn()){
    ?>
    <a href="/login.php" class="pseudo button">Login</a>
    <a href="/register.php" class="button">Registrieren</a>
    <?php
    } else {
    ?>
    <a href="/settings.php" class="pseudo button">Einstellungen</a>
    <a href="/logout.php" class="button">Logout</a>
    <?php
    }
    ?>
  </div>
</nav>
