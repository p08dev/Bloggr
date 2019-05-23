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
<?php
$title = "Einstellungen";
require_once(__DIR__."/inc/head.php");
?>
<body>
  <?php require_once(__DIR__."/inc/nav.php"); ?>
  <section class="main">
    <a href="/">Zurück</a>
    <h2>Einstellungen</h2>
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
  </section>
</body>
</html>
