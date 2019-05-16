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

}

$users = $auth->getAllUsers();
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
    <h2>Users</h2>
    <?php
    if($view) {
      echo '<a href="/users.php">Back</a><br>';
      $found = false;
      foreach ($users as $key => $value) {
        if($value['id'] == $view) {
          $found = true;
          
          ?>
    <form action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="post">
      <label for="username"><?= $value['username'] ?></label><br>
      <label for="email"><?= $value['email'] ?></label><br>
      <label for="roles_mask">Role</label>
      <select name="role" id="role">
        <option value="0" <?= ($value['roles_mask'] == 0) ? 'selected' : '' ?>>Gast</option>
        <option value="1" <?= ($value['roles_mask'] == 1) ? 'selected' : '' ?>>Admin</option>
        <option value="2" <?= ($value['roles_mask'] == 2) ? 'selected' : '' ?>>Author</option>
      </select><br>
      <input type="submit" name="update" value="Update">
    </form>
          <?php
        }
      }
      if (!$found) echo '404 Not Found';
    } else {
      $count = 0;
      foreach ($users as $key => $value) {
        echo '<a href="/users.php?view='.$value['id'].'">'.$value['id'].' - '.$value['username'].'</a><br>';
        $count++;
      }
    }
    ?>
  </div>
</body>
</html>
