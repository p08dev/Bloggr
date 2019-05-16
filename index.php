<?php
require_once(__DIR__."/lib/autoload.php");

$posts = $auth->getAllPosts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Home - <?= (defined("SITE_TITLE")) ? SITE_TITLE : 'A Bloggr Site' ?></title>
</head>
<body>
  <?php
  if ($auth->hasRole([ \Bloggr\Roles::ADMIN ])) {
  ?>
  <p>
    <a href="/users.php">Users</a>
  </p>
  <?php
  }
  if ($auth->hasRole([ \Bloggr\Roles::ADMIN, \Bloggr\Roles::AUTHOR ])) {
  ?>
  <p>
    <a href="/post.php?new">Neuer Beitrag</a>
  </p>
  <?php
  }
  if (!$auth->isLoggedIn()) {
  ?>
  <p>
    <a href="/login.php">Login</a>
  </p>
  <p>
    <a href="/register.php">Registrieren</a>
  </p>
  <?php
  } else {
  ?>
  <p>
    <a href="/settings.php">Einstellungen</a>
  </p>
  <p>
    <a href="/logout.php">Logout</a>
  </p>
  <?php
  }
  ?>
  <div>
    <?php
    if ($posts) {
    foreach($posts as $post) {
    ?>
    <p>
      <h2>Titel: <a href="/post.php?view=<?= $post['id'] ?>"><?= $post['title'] ?></a></h2>
      <p>
        Text:
        <?= substr($post['text'], 0, 512) ?><?= (substr($post['text'], 0, 512) !== $post['text']) ? '... <br><a href="/post.php?view='.$post["id"].'">Weiterlesen...</a>' : '' ?>
      </p>
      <p>Author: <?= $post['user'] ?></p>
    </p>
    <?php
    }
    } else {
      echo 'Noch kein Beitrag vorhanden. :(';
    }
    ?>
  </div>
</body>
</html>
