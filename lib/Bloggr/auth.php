<?php
namespace Bloggr;
class Auth
{
  protected $pdo;
  function __construct($pdo)
  {
    session_start();
    $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    $this->pdo = $pdo;
  }
  public function register($username, $email, $password, $role = 0) {
    $errors = array();
    $username = trim(filter_var($username, FILTER_SANITIZE_STRING));
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    $password = filter_var($password, FILTER_SANITIZE_STRING);
    $timestamp = time();
    if (preg_replace('/\s+/', '', $username) !== $username) {
      array_push($errors, 'Dein Benutzername darf keine Leerzeichen enthalten!');
    }
    if (strlen(trim($username)) < 3) {
      array_push($errors, 'Der Benutzername ist zu kurz! Min. 3');
    }
    if (strlen(trim($username)) > 16) {
      array_push($errors, 'Der Benutzername ist zu lang! Max. 16');
    }
    if (!$email) {
      array_push($errors, 'Ungültige E-Mail!');
    }
    if (strlen(trim($password)) < 8) {
      array_push($errors, 'Passwort zu kurz! Min 8');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    try {
      $s = $this->pdo->prepare("SELECT username, email FROM users WHERE username = :username OR email = :email;");
      $s->execute(array(
        ':username' => $username,
        ':email' => $email
      ));
      while ($row = $s->fetch()) {
        if ($row['username'] === $username) {
          array_push($errors, 'Benutzer existiert bereits!');
        }
        if ($row['email'] === $email) {
          array_push($errors, 'E-Mail existiert bereits!');
        }
      }
    } catch (\PDOException $e) {
      array_push($errors, 'Da ist etwas schiefgelaufen!');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    try {
      $s = $this->pdo->prepare("INSERT INTO users (username, email, password, registered, roles_mask) VALUES(:username, :email, :password, :registered, :roles_mask);");
      $r = $s->execute(array(
        ':username' => $username,
        ':email' => $email,
        ':password' => password_hash($password, PASSWORD_DEFAULT),
        ':registered' => $timestamp,
        ':roles_mask' => $role
      ));
      if(!$r) {
        array_push($errors, 'Da ist etwas schiefgelaufen!');
      }
    } catch (\PDOException $e) {
      array_push($errors, 'Da ist etwas schiefgelaufen!');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    return true;
  }
  public function login($user, $password) {
    $errors = array();
    $password = filter_var($password, FILTER_SANITIZE_STRING);
    $timestamp = time();
    if (!$user || $user === '' || preg_replace('/\s+/', '', $user) !== $user) {
      array_push($errors, 'Kein Benutzername oder E-Mail angegeben!');
    }
    if (!$password || $password === '') {
      array_push($errors, 'Kein Passwort angegeben!');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    $userId = 0;
    try {
      $s = $this->pdo->prepare("SELECT id, username, email, password FROM users WHERE username = :user OR email = :user LIMIT 1;");
      $s->execute(array(
        ':user' => $user
      ));
      if ($s->rowCount() <= 0) {
        array_push($errors, 'Falscher Benutzername/E-Mail oder Passwort!');
      } else {
        while ($row = $s->fetch()) {
          if (!password_verify($password, $row['password'])) {
            array_push($errors, 'Falscher Benutzername/E-Mail oder Passwort!');
          }
          $userId = $row['id'];
        }
      }
    } catch (\PDOException $e) {
      array_push($errors, 'Da ist etwas schiefgelaufen!');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    try {
      $s = $this->pdo->prepare("UPDATE users SET last_login = :lastlogin WHERE id = :id;");
      $r = $s->execute(array(
        ':lastlogin' => $timestamp,
        ':id' => $userId
      ));
      if(!$r) {
        array_push($errors, 'Da ist etwas schiefgelaufen!');
      }
    } catch (\PDOException $e) {
      array_push($errors, 'Da ist etwas schiefgelaufen!');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    $_SESSION['id'] = $userId;
    return true;
  }
  public function isLoggedIn() {
    return isset($_SESSION['id']);
  }
  public function getId() {
    if (!$this->isLoggedIn()) return false;
    return $_SESSION['id'];
  }
  public function logout() {
    $_SESSION['id'] = '';
    unset($_SESSION['id']);
    session_unset();
    return true;
  }
  public function getUsernameById($id) {
    if (empty($id) || !\is_numeric($id)) {
      return false;
    }

    try {
      $s = $this->pdo->prepare("SELECT username FROM users WHERE id = :id LIMIT 1;");
      $s->execute(array(
        ':id' => $id,
      ));

      if ($s->rowCount() <= 0) {
        return false;
      }

      while ($row = $s->fetch()) {
        return $row['username'];
      }

      return false;
    } catch (\PDOException $e) {
      return false;
    }
  }
  public function hasRole($role) {
    if (empty($role) || !\is_numeric($role) && !\is_array($role)) {
      return false;
    }

    if (empty($_SESSION['id'])) return false;

    try {
      $s = $this->pdo->prepare("SELECT roles_mask FROM users WHERE id = :id LIMIT 1;");
      $s->execute(array(
        ':id' => $_SESSION['id']
      ));

      if ($s->rowCount() <= 0) {
        return false;
      }

      while ($row = $s->fetch()) {
        $mask = $row['roles_mask'];
      }

      if (\is_array($role)) {
        foreach ($role as $key => $value) {
          if (($mask & $value) === $value) {
            return true;
          }
        }
      }

      return ($mask & $role) === $role;
    } catch (\PDOException $e) {
      return false;
    }
  }
  public function newPost($title, $text) {
    if (!$this->isLoggedIn()) return false;

    $errors = array();
    $title = htmlspecialchars(trim(filter_var($title, FILTER_SANITIZE_STRING)));
    $text = htmlspecialchars(trim($text, FILTER_SANITIZE_STRING));
    $created_at = time();
    $id = [ 'Da ist etwas schiefgelaufen!' ];

    if (strlen($title) < 3) {
      array_push($errors, 'Titel ist zu kurz! Min. 3');
    }
    if (strlen($title) > 64) {
      array_push($errors, 'Titel ist zu lang! Max. 64');
    }
    if (strlen($text) < 8) {
      array_push($errors, 'Text ist zu kurz! Min. 8');
    }
    if (strlen($text) > 12000000) {
      array_push($errors, 'Text ist zu lang! Max. 10M');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    try {
      $s = $this->pdo->prepare("INSERT INTO posts (user, title, text, created_at) VALUES(:user, :title, :text, :created_at);");
      $r = $s->execute(array(
        ':user' => $this->getId(),
        ':title' => $title,
        ':text' => $text,
        ':created_at' => $created_at,
      ));
      $id = $this->pdo->lastInsertId();
      if(!$r) {
        array_push($errors, 'Da ist etwas schiefgelaufen!');
      }
    } catch (\PDOException $e) {
      array_push($errors, 'Da ist etwas schiefgelaufen!');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    return $id;
  }
  public function canEditPost($id) {
    if (!$this->isLoggedIn()) return false;
    if (!$this->hasRole(\Bloggr\Roles::ADMIN)) {
      try {
        $s = $this->pdo->prepare("SELECT id FROM posts WHERE id = :id AND user = :user LIMIT 1;");
        $s->execute(array(
          ':id' => $id,
          ':user' => $this->getId(),
        ));
  
        if ($s->rowCount() <= 0) {
          return false;
        }

        return true;
      } catch (\PDOException $e) {
        return false;
      }
    }
    return true;
  }
  public function editPost($id, $title, $text) {
    if (!$this->isLoggedIn()) return false;
    if (!$this->canEditPost($id)) return false;

    $errors = array();
    $title = htmlspecialchars(trim(filter_var($title, FILTER_SANITIZE_STRING)));
    $text = htmlspecialchars(trim($text, FILTER_SANITIZE_STRING));
    $updated_at = time();

    try {
      $s = $this->pdo->prepare("SELECT posts.* FROM posts INNER JOIN users ON posts.user = users.id WHERE posts.id = :post LIMIT 1;");
      $s->execute(array(
        ':post' => $id,
      ));

      if ($s->rowCount() <= 0) {
        return false;
      }
    } catch (\PDOException $e) {
      array_push($errors, 'Da ist etwas schiefgelaufen!');
    }
    
    if (strlen($title) < 3) {
      array_push($errors, 'Titel ist zu kurz! Min. 3');
    }
    if (strlen($title) > 64) {
      array_push($errors, 'Titel ist zu lang! Max. 64');
    }
    if (strlen($text) < 8) {
      array_push($errors, 'Text ist zu kurz! Min. 8');
    }
    if (strlen($text) > 12000000) {
      array_push($errors, 'Text ist zu lang! Max. 10M');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    try {
      $s = $this->pdo->prepare("UPDATE posts SET title = :title, text = :text, updated_at = :updated_at, updated_by = :updated_by WHERE id = :id LIMIT 1;");
      $r = $s->execute(array(
        ':title' => $title,
        ':text' => $text,
        ':updated_at' => $updated_at,
        ':updated_by' => $this->getId(),
        ':id' => $id,
      ));
      if(!$r) {
        array_push($errors, 'Da ist etwas schiefgelaufen!');
      }
    } catch (\PDOException $e) {
      array_push($errors, 'Da ist etwas schiefgelaufen!');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    return true;
  }
  public function getPost($id) {
    if (empty($id) || !\is_numeric($id)) {
      return false;
    }

    try {
      $s = $this->pdo->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1;");
      $s->execute(array(
        ':id' => $id,
      ));

      if ($s->rowCount() <= 0) {
        return false;
      }

      while ($row = $s->fetch()) {
        $row['user'] = $this->getUsernameById($row['user']);
        if ($row['updated_by'] && $row['updated_by'] != 0) {
          $row['updated_by'] = $this->getUsernameById($row['updated_by']);
        }
        return $row;
      }

      return false;
    } catch (\PDOException $e) {
      return false;
    }
  }
  public function getAllPosts() {
    try {
      $posts = [];
      $sql = "SELECT * FROM posts ORDER BY id DESC";
      $result = $this->pdo->query($sql);

      if (!$result) {
        return false;
      }

      foreach ($result as $row) {
        $row['user'] = $this->getUsernameById($row['user']);
        array_push($posts, $row);
      }

      return $posts;
    } catch (\PDOException $e) {
      return $posts;
    }
  }
  public function commentPost($id, $comment) {
    if (empty($id) || !\is_numeric($id)) {
      return false;
    }
    if (!$this->isLoggedIn()) return false;

    $errors = array();
    $comment = htmlspecialchars(trim($comment, FILTER_SANITIZE_STRING));
    $created_at = time();
    if (strlen($comment) < 3) {
      array_push($errors, 'Text ist zu kurz! Min. 3');
    }
    if (strlen($comment) > 256) {
      array_push($errors, 'Text ist zu lang! Max. 256');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    try {
      $s = $this->pdo->prepare("INSERT INTO comments (post, user, comment, created_at) VALUES(:post, :user, :comment, :created_at);");
      $r = $s->execute(array(
        ':post' => $id,
        ':user' => $this->getId(),
        ':comment' => $comment,
        ':created_at' => $created_at,
      ));
      if(!$r) {
        array_push($errors, 'Da ist etwas schiefgelaufen!');
      }
      if (count($errors) > 0) {
        return $errors;
      }
      return true;
    } catch (\PDOException $e) {
      array_push($errors, 'Da ist etwas schiefgelaufen!');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    return false;
  }
  public function getPostComments($id) {
    if (empty($id) || !\is_numeric($id)) {
      return false;
    }

    try {
      $s = $this->pdo->prepare("SELECT * FROM comments WHERE post = :id ORDER BY id DESC;");
      $s->execute(array(
        ':id' => $id,
      ));

      if ($s->rowCount() <= 0) {
        return false;
      }
      $rows = [];

      while ($row = $s->fetch()) {
        $row['user'] = $this->getUsernameById($row['user']);
        array_push($rows, $row);
      }
      return $rows;

      return false;
    } catch (\PDOException $e) {
      return false;
    }
  }
  public function getAllUsers() {
    try {
      $users = [];
      $sql = "SELECT * FROM users ORDER BY id ASC";
      $result = $this->pdo->query($sql);

      if (!$result) {
        return false;
      }

      foreach ($result as $row) {
        array_push($users, $row);
      }

      return $users;
    } catch (\PDOException $e) {
      return $users;
    }
  }
  public function updateUserRole($id, $role = 0) {
    if (!$this->isLoggedIn()) return false;
    if (!$this->hasRole([ \Bloggr\Roles::ADMIN ])) {
      return false;
    }
    if (empty($id) || !\is_numeric($id)) {
      return false;
    }
    if (!isset($role) || !\is_numeric($role)) {
      return false;
    }

    $errors = array();

    try {
      $s = $this->pdo->prepare("UPDATE users SET roles_mask = :role WHERE id = :id LIMIT 1;");
      $r = $s->execute(array(
        ':role' => $role,
        ':id' => $id,
      ));
      if(!$r) {
        array_push($errors, 'Da ist etwas schiefgelaufen!');
      }
    } catch (\PDOException $e) {
      array_push($errors, 'Da ist etwas schiefgelaufen!');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    return true;
  }
  public function updatePassword($old, $new, $repeat) {
    if (!$this->isLoggedIn()) return false;
    $errors = array();
    
    $old = filter_var($old, FILTER_SANITIZE_STRING);
    $new = filter_var($new, FILTER_SANITIZE_STRING);
    $repeat = filter_var($repeat, FILTER_SANITIZE_STRING);

    try {
      $s = $this->pdo->prepare("SELECT id, username, email, password FROM users WHERE id = :id LIMIT 1;");
      $s->execute(array(
        ':id' => $this->getId()
      ));
      if ($s->rowCount() <= 0) {
        array_push($errors, 'Benutzer nicht gefunden!');
      } else {
        while ($row = $s->fetch()) {
          if (!password_verify($old, $row['password'])) {
            array_push($errors, 'Falsches Passwort!');
          }
        }
      }
    } catch (\PDOException $e) {
      array_push($errors, 'Da ist etwas schiefgelaufen!');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    if ($new != $repeat) {
      array_push($errors, 'Passwörter sind nicht gleich!');
    }
    if (strlen(trim($new)) < 8) {
      array_push($errors, 'Passwort zu kurz! Min. 8');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    if (count($errors) > 0) {
      return $errors;
    }

    try {
      $s = $this->pdo->prepare("UPDATE users SET password = :password WHERE id = :id LIMIT 1;");
      $r = $s->execute(array(
        ':password' => password_hash($new, PASSWORD_DEFAULT),
        ':id' => $this->getId(),
      ));
      if(!$r) {
        array_push($errors, 'Da ist etwas schiefgelaufen!');
      }
    } catch (\PDOException $e) {
      array_push($errors, 'Da ist etwas schiefgelaufen!');
    }
    if (count($errors) > 0) {
      return $errors;
    }
    return true;
  }
}
?>