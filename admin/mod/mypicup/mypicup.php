<?php	

	function mypic_upload(){
		$ret = false;
		$img_blob = '';
		$img_taille = 0;
		$img_type = '';
		$img_nom = '';
		$taille_max = 5000;
		$ret = is_uploaded_file ($_FILES['fic']['tmp_name']);
		if ( !$ret ){
			echo "Problème de transfert";
			return false;
		}else{
			// Le fichier a bien été reçu
			$img_taille = $_FILES['fic']['size'];
			if ( $img_taille > $taille_max ){
				echo "Trop gros !";
				return false;
			}
			
			$img_type = $_FILES['fic']['type'];
			$img_nom = $_FILES['fic']['name'];
			$img_blob = file_get_contents ($_FILES['fic']['tmp_name']);
			$req = "REPLACE INTO ".T_IMG." (".
			"code, nom, taille, mimetype, img_blob ".
			") VALUES (".
			"'".$_POST['form_code']."', ".
			"'".$img_nom."', ".
			"'".$img_taille."', ".
			"'".$img_type."', ".
			// N'oublions pas d'échapper le contenu binaire
			"'".addslashes ($img_blob)."') ";
			query($req);
			return true;
		
		}
	}
	
	if ( isset($_FILES['fic']) ){
		mypic_upload();
	}
		
	
	$page_form = '<form enctype="multipart/form-data" action="#" method="post">';
	$page_form.= build_input('MAX_FILE_SIZE',5000,'hidden');
	$page_form.= build_input('form_code');
	$page_form.= build_input('fic','','file','size="50"');
	$page_form.= build_input('upload','Envoyer','submit');
	$page_form.= '</form>';
	$page_form = build_fieldset($page_form,'Ajouter une icône en base');
	
	
	$html = '<br/>';
	$req = "SELECT nom, id,code ".
	"FROM ".T_IMG." ORDER BY code";
	$ret = query($req);
	while ( $col = mysqli_fetch_row($ret) ){
		$html.= '<div style="float:left;width:150px;height:32px;">'."<a href=\"mypic.php?id=".$col[1].
		"\">".mypic($col[2], $col[0], ' style="vertical-align:-45%;"')."</a>".$col[2]."</div>";
	}
	
	// Mise en page
	$maPage = new page();
	$maPage->build_content($page_form.$html);
	$maPage->show();
?>
