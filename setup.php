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
    array_push($error, "Bitte gib einen Seitentitel ein.");
  }
  if($dbHost == "") {
    array_push($error, "Bitte gib einen Hostnamen ein.");
  }
  if($dbName == "") {
    array_push($error, "Bitte gib eine Datenbank an.");
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
        array_push($successArray, 'Tabellen erstellt...!');
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
        array_push($successArray, 'Administrator Account erstellt...!');
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
  <link rel="stylesheet" href="/css/picnic.min.css">
  <link rel="stylesheet" href="/css/main.css">
</head>
<body>
  <nav class="demo">
    <a href="/setup.php" class="brand">
      <span>Willkommen zu Bloggr!</span>
    </a>
  </nav>
  <section class="main">
  <h2>Seiteninformationen</h2>
  <article class="card">
  <section>
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
    <div <?= ($success) ? 'style="display: none;"' : '' ?>>
      <p>
        <h2>Seitentitel</h2>
        <input type="text" name="SITE_TITLE" id="SITE_TITLE" placeholder="z.B. Mein Blog" value="<?= $siteTitle ?>" >
      </p>
      <h2>Datenbankinformationen</h2>
      <p>
        <label for="DB_HOST">Host</label>
        <input type="text" name="DB_HOST" id="DB_HOST" placeholder="z.B. localhost" value="<?= $dbHost ?>" >
      </p>
      <p>
        <label for="DB_NAME">Datenbank</label>
        <input type="text" name="DB_NAME" id="DB_NAME" placeholder="z.B. bloggr" value="<?= $dbName ?>" >
      </p>
      <p>
        <label for="DB_USER">Benutzername</label>
        <input type="text" name="DB_USER" id="DB_USER" placeholder="z.B. root" value="<?= $dbUser ?>" >
      </p>
      <p>
        <label for="DB_PASS">Passwort (min. 8)</label>
        <input type="password" name="DB_PASS" id="DB_PASS" placeholder="" value="<?= $dbPass ?>" >
      </p>
      <br>
      <h2>Administrator Konto</h2>
      <p>
        <label for="ADMIN_USER">Benutzername</label>
        <input type="text" name="ADMIN_USER" id="ADMIN_USER" placeholder="z.B. admin" value="<?= $user ?>" >
      </p>
      <p>
        <label for="ADMIN_EMAIL">E-Mail</label>
        <input type="text" name="ADMIN_EMAIL" id="ADMIN_EMAIL" placeholder="z.B. blog@example.com" value="<?= $email ?>" >
      </p>
      <p>
        <label for="ADMIN_PASS">Passwort </label>
        <input type="password" name="ADMIN_PASS" id="ADMIN_PASS" placeholder="" value="<?= $pass ?>" >
      </p>
    </div>
    <p>
      
      <?= (!$viewSubmit) ? '<input type="submit" value="Los!" name="check">' : '' ?>
      <?= ($viewSubmit) ? '<input type="submit" value="Abschließen" name="submit">' : '' ?>
    </p>
  </form>
  </section>
  </article>
  </section>
</body>
</html>
