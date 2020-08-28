<?php

# Parametre BDD
if(is_file('is_prod')){
	if(!defined('IS_PROD')) define('IS_PROD',TRUE);
	define('SERVEUR_BDD','');
	define('NAME_BDD','');
	define('USER_BDD','');
	define('PASSWORD_BDD','');
	define('UPLOAD_PIC','/pic/upload/images');
}else{
	define('SERVEUR_BDD','localhost');
	define('NAME_BDD','Projet_IFR');
	define('USER_BDD','root');
	define('PASSWORD_BDD','root');
	define('UPLOAD_PIC','/Engine_PHP_Fullstack/pic/upload/images');
}

# Parametre Site Web
define('TITLE_SITE','IFR');
define('USERSESSION', 'CDSessionName');
define('MAIL_SUBSCRIB','');

# Mail
define('SMTP_HOST','');
define('SMTP_FROM','Contact IFR');
define('SMTP_USER','');
define('SMTP_PASSWORD','');
define('SMTP_PORT',587);

?>