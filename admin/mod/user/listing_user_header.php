<?php
	// Preparation du module xGrid pour listing Menu
	require_once 'class/xGrid.class.php';
	
	$sql = "SELECT {$g_user['id']} AS id";
	$sql.= " ,{$g_user['prenom']} AS prenom";
	$sql.= " ,{$g_user['nom']} AS nom";
	$sql.= " ,{$g_user['login']} AS login";
	$sql.= " ,{$g_user['desactiveON']} AS desactiveON";
	//$sql.= " ,{$g_user['id']} AS new_mdp";
	$sql.= " ,{$g_user['id']} AS supp";
	$sql.= " FROM ".T_USER;
	$sql.= " WHERE {$g_user['isAdministrateurON']}=1";
	
	$x=new xGrid('listing_user', $sql);
	$x->title('Listing des utilisateurs');
	
	$x->ajaxMode();
	$x->setResultPerPage(20);
	$x->allowAllSort();
	$x->allowAllFilter();
	$x->alternateRowBgd(TRUE);
	$x->fadedRowBgd(TRUE);
	$x->disableExcelExport(TRUE);
	$x->enableExcelExport(FALSE);
	$x->rowAttrSwap('id','id');
	
	$x->th('id','ID');
	
	$x->th('nom','Nom');
	
	$x->th('prenom','Prénom');
	
	$x->th('login','Login');
	
	$x->th('desactiveON','Actif');
	$x->disableFilter('desactiveON');
	$x->cellWidth('desactiveON',16);
	$img_ok = '<div style="text-align:center" id="zone_%0%"><a href="#" onclick="change_etat_user(%0%); return false;"><img src="pic/ok.png" style="border:none;" /></a></div>';
	$x_replace_ok=new xGridReplace(array('%0%'), array('id'), $img_ok);
	$img_ko = '<div style="text-align:center" id="zone_%0%"><a href="#" onclick="change_etat_user(%0%); return false;"><img src="pic/cancel.png" style="border:none;" /></a></div>';
	$x_replace_ko=new xGridReplace(array('%0%'), array('id'), $img_ko);
	$x_case=new xGridCase();
	$x_case->add('desactiveON',0,$x_replace_ok);
	$x_case->add('desactiveON',1,$x_replace_ko);
	$x->caseReplace('desactiveON', $x_case);	
	
	$x->th('supp',' ');
	$x->disableFilter('supp');
	$x->cellWidth('supp',90);
	$param = array();
	$param['label'] = 'Supprimer';
	$param['mypic'] = 'SUPPR';
	$param['xgrid'] = true;
	$param['onclick'] = 'supp_user(%0%);';
	$btn_edit = ubtn($param);
	$x_replace=new xGridReplace(array('%0%'), array('id'), $btn_edit);
	$x->replace('supp', $x_replace);	
	
	$js="load_page('index.php?to=user&idUser=%0%');";
	$x_replace=new xGridReplace(array('%0%'), array('id'), $js);
	$x->jsOnDblClick('dblclick', $x_replace);
	
	$html = $x->build();
	
?>