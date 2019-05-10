<?php
require_once(__DIR__."/lib/autoload.php");

$viewSubmit = false;
$error = [];
$successArray = [];
$success = false;

$siteTitle = "";
$dbHost = "";
$dbName = "";
$dbUser = "";
$dbPass = "";
$user = "";
$email = "";
$pass = "";

if (isset($_POST['check']) || isset($_POST['submit'])) {
  $siteTitle = trim($_POST['SITE_TITLE']);
  $dbHost = htmlspecialchars($_POST['DB_HOST']);
  $dbName = htmlspecialchars($_POST['DB_NAME']);
  $dbUser = htmlspecialchars($_POST['DB_USER']);
  $dbPass = $_POST['DB_PASS'];

  $user = htmlspecialchars($_POST['ADMIN_USER']);
  $email =htmlspecialchars($_POST['ADMIN_EMAIL']);
  $pass = $_POST['ADMIN_PASS'];

  if($siteTitle == "" || strlen($siteTitle) < 1) {
    array_push($error, "Please enter a site title");
  }
  if($dbHost == "") {
    array_push($error, "Please enter a hostname");
  }
  if($dbName == "") {
    array_push($error, "Please enter a database");
  }

  if (isset($_POST['submit'])) {
    $config = fopen(__DIR__."/lib/config.php", "w");

    fwrite($config, "<?php\n");
    fwrite($config, "define('SITE_TITLE', '".$siteTitle."');\n\n");

    fwrite($config, "define('DB_HOST', '".$dbHost."');\n");
    fwrite($config, "define('DB_NAME', '".$dbName."');\n");
    fwrite($config, "define('DB_USER', '".$dbUser."');\n");
    fwrite($config, "define('DB_PASS', '".$dbPass."');\n\n");

    fwrite($config, "define('ADMIN_USER', '".$user."');\n");
    fwrite($config, "define('ADMIN_EMAIL', '".$email."');\n");

    header('Location: /');
    die();
  }
}

if (isset($_POST['check'])) {
  try {
    if(count($error) <= 0) {
      $pdo = new \PDO('mysql:dbname='.$dbName.';host='.$dbHost.';charset=utf8mb4', $dbUser, $dbPass);
      $sql = file_get_contents(__DIR__."/lib/sql/db.sql");
      try {
        $pdo->exec($sql);
        array_push($successArray, 'Database setup complete...');
      } catch (PDOException $ex) {
        array_push($error, $ex->getMessage());
      }
    }

    if(count($error) <= 0) {
      $auth = new \Bloggr\Auth($pdo);

      $register = $auth->register($user, $email, $pass, \Bloggr\Roles::ADMIN);
      if (is_array($register)) {
        foreach($register as $regErr) {
          array_push($error, $regErr);
        }
      } else {
        array_push($successArray, 'Admin setup complete...');
        $success = true;
        $viewSubmit = true;
      }
    }
  } catch (PDOException $ex) {
    array_push($error, $ex->getMessage());
  }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Setup - Bloggr</title>
</head>
<body>
  <p>
    <?php
      foreach($error as $err) {
        echo '<span style="color: red;">'.$err.'</span><br>';
      }
      foreach($successArray as $succ) {
        echo '<span style="color: green;">'.$succ.'</span><br>';
      }
    ?>
  </p>
  <form action="" method="post">
    <div <?= ($success) ? 'style="display: none;' : '' ?>>
      <h2>Site info</h2>
      <p>
        <label for="SITE_TITLE">Title</label>
        <input type="text" name="SITE_TITLE" id="SITE_TITLE" placeholder="A Bloggr Site" value="<?= $siteTitle ?>" >
      </p>
      <h2>Database info</h2>
      <p>
        <label for="DB_HOST">Host</label>
        <input type="text" name="DB_HOST" id="DB_HOST" placeholder="localhost" value="<?= $dbHost ?>" >
      </p>
      <p>
        <label for="DB_NAME">Database</label>
        <input type="text" name="DB_NAME" id="DB_NAME" placeholder="bloggr" value="<?= $dbName ?>" >
      </p>
      <p>
        <label for="DB_USER">Username</label>
        <input type="text" name="DB_USER" id="DB_USER" placeholder="bloggr" value="<?= $dbUser ?>" >
      </p>
      <p>
        <label for="DB_PASS">Password</label>
        <input type="password" name="DB_PASS" id="DB_PASS" placeholder="s3cur3" value="<?= $dbPass ?>" >
      </p>
      <br>
      <h2>Create administrator</h2>
      <p>
        <label for="ADMIN_USER">Username</label>
        <input type="text" name="ADMIN_USER" id="ADMIN_USER" placeholder="admin" value="<?= $user ?>" >
      </p>
      <p>
        <label for="ADMIN_EMAIL">E-Mail</label>
        <input type="text" name="ADMIN_EMAIL" id="ADMIN_EMAIL" placeholder="bloggr" value="<?= $email ?>" >
      </p>
      <p>
        <label for="ADMIN_PASS">Password</label>
        <input type="password" name="ADMIN_PASS" id="ADMIN_PASS" placeholder="s3cur3" value="<?= $pass ?>" >
      </p>
    </div>
    <p>
      
      <?= (!$viewSubmit) ? '<input type="submit" value="Check" name="check">' : '' ?>
      <?= ($viewSubmit) ? '<input type="submit" value="Submit" name="submit">' : '' ?>
    </p>
  </form>
</body>
</html>
