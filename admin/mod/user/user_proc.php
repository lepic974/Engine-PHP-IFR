<?php
if(isset($_POST) && !empty($_POST)){
	$h = array();
	$h[$g_user['nom']] = $_POST[$g_user['nom']];
	$h[$g_user['prenom']] = $_POST[$g_user['prenom']];
	$h[$g_user['login']] = $_POST[$g_user['login']];
	$h[$g_user['isAdministrateurON']] = 1;
	
	if(isset($_POST[$g_user['enable_menu']]) && $_POST[$g_user['enable_menu']] == 1) $h[$g_user['enable_menu']] = 1;
	else $h[$g_user['enable_menu']] = 0;
	
	if(isset($_POST[$g_user['enable_article']]) && $_POST[$g_user['enable_article']] == 1) $h[$g_user['enable_article']] = 1;
	else $h[$g_user['enable_article']] = 0;
	
	if(isset($_POST[$g_user['enable_actu']]) && $_POST[$g_user['enable_actu']] == 1) $h[$g_user['enable_actu']] = 1;
	else $h[$g_user['enable_actu']] = 0;
	
	if(isset($_POST[$g_user['enable_photo']]) && $_POST[$g_user['enable_photo']] == 1) $h[$g_user['enable_photo']] = 1;
	else $h[$g_user['enable_photo']] = 0;
	
	if(isset($_POST[$g_user['enable_event']]) && $_POST[$g_user['enable_event']] == 1) $h[$g_user['enable_event']] = 1;
	else $h[$g_user['enable_event']] = 0;
	
	if(isset($_POST[$g_user['enable_param']]) && $_POST[$g_user['enable_param']] == 1) $h[$g_user['enable_param']] = 1;
	else $h[$g_user['enable_param']] = 0;
	
	if(isset($_POST[$g_user['enable_user']]) && $_POST[$g_user['enable_user']] == 1) $h[$g_user['enable_user']] = 1;
	else $h[$g_user['enable_user']] = 0;
	
	if(!empty($_POST[$g_user['password']])){
		$h[$g_user['password']] = md5($_POST[$g_user['password']]);
	}
	
	if($_POST[$g_user['id']]>0){
		// update
		sql_simple_update(T_USER,$_POST[$g_user['id']],$h);
	}else{
		// Ajout
		sql_simple_insert(T_USER,$h);
	}
	
	jump_to_url_and_exit('index.php?to=listing_user');
}
?>