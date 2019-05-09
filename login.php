<?php
include_once(__DIR__."/lib/autoload.php");
// print_r($auth->register('Furentes', 'furentes@furentes.de', '123456789'));
// print_r($auth->login('Furentes', '123456789'));
// echo $auth->isLoggedIn();
// echo $auth->logout();
if ($auth->isLoggedIn()) {
  header('Location: /');
}
$errors = [];
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
  <title>Login - <?= (defined("SITE_TITLE")) ? SITE_TITLE : 'A Bloggr Site' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" type="text/css" media="screen" href="/css/main.css">
</head>
<body>
  <div>
    <h2>Login</h2>

    <?php
    foreach ($errors as $key=>$value):
    ?>
    <span style="color: red;">
      <?= $value ?>
      </span><br>
    <?php
    endforeach;
    ?>

    <form action="/login.php" method="post" class="clearfix">
      <label for="user">Username/E-Mail</label>
      <input type="text" name="user" id="user" value="<?= (isset($_POST['user'])) ? $_POST['user'] : ''; ?>">
      <label for="password">Password</label>
      <input type="password" name="password" id="password"">
      <input type="submit" name="login" value="login" class="float-right">
    </form>
  </div>
</body>
</html>