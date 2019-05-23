<?php
require_once(__DIR__."/lib/autoload.php");

$posts = $auth->getAllPosts();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$title = "Startseite";
require_once(__DIR__."/inc/head.php");
?>
<body>
  <?php require_once(__DIR__."/inc/nav.php"); ?>
  <section class="main">
    <h2>Beiträge</h2>
    <?php
    if ($posts) {
    foreach($posts as $post) {
    ?>
    <article class="card">
    <header>
      <h2><a href="/post.php?view=<?= $post['id'] ?>"><?= $post['title'] ?></a></h2>
    </header>
      <p>
        <?= substr($post['text'], 0, 512) ?><?= (substr($post['text'], 0, 512) !== $post['text']) ? '... <br><a href="/post.php?view='.$post["id"].'">Weiterlesen...</a>' : '' ?>
      </p>
    <footer>
      <p><small>von <?= $post['user'] ?> am <?= date('d.m.Y H:i', $post['created_at']) ?></small></p>
    </footer>
    </article>
    <?php
    }
    } else {
      echo 'Noch kein Beitrag vorhanden. :(';
    }
    ?>
  </section>
</body>
</html>
