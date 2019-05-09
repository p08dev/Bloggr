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
$success = false;
if (isset($_POST['register'])) {
  if ($_POST['password'] !== $_POST['password2']) {
    array_push($errors, 'Passwörter sind nicht gleich');
  } else {
    $register = $auth->register($_POST['username'], $_POST['email'], $_POST['password']);
    if (is_array($register)) {
      $errors = $register;
    } else {
      $success = true;
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Register - <?= (defined("SITE_TITLE")) ? SITE_TITLE : 'A Bloggr Site' ?></title>
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

    if($success == true) {
      echo '<span style="color: green;">Account erstellt!</span><br>';
    } else {
    ?>

    <form action="/register.php" method="post" class="clearfix">
      <label for="username">Username</label>
      <input type="text" name="username" id="username" value="<?= (isset($_POST['username'])) ? $_POST['username'] : ''; ?>"><br>
      <label for="email">E-Mail</label>
      <input type="text" name="email" id="email" value="<?= (isset($_POST['email'])) ? $_POST['email'] : ''; ?>"><br>
      <label for="password">Password</label>
      <input type="password" name="password" id="password""><br>
      <label for="password2">Password wiederholen</label>
      <input type="password" name="password2" id="password2""><br>
      <input type="submit" name="register" value="Registrieren">
    </form>
    <?php } ?>
  </div>
</body>
</html>
