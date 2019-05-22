<?php
include_once(__DIR__."/lib/autoload.php");

if ($auth->isLoggedIn()) {
  header('Location: /');
}
$errors = [];
$success = false;
if (isset($_POST['register'])) {
  if ($_POST['password'] !== $_POST['password2']) {
    array_push($errors, 'Passwörter sind nicht gleich!');
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
<?php
$title = "Account erstellen";
require_once(__DIR__."/inc/head.php");
?>
<body>
  <?php require_once(__DIR__."/inc/nav.php"); ?>
  <section class="main">
    <h2>Register</h2>

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
      <input type="text" name="username" id="username" value="<?= (isset($_POST['username'])) ? htmlspecialchars($_POST['username']) : ''; ?>"><br>
      <label for="email">E-Mail</label>
      <input type="text" name="email" id="email" value="<?= (isset($_POST['email'])) ? htmlspecialchars($_POST['email']) : ''; ?>"><br>
      <label for="password">Password</label>
      <input type="password" name="password" id="password""><br>
      <label for="password2">Password wiederholen</label>
      <input type="password" name="password2" id="password2""><br>
      <input type="submit" name="register" value="Registrieren">
    </form>
    <?php } ?>
  </section>
</body>
</html>
