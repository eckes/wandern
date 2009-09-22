<div id="headline">
    <?php
    require_once('../login/common.php');
    if($_SESSION['validUser'] == true) 
    {
        echo("<b>Logged in as " . $_SESSION['userName'] . "</b>");
        /* TODO: instead of loading the settings into an own variable, attach them to the _SESSION */
        $_SESSION['settings'] = loadSettings($_SESSION['userName']); 
        echo ' | <a href="usersettings.php">Settings</a> | <a href="../login/logout.php">Logout</a>';
    } 
    else
    {
        echo '<a href="../login/login.php">Login</a> | <a href="../login/register.php">Register</a>';
        $_SESSION['settings'] = loadSettings('anonymous'); 
    }
    echo ' | <a href="index.php">Walks Selection</a>';
    ?>
</div>
