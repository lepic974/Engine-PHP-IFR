<?php

// formulaire de connexion 
$tmp_labels[]='<img src="pic/interface/icone_user.png" />';
$tmp_fields[]=build_input('form_login',TRUE,'','size="30" class="input_login" autocomplete="off" ');
$tmp_labels[]='<img src="pic/interface/icone_lock.png" />';
$tmp_fields[]=build_input('form_pass',TRUE,'password','size="30" class="input_login" autocomplete="off" ');
$tmp_labels[]='';
$tmp_fields[]=build_apply('Se connecter');

$page_form = build_fieldset(build_formTable($tmp_labels,$tmp_fields),'<b>Bienvenue,</b> Connectez-vous',TRUE);

if(isset($error_msg) && !empty($error_msg)){
	$html = build_erreur_msg($error_msg);
}else{
	$html = '';
}
	
$html.= '<table style="margin:auto;margin-top:250px;" cellspacing="0" cellpadding="0" border="0">';
$html.= '	<tr>';
$html.= '		<td>';
$html.= '			'.wrap_form($page_form,'form','',URLSELF,true);
$html.= '		</td>';
$html.= '	</tr>';
$html.= '</table>';

$maPage = new page();
$maPage->build_content($html);
$maPage->show();

?>