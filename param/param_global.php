<?php

	define('PICTURE_FOLDER', 'pic');	
	define('UPLOADFOLDER', 'upload');	
	define('LANG_CODE_DEFAULT', 1);
	define('DEFAULT_ERP_CURRENCY', '€');
	define('DATEFORMAT' , 'd/m/Y');	
	define('DATEFORMATFULL' , 'd/m/Y H:i');	

	if(!defined('URLSELF')) define('URLSELF',htmlspecialchars($_SERVER["REQUEST_URI"]));

?>