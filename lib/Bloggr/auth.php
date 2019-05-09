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
      array_push($errors, 'Your username may not contain whitespaces!');
    }
    if (strlen(trim($username)) < 3) {
      array_push($errors, 'Username is too short! Min. 3');
    }
    if (strlen(trim($username)) > 16) {
      array_push($errors, 'Username is too long! Max. 16');
    }
    if (!$email) {
      array_push($errors, 'Enter a valid email!');
    }
    if (strlen(trim($password)) < 8) {
      array_push($errors, 'Password is too short! Min 8');
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
          array_push($errors, 'Username already exists!');
        }
        if ($row['email'] === $email) {
          array_push($errors, 'Email already exists!');
        }
      }
    } catch (\PDOException $e) {
      array_push($errors, 'Something went wrong!');
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
        array_push($errors, 'Something went wrong!');
      }
    } catch (\PDOException $e) {
      array_push($errors, 'Something went wrong!');
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
      array_push($errors, 'Please enter a username or email!');
    }
    if (!$password || $password === '') {
      array_push($errors, 'Please enter a password!');
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
        array_push($errors, 'Wrong username/email or password!');
      } else {
        while ($row = $s->fetch()) {
          if (!password_verify($password, $row['password'])) {
            array_push($errors, 'Wrong username/email or password!');
          }
          $userId = $row['id'];
        }
      }
    } catch (\PDOException $e) {
      array_push($errors, 'Something went wrong!');
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
        array_push($errors, 'Something went wrong!');
      }
    } catch (\PDOException $e) {
      array_push($errors, 'Something went wrong!');
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
  public function logout() {
    $_SESSION['id'] = '';
    unset($_SESSION['id']);
    session_unset();
    return true;
  }
}
?>