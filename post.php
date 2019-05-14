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

if (isset($_GET['view'])) {
  $result = $auth->getPost($_GET['view']);
  if(!$result) {
    array_push($errors, '404 Not Found');
  } else {
    $action = 'view';
    $data = $result;
  }
}
else if (isset($_GET['new'])) {
  $action = 'new';
}
else if (isset($_GET['edit'])) {
  $action = 'edit';
} else {
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

$title = "";
$text = "";

if ($action == 'edit' && isset($_POST['edit'])) {
  $result = $auth->editPost($_GET['edit'], $_POST['title'], $_POST['text']);

  if (is_array($result)) {
    $errors = $result;
  } else {
    $success = true;
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

if (isset($_POST['login'])) {
  $login = $auth->login($_POST['user'], $_POST['password']);
  if (is_array($login)) {
    $errors = $login;
  } else {
    header('Location: /');
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Neuer Beitrag - <?= (defined("SITE_TITLE")) ? SITE_TITLE : 'A Bloggr Site' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" type="text/css" media="screen" href="/css/main.css">
</head>
<body>
  <div>
    <a href="/">Home</a>
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
    <h2>Titel: <?= $data['title'] ?></h2>
    <p>Text: <?= nl2br($data['text']) ?></p>
    <p>Author: <?= $data['user'] ?></p>
    <?php
    endif;

    if ($action == 'new'):
    ?>
    <h2>Neuer Beitrag</h2>
    <form action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="post" class="clearfix">
      <label for="title">Titel</label>
      <input type="text" name="title" id="title" value="<?= (isset($_POST['title'])) ? htmlspecialchars($_POST['title']) : ''; ?>"><br>
      <label for="text">Text</label>
      <textarea rows="4" cols="50" name="text" id="text"><?= (isset($_POST['text'])) ? htmlspecialchars($_POST['text']) : ''; ?></textarea>
      <input type="submit" name="new" value="new">
    </form>
    <?php
    endif;

    if ($action == 'edit' && (count($errors) <= 0)):
    ?>
    <h2>Beitrag Bearbeiten</h2>
    <form action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="post" class="clearfix">
      <label for="title">Titel</label>
      <input type="text" name="title" id="title" value="<?= (isset($data['title'])) ? $data['title'] : $title; ?>"><br>
      <label for="text">Text</label>
      <textarea rows="4" cols="50" name="text" id="text"><?= (isset($data['text'])) ? $data['text'] : $text; ?></textarea>
      <input type="submit" name="edit" value="edit">
    </form>
    <?php
    endif;
    ?>
  </div>
</body>
</html>