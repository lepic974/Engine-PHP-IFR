<?php

// chemin absolu du fichier
$path=$_SERVER['SCRIPT_FILENAME'];
$path = str_replace('\\', '/', $path);
$path = pathinfo($path);

$current_folder = '/inc';
$path_dir = str_replace($current_folder, '', $path['dirname']);


define('ENGINE_TO','index.php?to=');
define('INDEX_TO','index.php?to=');
define('ENGINE','index.php');
define('INDEX','index.php');
define('SITE_NAME',file_get_contents('param.ini'));
define('DEFAULT_LANGUAGE_ID',1);

$custom_path = '';

define('PATH_TO_PARAM_FOLDER',$custom_path.'param/');
define('PATH_TO_FUNC_FOLDER',$custom_path.'func/');
define('PATH_TO_LIB_FOLDER',$custom_path.'lib/');
define('PATH_TO_CLASS_FOLDER',$custom_path.'class/');
define('PATH_TO_PIC_FOLDER',$custom_path.'pic/');
define('PATH_TO_SPECIFIC_FOLDER',$custom_path.'_specific/'.SITE_NAME.'/');

// require specific
require $custom_path.'_specific/'.SITE_NAME.'/param.php';

// require generic
require $custom_path.'param/param_dbfield.php';
require $custom_path.'lib/func_html.php';
require $custom_path.'lib/func_php.php';
require $custom_path.'lib/lib_version.php';
require $custom_path.'inc/inc_sqlconnect.php';
require $custom_path.'inc/inc_sqlquery.php';
require $custom_path.'func/func_common.php';
require $custom_path.'func/func_tools.php';
require $custom_path.'func/func_ubtn.php';

require $custom_path.'param/param_engine.php';
require $custom_path.'param/param_global.php';
require $custom_path.'func/func_user.php';

require $custom_path.'func/func_ajax.php';

// Gestion inclusion fonction specifique au projet
require $custom_path.'_specific/'.SITE_NAME.'/func/func_specific.php';

// Gestion Interface specific
require $custom_path.'_specific/'.SITE_NAME.'/class/page.class.php';

//use specific class or default if not found
$classList = array();

foreach($classList as $file){
	$file_path_default = $custom_path.PATH_TO_CLASS_FOLDER.$file;
	
	if(file_exists($file_path_default)){
		require $file_path_default;
	}else{
		echo 'file not found: '.$file_path_specific;
		exit;
	}
}


?>