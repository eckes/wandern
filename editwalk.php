<?php
require_once('login/common.php');
require_once('common.php');

if(     (!isset($_REQUEST['id']))
    ||  (!isset($_REQUEST['walked']) )
  )
{
    header("Status: 400 Bad Request");
    die;
}
elseif($_SESSION['validUser'] == false) 
{
    header("Status: 401 Not authorized");
    die;
}

if(isset($_REQUEST['walked']))
{
  if(0 == editWalk($_SESSION['userName'], $_REQUEST['id'], 'walked'))
  {
    header("Status: 200 OK");
    echo $_REQUEST['id'];
    return;
  }
  else
  {
    header("Status: 500 Internal Server Error");
    die;
  }
}
?>
