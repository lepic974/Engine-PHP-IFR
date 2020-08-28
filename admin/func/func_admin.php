<?php

function manage_ui(){
	global $g_interface;
	if(isset($_SESSION[USERSESSION]['user'])){
		$js = '<script type="text/javascript">';
		$js.= '		$(document).ready(function(){'.PHP_EOL;
		
		$data_menu = squeryArr("SELECT * FROM ".T_UI." WHERE {$g_interface['fk_user']}=".$_SESSION[USERSESSION]['user']." AND {$g_interface['div_interface']}='zone_menu'");
		if($data_menu['offset_x'])
			$js.= '		$("#'.$data_menu[$g_interface['div_interface']].'").offset({ top: '.$data_menu['offset_y'].', left: '.$data_menu['offset_x'].' });'.PHP_EOL;
			
		$data_info = squeryArr("SELECT * FROM ".T_UI." WHERE {$g_interface['fk_user']}=".$_SESSION[USERSESSION]['user']." AND {$g_interface['div_interface']}='zone_info_login'");
		if($data_info['offset_x'])
			$js.= '		$("#'.$data_info[$g_interface['div_interface']].'").offset({ top: '.$data_info['offset_y'].', left: '.$data_info['offset_x'].' });'.PHP_EOL;
			
		$data_main = squeryArr("SELECT * FROM ".T_UI." WHERE {$g_interface['fk_user']}=".$_SESSION[USERSESSION]['user']." AND {$g_interface['div_interface']}='main_div'");
		if($data_main['offset_x'])
			$js.= '		$("#'.$data_main[$g_interface['div_interface']].'").offset({ top: '.$data_main['offset_y'].', left: '.$data_main['offset_x'].' });'.PHP_EOL;

		if(isset($_GET['to'])){
			if($_GET['to'] == 'site' || $_GET['to'] == 'page'){
				$js.= '		$("#menu").accordion({ collapsible: true, heightStyle: "content", active: 0 });'.PHP_EOL;
			}elseif($_GET['to'] == 'listing_user' || $_GET['to'] == 'user' || $_GET['to'] == 'param'){
				$js.= '		$("#menu").accordion({ collapsible: true, heightStyle: "content", active: 1 });'.PHP_EOL;
			}else{
				$js.= '		$("#menu").accordion({ collapsible: true, heightStyle: "content", active: false });'.PHP_EOL;
			}
		}else{
			$js.= '		$("#menu").accordion({ collapsible: true, heightStyle: "content", active: false });'.PHP_EOL;
		}

		// Gestion Image de fond
		$js.= '		$.vegas({'.PHP_EOL;
		$name_bg = squery("SELECT {$g_interface['background']} FROM ".T_UI." WHERE {$g_interface['fk_user']}=".$_SESSION[USERSESSION]['user']." AND {$g_interface['div_interface']}='background'");
		if(is_file('../pic/upload/interface/'.$name_bg))
			$js.= '			src:"../pic/upload/interface/'.$name_bg.'",'.PHP_EOL;
		else
			$js.= '			src:"pic/interface/bg_administration.png",'.PHP_EOL;
    	$js.= '			fade:1000'.PHP_EOL;
		$js.= '		});'.PHP_EOL;
		
		
		$js.= '	});'.PHP_EOL;
		$js.= '</script>'.PHP_EOL;
		
		return $js;
	}else{
		$js = '<script type="text/javascript">';
		$js.= '		$(document).ready(function(){'.PHP_EOL;
		$js.= '			$.vegas({'.PHP_EOL;
		$js.= '				src:"pic/interface/bg_login.png",'.PHP_EOL;
    	$js.= '				fade:1000'.PHP_EOL;
		$js.= '			});'.PHP_EOL;	
		$js.= '		});'.PHP_EOL;
		$js.= '</script>'.PHP_EOL;	

		return $js;
	}
	
}

function get_userName($id){
	global $g_user;

	$sql = "SELECT CONCAT_WS(' ',{$g_user['prenom']},{$g_user['nom']}) ";
	$sql.= "FROM ".T_USER;
	$sql.= " WHERE {$g_user['id']}=".$id;
 
	return squery($sql);
}


function getPaysName($id_pays = 0){
	global $g_pays;

	if($id_pays == 0)
		return '';
	return squery("SELECT {$g_pays['pays']} FROM ".T_PAYS." WHERE {$g_pays['id']}=".$id_pays);

}



function getSiteParam($code){
	global $g_parametre;
	
	$value = squery("SELECT {$g_parametre['value']} FROM ".T_PARAMETRE." WHERE {$g_parametre['code']}='".$code."' LIMIT 1");
	if($value)
		return stripslashes($value);
	else
		return '';
}

function setSiteParam($code,$value){
	global $g_parametre;
	// Verification code
	if(squery("SELECT id FROM ".T_PARAMETRE." WHERE {$g_parametre['code']}='".$code."'")){
		squery("UPDATE ".T_PARAMETRE." SET {$g_parametre['value']}='".addslashes($value)."' WHERE {$g_parametre['code']}='".$code."'");
	}else{
		squery("INSERT INTO ".T_PARAMETRE." ({$g_parametre['value']},{$g_parametre['code']}) VALUES ('".addslashes($value)."','".$code."')");
	}
}

function getArrayLang(){
	global $g_lang;
	$sql = "SELECT * FROM ".T_LANG;
	$rs = mysqli_query($GLOBALS["___mysqli_ston"], $sql);
	
	$list = array();
	if($rs){
		while($data = mysqli_fetch_assoc($rs)){
			$list[$data[$g_lang['id']]] = $data[$g_lang['flag']];
		}
	}
	return $list;
}


?>