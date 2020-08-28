<?php

function getUserAdresse($id_user){
	global $g_user, $g_pays;
	
	$sql = "SELECT a.{$g_user['nom']} AS nom";
	$sql.= " ,a.{$g_user['prenom']} AS prenom";
	$sql.= " ,a.{$g_user['adresse_1']} AS adresse_1";
	$sql.= " ,a.{$g_user['adresse_2']} AS adresse_2";
	$sql.= " ,a.{$g_user['cp']} AS cp";
	$sql.= " ,a.{$g_user['ville']} AS ville";
	$sql.= " ,b.{$g_pays['id']} AS id_pays";
	$sql.= " ,b.{$g_pays['pays']} AS pays";
	$sql.= " ,a.{$g_user['tel']} AS tel";
	$sql.= " FROM ".T_USER." a ";
	$sql.= " LEFT JOIN ".T_PAYS." b ON b.{$g_pays['id']}=a.{$g_user['fk_pays']} ";
	$sql.= " WHERE a.{$g_user['id']}=".$id_user;	
	$rs = query($sql);
	$list = array();
	if($rs){
		$data = mysqli_fetch_assoc($rs);
		$list[$g_user['nom']] = $data['nom'];
		$list[$g_user['prenom']] = $data['prenom'];
		$list[$g_user['adresse_1']] = $data['adresse_1'];
		$list[$g_user['adresse_2']] = $data['adresse_2'];
		$list[$g_user['cp']] = $data['cp'];
		$list[$g_user['ville']] = $data['ville'];
		$list[$g_user['tel']] = $data['tel'];
		$list['id_pays'] = $data['id_pays'];
		$list['pays'] = $data['pays'];
	}
	return $list;
}

function get_userName($id){
	global $g_user;

	$sql = "SELECT CONCAT_WS(' ',{$g_user['prenom']}, {$g_user['nom']}) ";
	$sql.= "FROM ".T_USER;
	$sql.= " WHERE {$g_user['id']}=".$id;
 
	return squery($sql);
}

function get_userEmail($id){
	// Login = email...
	global $g_user;

	$sql = "SELECT {$g_user['login']} ";
	$sql.= "FROM ".T_USER;
	$sql.= " WHERE {$g_user['id']}=".$id;
 
	return squery($sql);
}
?>