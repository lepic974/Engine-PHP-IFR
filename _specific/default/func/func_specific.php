<?php

function getFbImage($data_get){
	if(empty($data_get)){
		// Page d'accueil
		
		$rs = '<meta property="og:updated_time" content="'.time().'" />';
		$rs.= '<meta property="og:image" content="http://www.urban-legion.com/_specific/urban-legion/pic/logo_ul_fb.jpg" />';
		return $rs;
	}
	
	if(!isset($data_get['to'])){
		$rs = '<meta property="og:updated_time" content="'.time().'" />';
		$rs.= '<meta property="og:image" content="http://www.urban-legion.com/_specific/urban-legion/pic/logo_ul_fb.jpg" />';
		return $rs;
	}else{
		$to = $data_get['to'];
		$image_bg = '';
		switch ($to) {
			case 'shop':
				if(isset($data_get['id_produit'])){
					global $g_produit;
					
					$nom = squery("SELECT {$g_produit['image_1']} FROM ".T_PRODUIT." WHERE {$g_produit['id']}=".$data_get['id_produit']);
					$image_bg = '<meta property="og:image" content="http://www.urban-legion.com/pic/upload/produit/'.$nom.'" />'	;
				}else{
					if(isset($data_get['id_categorie'])){
						$id_categorie = $data_get['id_categorie'];
					}else{
						$id_categorie = 1;
					}
					
					if(isset($data_get['id_ss_categorie'])){
						$id_ss_categorie = $data_get['id_ss_categorie'];
					}else{
						$id_ss_categorie = getDefautSsCategorie($id_categorie);
					}
					
					$data = getDataInterfaceShop($id_categorie,$id_ss_categorie);
					$image_bg = '<meta property="og:image" content="http://www.urban-legion.com/pic/upload/ss_categorie/'.$data['image_bg'].'" />';
				}
			break;
				
			case 'galerie':
			case 'partenaire':
			case 'contact':
				$nom = getSiteParam('bg_'.$to);
				$image_bg = '<meta property="og:image" content="http://www.urban-legion.com/_specific/'.file_get_contents('param.ini').'/pic/interface/'.$nom.'" />';
			break;
			
			default:
				$image_bg = '<meta property="og:image" content="http://www.urban-legion.com/_specific/urban-legion/pic/logo_ul_fb.jpg" />';
			break;
		}
		$image_bg.= '<meta property="og:updated_time" content="'.time().'" />';
		return $image_bg;
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

function truncate_mini_title($chaine,$length = 15){
	if(strlen($chaine)>=$length){
		$chaine = mb_substr($chaine, 0, $length, 'UTF-8'); 
		$chaine.= '...';
	}	
	return $chaine;
}

function getListLangue(){
	global $g_lang;
	$sql = "SELECT * FROM ".T_LANG;
	$rs = mysqli_query($GLOBALS["___mysqli_ston"], $sql);
	
	$list = array();
	if($rs){
		while($data = mysqli_fetch_assoc($rs)){
			$list[$data[$g_lang['id']]] = $data[$g_lang['lang']];
		}
	}
	return $list;	
}

function getListPays(){
	global $g_pays;
	$sql = "SELECT * FROM ".T_PAYS;
	$rs = mysqli_query($GLOBALS["___mysqli_ston"], $sql);
	
	$list = array();
	if($rs){
		while($data = mysqli_fetch_assoc($rs)){
			$list[$data[$g_pays['id']]] = $data[$g_pays['pays']];
		}
	}
	return $list;	
}

?>