<?php

session_start();

function registerUser($user,$pass1,$pass2){
  $errorText = '';

  // Check passwords
  if ($pass1 != $pass2) $errorText = "Passwords are not identical!";
  elseif (strlen($pass1) < 6) $errorText = "Password is to short!";

  // Check user existance	
  $pfile = fopen("userpwd.txt","a+");
  rewind($pfile);

  while (!feof($pfile)) {
    $line = fgets($pfile);
    $tmp = explode(':', $line);
    if ($tmp[0] == $user) {
      $errorText = "The selected user name is taken!";
      break;
    }
  }

  // If everything is OK -> store user data
  if ($errorText == ''){
    // Secure password string
    $userpass = md5($pass1);

    fwrite($pfile, "\r\n$user:$userpass");
  }

  fclose($pfile);


  return $errorText;
}

function loginUser($user,$pass){
  $errorText = '';
  $validUser = false;

  // Check user existance	
  if(!file_exists("userpwd.txt"))die("user database userpwd.txt not fond");
  $pfile = fopen("userpwd.txt","r");
  if(false == $pfile)
  {
    die("opening user database failed");
  }
  rewind($pfile);

  while (!feof($pfile)) {
    $line = fgets($pfile);
    $tmp = explode(':', $line);
    if ($tmp[0] == $user) {
      // User exists, check password
      if (trim($tmp[1]) == trim(md5($pass))){
        $validUser= true;
        $_SESSION['userName'] = $user;
      }
      break;
    }
  }
  fclose($pfile);

  if ($validUser != true) $errorText = "Invalid username or password!";

  if ($validUser == true) $_SESSION['validUser'] = true;
  else $_SESSION['validUser'] = false;

  return $errorText;	
}

function logoutUser(){
  unset($_SESSION['validUser']);
  unset($_SESSION['userName']);
}

function checkUser(){
  if ((!isset($_SESSION['validUser'])) || ($_SESSION['validUser'] != true)){
    header('Location: login.php');
  }
}

?>
