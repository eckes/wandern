<?php
    require_once('../login/common.php');
    require_once('common.php');

    if (isset($_POST['saveSettings']))
    {
		// Get user input
        foreach($g_booklist AS $thebook)
        {
            $settings[$thebook]  = isset($_POST[$thebook]) ? $_POST[$thebook] : 'no';
        }
        // SAVE SETTINGS TO FILE
        $retVal = storeSettings($_SESSION['userName'], $settings);
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
        $settings = loadSettings($_SESSION['userName']);
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
<?php
    require('loginhead.php');
    if (isset($_POST['saveSettings']))
    {
        if(0 == $retVal)
        {
            $msg = "Saving settings successful";
        }
        else
        {
            $msg = "Saving settings failed";
        
        }
        echo '<script type="text/javascript">alert("' . $msg . '");</script>';
    }
?>
        <div style="width:250pt;float:left;">
          <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="savesettings">
            <fieldset>
              <legend>Bücher</legend>
<?php
        foreach($g_booklist AS $b)
        {
            $bs = $b . "_small";
            echo<<<END
<a href="images/$b.png" target="_blank"><img src="images/$bs.png"/></a> <input type="checkbox" name="$b" value="yes"
END;
            if($settings[$b]=="yes") 
            {
                echo "checked";
            }
            echo ">" . $g_booktitles[$b] . "<br>";
        }
?>
            </fieldset>
            <button type="submit" name="saveSettings">Save Settings</button>
            </form>
        </div>
    </body>
</html>
