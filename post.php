<?php
include_once(__DIR__."/lib/autoload.php");

if (isset($_GET['new']) || isset($_GET['edit']) || isset($_POST['new']) || isset($_POST['edit'])) {
  if (!$auth->hasRole([ \Bloggr\Roles::ADMIN, \Bloggr\Roles::AUTHOR ])) {
    header('Location: /');
    die();
  }
}

$errors = [];
$action = '';
$data = [];
$success = false;
$title = "404 Not Found";

if (isset($_GET['view'])) {
  if (isset($_POST['comment'])) {
    $result = $auth->commentPost($_GET['view'], $_POST['comment']);
    if (is_array($result)) {
      $errors = $result;
    }
  }
  $result = $auth->getPost($_GET['view']);
  $result_comments = $auth->getPostComments($_GET['view']);
  if(!$result) {
    array_push($errors, '404 Not Found');
  } else {
    $title = $result['title'];
    $action = 'view';
    $data = $result;
  }
}
else if (isset($_GET['new'])) {
  $action = 'new';
  $title = "Neuer Beitrag";
}
else if (isset($_GET['edit'])) {
  $action = 'edit';
  $title = "Beitrag bearbeitem";
}
else {
  array_push($errors, '404 Not Found');
}

if ($action == 'new' && isset($_POST['new'])) {
  $result = $auth->newPost($_POST['title'], $_POST['text']);

  if (is_array($result)) {
    $errors = $result;
  } else {
    header("Location: /post.php?view=".$result);
  }
}

$ptitle = "";
$text = "";

if ($action == 'edit' && isset($_POST['edit'])) {
  $result = $auth->editPost($_GET['edit'], $_POST['title'], $_POST['text']);

  if (is_array($result)) {
    $errors = $result;
  } else {
    $success = true;
  }
}

if ($action == 'edit' && isset($_POST['delete'])) {
  $result = $auth->removePost($_GET['edit']);

  if (is_array($result)) {
    $errors = $result;
  } else {
    header("Location: /");
  }
}

if ($action == 'edit') {
  $result = $auth->getPost($_GET['edit']);
  if(!$result) {
    array_push($errors, '404 Not Found');
  } else {
    $data = $result;
  }
}
?>
<!DOCTYPE html>
<html>
<?php
require_once(__DIR__."/inc/head.php");
?>
<body>
  <?php require_once(__DIR__."/inc/nav.php"); ?>
  <section class="main">
    <?php
    if ($action == 'edit' && (count($errors) <= 0)) {
      echo '<a href="/post.php?view='.$data["id"].'">Zurück</a>';
    } else {
      echo '<a href="/">Zurück</a>';
    }
    ?>
    <?php
    foreach ($errors as $key=>$value):
    ?>
    <span style="color: red;">
      <?= $value ?>
      </span><br>
    <?php
    endforeach;

    if($success == true) {
      echo '<span style="color: green;">Post bearbeitet!</span><br>';
    }
    if ($action == 'view'):
    ?>
    <div>
    <header><h2><?= $data['title'] ?></h2></header>
    <section>
    <p><?= nl2br($data['text']) ?></p>
    </section>
    <footer><small>von <?= $data['user'] ?> am <?= date('d.m.Y H:i', $data['created_at']) ?><br>
    <?php
    if($data['updated_by']):
    ?>
    zuletzt bearbeitet: <?= date('d.m.Y H:i',$data['updated_at']).' von '.$data['updated_by'] ?>
    <?php
    endif;
    if ($auth->canEditPost($data["id"]) == true) echo '<br><a href="post.php?edit='.$data["id"].'">Edit Post</a>';
    echo '</small></footer>';
    ?>
    </div>
    <?php
    if ($auth->isLoggedIn()) {
    ?>
    <p>
      <form action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="post">
        <textarea name="comment" id="comment" cols="30" rows="2"></textarea>
        <input type="submit" value="Comment">
      </form>
    </p>
    <?php
    }
    echo '<h3>Kommentare</h3>';
    if(is_array($result_comments)) {
    foreach($result_comments as $comment) {
    ?>
    <article class="card">
      <section>
      <b><?= $comment['user']; ?></b> <small>am <?= date('H:i d.m.Y',$comment['created_at']) ?></small>
      </section>
      <footer>
      <?= $comment['comment']; ?>
      </footer>
    </article>
    <?php
    }
    }

    endif;

    if ($action == 'new'):
    ?>
    <h2>Neuer Beitrag</h2>
    <form action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="post" class="clearfix">
      <label for="title">Titel</label>
      <input type="text" name="title" id="title" value="<?= (isset($_POST['title'])) ? htmlspecialchars($_POST['title']) : ''; ?>"><br>
      <label for="text">Text</label>
      <textarea rows="4" cols="50" name="text" id="text"><?= (isset($_POST['text'])) ? htmlspecialchars($_POST['text']) : ''; ?></textarea>
      <input type="submit" name="new" value="Erstellen">
    </form>
    <?php
    endif;

    if ($action == 'edit'):
    ?>
    <h2>Beitrag Bearbeiten</h2>
    <form action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="post" class="clearfix">
      <label for="title">Titel</label>
      <input type="text" name="title" id="title" value="<?= (isset($data['title'])) ? $data['title'] : $ptitle; ?>"><br>
      <label for="text">Text</label>
      <textarea rows="4" cols="50" name="text" id="text"><?= (isset($data['text'])) ? $data['text'] : $text; ?></textarea>
      <input type="submit" name="edit" value="Speichern">
      <label for="modal_1" class="button warning">Löschen</label>

      <div class="modal">
        <input id="modal_1" type="checkbox" />
        <label for="modal_1" class="overlay"></label>
        <article>
          <header>
            <h3>Beitrag wirklich löschen?</h3>
            <label for="modal_1" class="close">&times;</label>
          </header>
          <section class="content">
            Sicher dass der Beitrag gelöscht werden soll? Das löschen eines Beitrags löscht alle seine Kommentare! <b>Die Daten sind nicht wiederherstellbar!</b>
          </section>
          <footer>
            <input class="dangerous warning" type="submit" name="delete" value="Trotzdem löschen">
            <label for="modal_1" class="button">
              Abbrechen
            </label>
          </footer>
        </article>
      </div>
    </form>
    <?php
    endif;
    ?>
  </section>
</body>
</html>
