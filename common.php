<?php
/** Array of book IDs, used on several places */
$g_booklist=array(  'mluw1',
                    'mluw2',
                    'fuw1',
                    'fuw2',
                    'fuw3',
                    'nw2');

/** The full titles of the books */
$g_booktitles=array('mluw1'=>'Mit Lenkrad und Wanderstab I',
                    'mluw2'=>'Mit Lenkrad und Wanderstab II',
                    'fuw1'=>'Fahren und Wandern 1',
                    'fuw2'=>'Fahren und Wandern 2',
                    'fuw3'=>'Fahren und Wandern 3',
                    'nw2'=>'Nürnberger Wanderziele II');

/** stores the given settings of the given user */
function storeSettings($a_user, $a_settings)
{
    $pfile = fopen("usersettings/" . $a_user . ".settings", "w+");
    rewind($pfile);
    foreach($a_settings AS $thekey=>$thevalue)
    {
        $theline = "$thekey:$thevalue\r\n";
        fwrite($pfile, $theline);
    }
    fclose($pfile);
}

/* loads the settings of the given user */
function loadSettings($a_user)
{
    $pfile = fopen("usersettings/" . $a_user . ".settings", "r");
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
?>
