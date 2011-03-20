<?php
	require_once('common.php');
	checkUser();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
   <title>Micro Login System</title>
   <link href="style/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div id="main">
      <div class="caption">Login System Demo Page</div>
      <div id="icon">&nbsp;</div>
      <div id="result">
		Hello <?php echo $_SESSION['userName']; ?> ! <br/>
		<p>This site demonstartes how to use Micro Login System.</p>
		<p><a href="logout.php"> To log out click here!</a></p>
	  </div>	
	  <div id="source">Micro Login System v 1.0</div>
    </div>
</body>   
