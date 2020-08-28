<?php
$ajax_action_post = '';
$ajax_action_get = '';
if(isset($_POST['_ajax_action'])) $ajax_action_post = $_POST['_ajax_action'];
if(isset($_GET['_ajax_action'])) $ajax_action_get = $_GET['_ajax_action'];

$ajax_action = $ajax_action_get;
if($ajax_action_post != "") $ajax_action = $ajax_action_post;

if($ajax_action){
	switch($ajax_action){
		case 'change_background':
			$uniqueId = time() % 100000000;
			$name_file = 'background_'.$uniqueId."_".$_GET['filename'];
			file_put_contents(
				'../pic/upload/interface/' . $name_file,
				file_get_contents('php://input')
			);
			squery("DELETE FROM ".T_UI." WHERE {$g_interface['fk_user']}=".$_SESSION[USERSESSION]['user_id']." AND {$g_interface['div_interface']}='background'");
			$h = array();
			$h[$g_interface['fk_user']] = $_SESSION[USERSESSION]['user_id'];
			$h[$g_interface['background']] = $name_file;
			$h[$g_interface['div_interface']] = 'background';
			sql_simple_insert(T_UI,$h);
		break;
		case 'save_interface':
			$div = $_POST['div'];
			if(!empty($div)){
				$offset_x = $_POST['offset_x'];
				$offset_y = $_POST['offset_y'];
				$user = $_SESSION[USERSESSION]['user_id'];
				squery("DELETE FROM ".T_UI." WHERE {$g_interface['fk_user']}=".$user." AND {$g_interface['div_interface']}='".$div."'");
				$h = array();
				$h[$g_interface['fk_user']] = $user;
				$h[$g_interface['offset_x']] = $offset_x;
				$h[$g_interface['offset_y']] = $offset_y;
				$h[$g_interface['div_interface']] = $div;
				sql_simple_insert(T_UI,$h);
			}
		break;
		case 'reset_interface':
			$user = $_SESSION[USERSESSION]['user_id'];
			$name_bg = squery("SELECT {$g_interface['background']} FROM ".T_UI." WHERE {$g_interface['fk_user']}=".$_SESSION[USERSESSION]['user']." AND {$g_interface['div_interface']}='background'");
			if(is_file('../pic/upload/interface/'.$name_bg))
				@unlink('../pic/upload/interface/'.$name_bg);
			squery("DELETE FROM ".T_UI." WHERE {$g_interface['fk_user']}=".$user);
		break;
	}
}
?>