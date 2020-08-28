<?php
	$page_name = 'param';
	
	//---< BOF $_POST[] >---\\
	$error_msg = "";
	require $page_name.'_proc.php';
	//---< EOF $_POST[] >---\\	


	// Creation du formulaire
	$param_input_f = ' size="89%" style="width:89%;" ';
	$param_input_o = ' size="100%" style="width:98%;" ';
	$param_input_s = ' size="10%" style="width:10%;" ';
	
	$param_textarea = ' style="width: 98%; height: 75px;" ';

    $tmp_labels[]=label("Nom Site");
    $tmp_fields[]=build_input('nom_site',getSiteParam('nom_site'),'text',$param_input_o);

    $tmp_labels[]=label("Téléphone : ");
    $tmp_fields[]=build_input('tel_contact',getSiteParam('tel_contact'),'text',$param_input_o);

    $tmp_labels[]=label("Adresse 1 : ");
    $tmp_fields[]=build_input('adresse_1',getSiteParam('adresse_1'),'text',$param_input_o);

    $tmp_labels[]=label("Adresse 2 : ");
    $tmp_fields[]=build_input('adresse_2',getSiteParam('adresse_2'),'text',$param_input_o);

    $tmp_labels[]=label("Ligne Coordonnées GPS : ");
    $tmp_fields[]=build_input('ligne_gps',getSiteParam('ligne_gps'),'text',$param_input_o);

    $tmp_labels[]=label("Adresse web Coordonnées GPS : ");
    $tmp_fields[]=build_input('ligne_web_gps',getSiteParam('ligne_web_gps'),'text',$param_input_o);


    $tmp_labels[]=label("Ligne Crédit : ");
    $tmp_fields[]=build_input('credit',getSiteParam('credit'),'text',$param_input_o);

    $tmp_labels[]=label("URL Twitter : ");
    $tmp_fields[]=build_input('twitter',getSiteParam('twitter'),'text',$param_input_o);

    $tmp_labels[]=label("URL Instagram : ");
    $tmp_fields[]=build_input('instagram',getSiteParam('instagram'),'text',$param_input_o);

    $tmp_labels[]=label("URL FaceBook : ");
    $tmp_fields[]=build_input('facebook',getSiteParam('facebook'),'text',$param_input_o);

    $tmp_labels[]=label("Meta Description (Home) : ");
    $tmp_fields[]=build_textarea('meta_description',getSiteParam('meta_description'),$param_textarea);

    $tmp_labels[]=label("Meta Keyword (Home) : ");
    $tmp_fields[]=build_textarea('meta_keyword',getSiteParam('meta_keyword'),$param_textarea);

    $tmp_labels[]=label("CGU : ");
    $tmp_fields[]=build_textarea('cgu',getSiteParam('cgu'),$param_textarea);

    $general = build_formTable($tmp_labels,$tmp_fields,'',false);
	unset($tmp_labels);
	unset($tmp_fields);
	
	$html_general = '<div id="tabs-general">';
	$html_general.= '	'.$general;
	$html_general.= '</div>';
	
	// Lien onglets;
	$html_link = '<ul>';
	$html_link.= '	<li><a href="#tabs-general">Informations Générales <img src="../pic/options.png" style="vertical-align:middle;"/></a></li> ';
	$html_link.= '</ul>';
	
	// Mise en forme onglets
	$html_onglet = '<div id="tabs">';
	$html_onglet.= '	'.$html_link.$html_general;
	$html_onglet.= '</div>';
	
	// Valid
	$param = array();
	$param['mypic'] = 'OK';
	$param['submit'] = true;
	$param['label'] = 'Sauvegarder';
	$btn_valid = '<div style="text-align:center;margin-top:15px;clear:both;margin-bottom:20px;">'.ubtn($param).'</div>';
	
	$html = $html_onglet.$btn_valid;

	$html = wrap_form($html,'form',' enctype="multipart/form-data" ',URLSELF,true);
?>