<?php include("colors.php"); ?>
<?php header("Content-type: text/css"); ?>

body {font-family:"Bitstream Vera Sans", sans-serif;
    background:<?=$col_body?>;
    color:<?=$col_accent?>;
    margin-left:40px;} 

h1, h2, h3, h4, h5, h6{text-align:left;
    font-family:"Bitstream Vera Sans", sans-serif;
    color:<?=$col_accent?>;}

#headline{margin:-10px -8px 10px -40px;
    padding: 5px 10px 5px 40px;
    border: 5px solid <?=$col_accent?>;
    background:<?=$col_accent?>; 
    color:<?=$col_body?>;
min-height:60px;
}

#headline h1{color:<?=$col_body?>;}
#headline a:link{color:<?=$col_body?>;}
#headline a:visited{color:<?=$col_body?>;}
#headline a:hover{color:<?=$col_hlight?>;}

/* linklist styling below */
.linklist{ font-family:"Bitstream Vera Sans", sans-serif;
    font-weight: bold;
    width: 300pt;
    padding:10px; }
.linklist li{margin:30px;
    background:<?=$col_accent?>;
    list-style-type:none;
    border:1px solid <?=$col_accent?>;}
.linklist * .linkentry{color:white;}
.linklist * .linktext{background:<?=$col_body?>;
    padding: 2px;
    color:<?=$col_accent?>;}
.linklist * a:link{color:<?=$col_body?>;}
.linklist * a:visited{color:<?=$col_body?>;}
.linklist * a:hover{color:<?=$col_hlight?>;}

.loginhead {color:<?=$col_body?>; background:<?=$col_accent?>;padding:10px;}
.loginhead a:link{color:<?=$col_body?>;}
.loginhead a:visited{color:<?=$col_body?>;}
.loginhead a:hover{color:<?=$col_hlight?>;}
