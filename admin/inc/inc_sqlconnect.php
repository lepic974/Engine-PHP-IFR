<?php

$DATABASE = NAME_BDD;
$SERVEUR = SERVEUR_BDD;
$USER = USER_BDD;
$PASS = PASSWORD_BDD;

$style = 'border:3px solid #ff0000;';
$style.= 'border-left:10px solid #ff0000;';
$style.= 'border-right:10px solid #ff0000;';
$style.= 'background-color: #fd5757;';
$style.= 'width: 450px; height:75px;';
$style.= 'margin:auto;';
$style.= 'padding-top:20px;';
$style.= 'margin-top:75px;';
$style.= 'text-align:center;';
$style.= 'color: #FFFFFF;';
$style.= 'font-family:verdana;';
$style.= 'font-size:11px;';
$style.= 'vertical-align:middle;';

$die_message_serveur = '<html><body>';
$die_message_serveur.= '	<div style="'.$style.'">';
$die_message_serveur.= '		<img src="pic/icons/erreur.png" alt="erreur" style="margin-right:30px;vertical-align:middle;"/>';
$die_message_serveur.= '		Connexion impossible au serveur <b>'.$SERVEUR.'</b>';
$die_message_serveur.= '		<img src="pic/icons/erreur.png" alt="erreur" style="margin-left:30px;vertical-align:middle;"/>';
$die_message_serveur.= '	</div>';
$die_message_serveur.= '</body></html>';

$die_message_bdd = '<html><body>';
$die_message_bdd.= '	<div style="'.$style.'">';
$die_message_bdd.= '		<img src="pic/icons/erreur.png" alt="erreur" style="margin-right:30px;vertical-align:middle;"/>';
$die_message_bdd.= '		Connexion impossible à la base <b>'.$DATABASE.'</b>';
$die_message_bdd.= '		<img src="pic/icons/erreur.png" alt="erreur" style="margin-left:30px;vertical-align:middle;"/>';
$die_message_bdd.= '	</div>';
$die_message_bdd.= '</body></html>';

$link = ($GLOBALS["___mysqli_ston"] = mysqli_connect($SERVEUR,  $USER,  $PASS)) or die($die_message_serveur);
mysqli_select_db($link, $DATABASE) or die($die_message_bdd);
   
$sql="SET CHARACTER SET 'utf8mb4';";
mysqli_query($link, $sql);
$sql="SET collation_connection = 'utf8mb4_general_ci';";
mysqli_query($link, $sql);

?>