<?php

function boolToString($a_val)
{
  if($a_val == "true" || $a_val == "1" || $a_val == "yes") return "true";
  if($a_val == "false" || $a_val == "0" || $a_val == "no") return "false";
  // no bool value, numbers can be returned directly
  if(is_numeric($a_val)) return $a_val;
  // no bool value, at least quote the string then.
  return "'" . $a_val . "'";
}

/** stores the given settings of the given user */
function storeSettings($a_user, $a_settings)
{
  $filepath = "usersettings/" . $a_user . ".settings";
  $pfile = fopen($filepath, "w+");
  if(FALSE == $pfile)
  {
    return -1;
  }
  rewind($pfile);
  foreach($a_settings AS $thekey=>$thevalue)
  {
    $theline = "$thekey:$thevalue\r\n";
    fwrite($pfile, $theline);
  }
  fclose($pfile);
  return 0;
}

function checkSession()
{
  return(($_SESSION['validUser'] == true) && isset($_SESSION['userName']));
}

/* loads the settings of the given user */
function loadSettings($a_user)
{
  $filepath = "usersettings/" . $a_user . ".settings";
  if(!is_readable($filepath))
  {
    return null;
  }
  $pfile = fopen($filepath, "r+");
  if(FALSE == $pfile)
  {
    return null;
  }
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
  return($settings);
}

/* loads the walks for the given user */
function loadWalks($a_user)
{
  $filepath = "usersettings/" . $a_user . ".walks";
  if(!is_readable($filepath))
  {
    return null;
  }
  $pfile = fopen($filepath, "r");
  if(FALSE == $pfile)
  {
    return null;
  }
  rewind($pfile);
  $walks = array();
  while (!feof($pfile))
  {
    $line = fgets($pfile);
    if(0 != strlen($line))
    {
      $tmp = explode(' ', $line);
      $walks[strtoupper($tmp[0])] = substr($tmp[1], 0, strlen($tmp[1])-2); // -2 because of trailing CR_LF 
    }
  }
  return $walks;
}

function editWalk($a_user, $a_id, $a_action)
{
  switch($a_action)
  {
  case 'walked':
    $filepath = "usersettings/" . $a_user . ".walks";
    $pfile      = fopen($filepath, "a");
    if(FALSE == $pfile)
    {
      return -1;
    }
    $today      = date('Y-m-d');
    $theline    = strtoupper($a_id) . " " . $today . "\r\n";
    fwrite($pfile, $theline);
    fclose($pfile);
    return 0;
  default:
    return -1;
    break;
  }
}

?>
