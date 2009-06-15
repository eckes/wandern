<?php
    require_once('../login/common.php');

	if (isset($_POST['saveSettings'])){
		// Get user input
		$settings['mluw1']  = isset($_POST['mluw1']) ? $_POST['mluw1'] : 'no';
		$settings['mluw2']  = isset($_POST['mluw2']) ? $_POST['mluw2'] : 'no';
		$settings['fuw1']   = isset($_POST['fuw1'])  ? $_POST['fuw1']  : 'no';
		$settings['fuw2']   = isset($_POST['fuw2'])  ? $_POST['fuw2']  : 'no';
		$settings['fuw3']   = isset($_POST['fuw3'])  ? $_POST['fuw3']  : 'no';
		$settings['nw2']    = isset($_POST['nw2'])   ? $_POST['nw2']   : 'no';
        // SAVE SETTINGS TO FILE
        $pfile = fopen("usersettings/" . $_SESSION['userName'] . ".settings", "w+");
        rewind($pfile);
        $keys = array_keys($settings);
        $len = count($settings);
        for ($i = 0; $i < $len; $i++)
        {
            $thekey = $keys[$i];
            $theline = "$thekey:$settings[$thekey]\r\n";
            fwrite($pfile, $theline);
        }
        fclose($pfile);
	}	

    if(!isset($_SESSION['userName']))
    {
        header('Location: ../login/login.php');
        return;
    }

    // LOAD SETTINGS FROM FILE HERE 
    if(!isset($settings))
    {
        /* read settings from file */
        $pfile = fopen("usersettings/" . $_SESSION['userName'] . ".settings", "r");
        rewind($pfile);
        while (!feof($pfile))
        {
            $line = fgets($pfile);
            if(0 != strlen($line))
            {
                $tmp = explode(':', $line);
                $settings[$tmp[0]] = substr($tmp[1], 0, strlen($tmp[1])-2); // -2 because of trailing CR_LF 
            }
        }
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
    <title>Change user Settings for <?=$_SESSION['userName']?></title>
        <META http-equiv="content-type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="../css/spring.css">
        <style type="text/css">
          fieldset{border:1px solid #6cb0bd};
        </style>
    </head>
    <body>
<p class="loginhead">
<?php
if($_SESSION['validUser'] == true) 
{
    echo("<b>Hello " . $_SESSION['userName'] . "</b>");
?>
    | Go back to the <a href="index.php">walks selection</a> | <a href="../login/logout.php">Logout</a>
<?php
} 
else
{
?>
If you are a registerd user, you could login <a href="../login/login.php">here</a>. Otherwise you could register <a href="../login/register.php">here</a>.
<?php
}
?>
    </p>
        <div style="width:250pt;float:left;">
          <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="savesettings">
            <fieldset>
              <legend>Bücher</legend>
              <input type="checkbox" name="mluw1" value="yes" <?php if($settings["mluw1"]=="yes") echo "checked";?>> Mit Lenkrad und Wanderstab I<br>
              <input type="checkbox" name="mluw2" value="yes" <?php if($settings["mluw2"]=="yes") echo "checked";?>> Mit Lenkrad und Wanderstab II<br>
              <input type="checkbox" name="fuw1"  value="yes" <?php if($settings["fuw1"] =="yes") echo "checked";?>> Fahren und Wandern I<br>
              <input type="checkbox" name="fuw2"  value="yes" <?php if($settings["fuw2"] =="yes") echo "checked";?>> Fahren und Wandern II<br>
              <input type="checkbox" name="fuw3"  value="yes" <?php if($settings["fuw3"] =="yes") echo "checked";?>> Fahren und Wandern III<br>
              <input type="checkbox" name="nw2"   value="yes" <?php if($settings["nw2"]  =="yes") echo "checked";?>> Nürnberger Wanderziele II<br>
            </fieldset>
            <button type="submit" name="saveSettings">Save Settings</button>
            </form>
        </div>
    </body>
</html>
