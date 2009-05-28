<?php 
    function build_query()
    {
        $l_sql = "SELECT * FROM `walks` WHERE "; // the base query

        /* shall we display already walked trails? */
        if(!$_REQUEST[showwalked])
        {
            $l_sql = $l_sql . "(`DATUM` = 0000-00-00)";
        }
        else
        {
            $l_sql = $l_sql . "(1)";
        }

        /* are we forbidden to display trails that are hügelig? */
        if($_REQUEST[kein_huegeliges])
        {
            $l_sql = $l_sql . " AND (`Charakter` NOT LIKE '%hügelig%')";
        }

        /* are we forbidden to display trails that are anstrengend? */
        if($_REQUEST[kein_anstrengendes])
        {
            $l_sql = $l_sql . " AND (`Charakter` NOT LIKE '%anstrengend%')";
        }

        /* are we forbidden to display trails that are steil? */
        if($_REQUEST[kein_steiles])
        {
            $l_sql = $l_sql . " AND (`Charakter` NOT LIKE '%steil%')";
        }

        /* shall we only deliver trails that are leicht? */
        if($_REQUEST[nur_leichtes])
        {
            $l_sql = $l_sql . " AND (`Charakter` LIKE 'leichtes Gelände')";
        }

        /* check the minimum distance */
        if($_REQUEST[dst_min] != "egal")
        {
            $l_sql = $l_sql . " AND (`Laenge` >= $_REQUEST[dst_min])";
        }

        /* check the maximum distance */
        if($_REQUEST[dst_max] != "egal")
        {
            $l_sql = $l_sql . " AND (`Laenge` <= $_REQUEST[dst_max])";
        }

        return $l_sql;
    }

    /* Init function for the mysql database access */
    function db_init()
    {
        $sql_host   = "localhost";
        $sql_user   = "root";
        $sql_pass   = "";
        $sql_db     = "wandern";

        $db         = mysql_connect($sql_host, $sql_user, $sql_pass);
        mysql_select_db($sql_db, $db) or die ("selecting db failed\n");
        return $db;
    }

    /* cleanup function for the mysql database access */
    function db_cleanup($a_db)
    {
        mysql_close($a_db);
    }

    /* worker method. returns the needed elements */
    function db_getElements($a_db)
    {
        $sql        = build_query();
        $res = mysql_query($sql, $a_db);
        $retval = array();
        while($row=mysql_fetch_array($res))
        {
            $entry[Tag]        = $row[Tag]         ;
            $entry[Name]       = $row[Name]        ;
            $entry[Laenge]     = $row[Laenge]      ;
            $entry[Dauer]      = $row[Dauer]       ;
            $entry[Charakter]  = $row[Charakter]   ;
            $entry[Lat]        = $row[Lat]         ;
            $entry[Lon]        = $row[Lon]         ;
            $entry[Datum]      = $row[Datum]       ;
            array_push($retval, $entry);
        }
        return $retval;
    }
?>
