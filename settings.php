<?php
include_once(__DIR__."/lib/autoload.php");

if (!$auth->isLoggedIn()) {
  header('Location: /');
  die();
}

$errors = [];
$action = '';
$data = [];
$view = false;
$success = false;


if(isset($_POST['update'])) {
  $result = $auth->updatePassword($_POST['opassword'], $_POST['npassword'], $_POST['rpassword']);

  if (is_array($result)) {
    $errors = $result;
  } else {
    $success = true;
  }
}

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
  <div>
    <a href="/">Home</a>
    <h2>Change Password</h2>
    <?php
    foreach ($errors as $key=>$value):
    ?>
    <span style="color: red;">
      <?= $value ?>
      </span><br>
    <?php
    endforeach;

    if($success == true) {
      echo '<span style="color: green;">Passwort aktualisiert!</span><br>';
    }
    ?>
    <form action="/settings.php" method="post">
      <label for="opassword">Altes Passwort</label>
      <input type="password" name="opassword" id="opassword"><br>
      <label for="npassword">Passwort</label>
      <input type="password" name="npassword" id="npassword"><br>
      <label for="rpassword">Passwort Wiederholen</label>
      <input type="password" name="rpassword" id="rpassword"><br>
      <input type="submit" name="update" value="Update">
    </form>
  </div>
</body>
</html>
