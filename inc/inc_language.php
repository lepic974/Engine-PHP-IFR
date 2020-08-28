<? 
/* ==============================================
	NEXTO - solutions web
	www.nexto.fr
	
	Copyright (c) 2007-2008 NEXTO
	developpement realise par Pascal Chea.
	
============================================== */
?>
<?
	global $language_file;

	if(defined('LANGUAGE')){
		$language_file = LANGUAGE.'.php';
		if(file_exists(LANGUAGEFOLDER.'/'.$language_file)){
			$language_path = LANGUAGEFOLDER.'/'.$language_file;
			include($language_path);
		}else{
			/*echo 'ERROR: Language file not found ('.LANGUAGEFOLDER.'/'.$language_file.')';*/
		}
	}else{
		echo 'ERROR: Language not set in param/param_global.php';
	}
?>
