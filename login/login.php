<?php
require_once('common.php');

$error = '0';

if (isset($_POST['submitBtn'])){
	// Get user input
	$username = isset($_POST['username']) ? $_POST['username'] : '';
	$password = isset($_POST['password']) ? $_POST['password'] : '';
        
	// Try to login the user
	$error = loginUser($username,$password);
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
   <title>Micro Login System</title>
   <link href="style/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div id="main">
<?php if ($error != '') {?>
      <div class="caption">Site login</div>
      <div id="icon">&nbsp;</div>
      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="loginform">
        <table width="100%">
          <tr><td>Username:</td><td> <input class="text" name="username" type="text"  /></td></tr>
          <tr><td>Password:</td><td> <input class="text" name="password" type="password" /></td></tr>
          <tr><td colspan="2" align="center"><input class="text" type="submit" name="submitBtn" value="Login" /></td></tr>
        </table>  
        <input type="hidden" value="<?=$_SERVER['HTTP_REFERER']?>" name="referer">
      </form>
      
      &nbsp;<a href="register.php">Register</a>
      
<?php 
}   
    if (isset($_POST['submitBtn'])){

?>
      <div class="caption">Login result:</div>
      <div id="icon2">&nbsp;</div>
      <div id="result">
        <table width="100%"><tr><td><br/>
<?php
    if ($error == '') 
    {
?>
    Welcome <?=$username?>! <br/>You are logged in!<br/><br/>
    Click <a href="<?=$_POST['referer']?>">here</a> to get back where you came from<br/>
    Alternatively, you can go to the <a href="../index.php">top</a> of this site.
<?php
	}
	else echo $error;
?>
		<br/><br/><br/></td></tr></table>
	</div>
<?php            
    }
?>
	<div id="source">Micro Login System v 1.0</div>
    </div>
</body>   
