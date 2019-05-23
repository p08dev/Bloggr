<?php
include_once(__DIR__."/lib/autoload.php");

if (!$auth->hasRole([ \Bloggr\Roles::ADMIN ])) {
  header('Location: /');
  die();
}

$errors = [];
$action = '';
$data = [];
$view = false;


if(isset($_GET['view'])) {
  if (!empty($_GET['view']) && \is_numeric($_GET['view'])) {
    $view = $_GET['view'];
  }

  if(isset($_POST['update'])) {
    $update = $auth->updateUserRole($view, $_POST['role']);
  }

  if(isset($_POST['delete'])) {
    $delete = $auth->deleteUser($view);

    if (is_array($delete)) {
      $errors = $delete;
    } else {
      header("Location: /users.php");
    }
  }
}

$users = $auth->getAllUsers();
?>
<!DOCTYPE html>
<html lang="en">
<?php
$title = "Benutzer";
require_once(__DIR__."/inc/head.php");
?>
<body>
  <?php require_once(__DIR__."/inc/nav.php"); ?>
  <section class="main">
    <?= ($view) ? '<a href="/users.php">Zurück</a>' : '<a href="/">Zurück</a>' ?>
    <h2>Benutzer</h2>
    <?php
    foreach ($errors as $key=>$value):
    ?>
    <span style="color: red;">
      <?= $value ?>
    </span><br>
    <?php
    endforeach;
    if($view) {
      $found = false;
      foreach ($users as $key => $value) {
        if($value['id'] == $view) {
          $found = true;
          
          ?>
    <form action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="post">
      <label for="id"><b>ID:</b> <?= $value['id'] ?></label><br>
      <label for="username"><b>Benutzername:</b> <?= $value['username'] ?></label><br>
      <label for="email"><b>E-Mail:</b> <?= $value['email'] ?></label><br>
      <label for="registered"><b>Mitglied seit:</b> <?= date('d.m.Y H:i', $value['registered']) ?></label><br>
      <label for="last_login"><b>Letzter login:</b> <?= date('d.m.Y H:i', $value['last_login']) ?></label><br>
      <label for="roles_mask"><b>Rolle</b></label>
      <select name="role" id="role">
        <option value="0" <?= ($value['roles_mask'] == 0) ? 'selected' : '' ?>>Gast</option>
        <option value="1" <?= ($value['roles_mask'] == 1) ? 'selected' : '' ?>>Admin</option>
        <option value="2" <?= ($value['roles_mask'] == 2) ? 'selected' : '' ?>>Author</option>
      </select><br>
      <input type="submit" name="update" value="Speichern">
      <label for="modal_1" class="button warning">Löschen</label>

      <div class="modal">
        <input id="modal_1" type="checkbox" />
        <label for="modal_1" class="overlay"></label>
        <article>
          <header>
            <h3>Benutzer wirklich löschen?</h3>
            <label for="modal_1" class="close">&times;</label>
          </header>
          <section class="content">
            Sicher dass der Benutzer gelöscht werden soll? Das löschen eines Benutzers löscht alle seine Beiträge und Kommentare! <b>Die Daten sind nicht wiederherstellbar!</b>
          </section>
          <footer>
            <input class="error dangerous" type="submit" name="delete" value="Trotzdem löschen">
            <label for="modal_1" class="button">
              Abbrechen
            </label>
          </footer>
        </article>
      </div>
    </form>
          <?php
        }
      }
      if (!$found) echo '404 Not Found';
    } else {
      $count = 0;
      foreach ($users as $key => $value) {
        echo '<a href="/users.php?view='.$value['id'].'">['.$value['id'].'] '.$value['username'].'</a><br>';
        $count++;
      }
    }
    ?>
  </section>
</body>
</html>
