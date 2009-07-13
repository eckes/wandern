<?php
require_once('../login/common.php');
require_once('common.php');

if(     (!isset($_REQUEST['id']))
    ||  (!isset($_REQUEST['walked']) )
  )
{
    header("Status: 400 Bad Request");
}
elseif($_SESSION['validUser'] == false) 
{
    header("Status: 401 Not authorized");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <title>Editing Walk</title>
        <META http-equiv="content-type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="../css/style.php">
    </head>
    <body>
<?php
    if(     (!isset($_REQUEST['id']))
        ||  (!isset($_REQUEST['walked']) )
      )
    {
        echo "<H1>Bad Request</H1>";
    }
    elseif($_SESSION['validUser'] == false) 
    {
        echo "<H1>You must be logged in to use this feature</H1>";
        echo '<a href="javascript:window.close();">close</a>';
    }
    elseif(isset($_REQUEST['walked']))
    {
        if(0 == editWalk($_SESSION['userName'], $_REQUEST['id'], 'walked'))
        {
            echo "<H1>ok</H1>";
        }
        else
        {
            echo "error";
        }
    }
    else
    {
    }
    echo '<a href="javascript:window.close();">close</a>';
?>
    </body>
</html>
