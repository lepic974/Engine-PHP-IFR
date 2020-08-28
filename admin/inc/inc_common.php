<?php

// chemin absolu du fichier
$path=$_SERVER['SCRIPT_FILENAME'];
$path = str_replace('\\', '/', $path);
$path = pathinfo($path);

$current_folder = '/inc';
$path_dir = str_replace($current_folder, '', $path['dirname']);

define('SITE_NAME',file_get_contents('../param.ini'));
define('ENGINE_TO','index.php?to=');
define('ENGINE','index.php');
$custom_path = '';

define('PATH_TO_PARAM_FOLDER',$custom_path.'param/');
define('PATH_TO_FUNC_FOLDER',$custom_path.'func/');
define('PATH_TO_LIB_FOLDER',$custom_path.'lib/');
define('PATH_TO_CLASS_FOLDER',$custom_path.'class/');
define('PATH_TO_PIC_FOLDER',$custom_path.'pic/');
define('PATH_TO_SPECIFIC_FOLDER','../_specific/'.SITE_NAME.'/');

//require generic
require $custom_path.'../_specific/'.file_get_contents('../param.ini').'/param.php';
require $custom_path.'../param/param_dbfield.php';
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
require $custom_path."lib/func_sql.php";
require $custom_path.'func/func_user.php';

require $custom_path.'func/func_ajax.php';
require $custom_path.'func/func_admin.php';

//use specific class or default if not found
$classList = array();
$classList[] = 'genAuth.class.php';
$classList[] = 'auth.class.php';
$classList[] = 'page.class.php';


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