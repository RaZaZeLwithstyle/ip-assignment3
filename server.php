<?php

session_start();

//initializing define_syslog_variables

$username = "";
$email = "";

$errors = array();

//connect to Heroku SQL database
$cleardb_url      = parse_url(getenv("CLEARDB_DATABASE_URL"));
$cleardb_server   = $cleardb_url["host"];
$cleardb_username = $cleardb_url["user"];
$cleardb_password = $cleardb_url["pass"];
$cleardb_db       = substr($cleardb_url["path"],1);

$db = mysqli_connect($cleardb_server, $cleardb_username, $cleardb_password, $cleardb_db) or die("could not connect to database");

if(isset($_POST['reg_user'])) {

  //Register users
  $username = mysqli_real_escape_string($db, $_POST['username']);
  $email = mysqli_real_escape_string($db, $_POST['email']);
  $password1 = mysqli_real_escape_string($db, $_POST['password1']);
  $password2 = mysqli_real_escape_string($db, $_POST['password2']);

  //form validation

  if(empty($username)) array_push($errors, "Username is required");
  if(empty($email)) array_push($errors, "Email is required");
  if(empty($password1)) array_push($errors, "Password is required");
  if($password1 != $password2) array_push($errors, "Passwords do not match");

  // checking db for existing users with same username
  $user_check_query = "SELECT FROM users WHERE username = '$username' or email = '$email' LIMIT 1";

  $result = mysqli_query($db, $user_check_query);
  $user = mysqli_fetch_assoc($result);

  if($user) {
        if($user['username'] === $username) {
          array_push($errors, "Username already exists");
        }
        if($user['email'] === $email) {
          array_push($errors, "Email already in use");
        }
  }

  //Register the user if no errors found

  if(count($errors) == 0){
      $password = md5($password1); //encrypting password
      $query = "INSERT INTO users (username, email, password)
            VALUES ('$username', '$email', '$password')";

      mysqli_query($db, $query);
      $_SESSION['username'] = $username;
      $_SESSION['success'] = "You are now logged in!";

      header('location: index.php');
  }
}

//LOGIN USER

if(isset($_POST['login_user'])) {

  $username = mysqli_real_escape_string($db, $_POST['username']);
  $password1 = mysqli_real_escape_string($db, $_POST['password1']);

  if(empty($username)){
    array_push($errors, "Username is required");
  }

  if(empty($password1)) {
    array_push($errors, "Password is required");
  }

  if(count($errors) == 0 ) {

    $password1 = md5($password1);

    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password1'";
    $results = mysqli_query($db, $query);

    if(mysqli_num_rows($results)) {
      $_SESSION['username'] = $username;
      $_SESSION['success'] = "Log In successful!";
      header('location: index.php');
    }
  } else {
    array_push($errors, "Wrong username or password. Please try again!");
  }
}

?>
