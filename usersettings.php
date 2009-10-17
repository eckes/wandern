<?php
    require_once('../login/common.php');
    require_once('common.php');
    require_once('constants.php');

    if (isset($_POST['saveSettings']))
    {
		// Get user input
        foreach($g_books AS $thebook)
        {
            $settings[$thebook->m_id]  = isset($_POST[$thebook->m_id]) ? $_POST[$thebook->m_id] : 'no';
        }
        $settings['lat'] = isset($_POST['lat']) ? $_POST['lat'] : DEFAULTLAT;
        $settings['lon'] = isset($_POST['lon']) ? $_POST['lon'] : DEFAULTLON;
        // SAVE SETTINGS TO FILE
        $retVal = storeSettings($_SESSION['userName'], $settings);
        $_SESSION['settings'] = loadSettings($_SESSION['userName']);
	}	

    if(!isset($_SESSION['userName']))
    {
        header('Location: ../login/login.php');
        return;
    }

    // LOAD SETTINGS FROM FILE HERE 
    if(!isset($_SESSION['settings']))
    {
        /* read settings from file */
        $_SESSION['settings'] = loadSettings($_SESSION['userName']);
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
    <title>Change user Settings for <?=$_SESSION['userName']?></title>
        <META http-equiv="content-type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="../css/style.php">
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
              <legend>HOME</legend>
              <input type='text' name='lat' value='<?=$_SESSION['settings']['lat']?>'> Lat</input>
              <input type='text' name='lon' value='<?=$_SESSION['settings']['lon']?>'> Lon</input>
            </fieldset>
            <fieldset>
              <legend>Bücher</legend>
<?php
        foreach($g_books AS $b)
        {
            $bs = $b->m_id . "_small";
            echo<<<END
<a href="images/$b->m_id.png" target="_blank"><img src="images/$bs.png"/></a> <input type="checkbox" name="$b->m_id" value="yes"
END;
            if($_SESSION['settings'][$b->m_id]=="yes") 
            {
                echo "checked";
            }
            echo ">" . $b->m_name . "<br>";
        }
?>
            </fieldset>
            <button type="submit" name="saveSettings">Save Settings</button>
            </form>
        </div>
    </body>
</html>
