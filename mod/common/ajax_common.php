<?php
$ajax_action_post = '';
$ajax_action_get = '';
if(isset($_POST['_ajax_action'])) $ajax_action_post = $_POST['_ajax_action'];
if(isset($_GET['_ajax_action'])) $ajax_action_get = $_GET['_ajax_action'];

$ajax_action = $ajax_action_get;
if($ajax_action_post != "") $ajax_action = $ajax_action_post;

if($ajax_action){
	switch($ajax_action){
		case 'change_langue':
			$id_lang = $_POST['id_lang'];
			$_SESSION[SITE_NAME]['id_lang'] = $id_lang;
		break;
	}
}
?>