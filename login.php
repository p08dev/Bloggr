<?php
include_once(__DIR__."/lib/autoload.php");

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
<?php
$title = "Login";
require_once(__DIR__."/inc/head.php");
?>
<body>
  <?php require_once(__DIR__."/inc/nav.php"); ?>
  <section class="main">
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
      <input type="text" name="user" id="user" value="<?= (isset($_POST['user'])) ? htmlspecialchars($_POST['user']) : ''; ?>">
      <label for="password">Password</label>
      <input type="password" name="password" id="password"">
      <input type="submit" name="login" value="Login" class="float-right">
    </form>
  </section>
</body>
</html>