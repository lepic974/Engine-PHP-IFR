<?php
function inc_cpt($table, $filed, $id){
	squery("UPDATE ".$table." SET {$filed}={$filed}+1 WHERE id=".$id);	
}

function isiPhone(){
	if (stristr($_SERVER['HTTP_USER_AGENT'],'iPhone') ||	stristr($_SERVER['HTTP_USER_AGENT'],'iPod') ||	stristr($_SERVER['HTTP_USER_AGENT'],'iPad')){
		return true;	
	}else{
		return false;
	}
}

function isSafari(){
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (stripos( $user_agent, 'Chrome') !== false){
       return false;
    }elseif (stripos( $user_agent, 'Safari') !== false){
        return true;
    }
    return false;
}

function isMobile(){
	if (stristr($_SERVER['HTTP_USER_AGENT'],'iPhone') ||	stristr($_SERVER['HTTP_USER_AGENT'],'iPod') ||
        stristr($_SERVER['HTTP_USER_AGENT'],'android') || stristr($_SERVER['HTTP_USER_AGENT'],'blackberry') ||
        stristr($_SERVER['HTTP_USER_AGENT'],'Windows Phone') || stristr($_SERVER['HTTP_USER_AGENT'],'symbian') ||
        stristr($_SERVER['HTTP_USER_AGENT'],'series60') || stristr($_SERVER['HTTP_USER_AGENT'],'palm')){
		return true;	
	}else{
		return false;
	}
}

function build_alert(){
	
	$html = '<div class="jqmAlert" id="alert">';
	$html.= '	<div id="ex3b" class="jqmAlertWindow">';
    $html.= '		<div class="jqmAlertTitle clearfix">';
    $html.= '			<h1>Information</h1><a href="#" class="jqmClose"><em>Close</em></a>';
	$html.= '		</div>';
	$html.= '		<div class="jqmAlertContent"></div>';
	$html.= '	</div>';
	$html.= '</div>';
	
	return $html;
}

function html2pdf($html_body, $param=array()) {

	$html='<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';

	$html.='<style type="text/css">';
	$html.='@page {';
	/*if(!isset($param['noFooter'])) {
		$html.='	@bottom-center {
	     				-html2ps-html-content: "'.$footer_text.'";
	  					font-size:9px;
	  					font-family: arial;
					}';
	}*/
	$html.='	@bottom-left {
  					font-family: arial;
    				content: "Fax : 01 47 14 29 59";
  				}
	';
	$footer_text = $param['code_totem'].'_'.date('d/m/Y',time());
	$html.='	@bottom-right {
  					font-family: arial;
    				content: "'.$footer_text.'";
  				}
			}
	';
	$html.='body{background:white !important; margin:0px;font-size:14px;font-family: arial;}';

	$html.='</style>';

	$html.='</head>'.$html_body;
	unset($html_body);
	$html.='</html>'; 
	
	if(isset($_GET['fast_mode']) OR ( isset($param['fast_mode']) AND $param['fast_mode'] )){
		echo $html;
		return '';
	}

	include_once('class/pdfClass.php');	
	$path_to_pdf=$footer_text;
	$base_path='';
	$outputType='download';

	if(isset($param['outputType'])) $outputType=$param['outputType']; 
	if(isset($param['pdfname'])) $path_to_pdf=cleanStringForFile($param['pdfname'],($param['outputType']!='file'),($param['outputType']!='file'));

	$pipeline = PipelineFactory::create_default_pipeline('', '');
	
	$pipeline->fetchers[] = new MyFetcherMemory($html, $base_path);
	
  	switch ($outputType) {
 		case 'browser':
 			$pipeline->destination = new DestinationBrowser($path_to_pdf);
   		break;
 		case 'download':
   			$pipeline->destination = new MyDestinationDownload($path_to_pdf);   			
   		break;
 		case 'file':
 			$pipeline->destination = new MyDestinationFile($path_to_pdf);
   			echo $path_to_pdf;
   	   break;
	};

	$baseurl = '';
	  
	$media =& Media::predefined('A4');
  	$media->set_landscape(isset($param['landscape']));
	  
	$media->set_margins(array('left'   => 3,
	                            'right'  => 3,
	                            'top'    => 3,
	                            'bottom' => 10+$bottomAdd));
	
	  global $g_config;
	  $g_config = array(
	  					
	                    'cssmedia'     => 'screen',
	                    'scalepoints'  => '1',
	                    'renderimages' => TRUE,
	                    'renderlinks'  => TRUE,
	                    'renderfields' => FALSE,
	                    'mode'         => 'html',
	                    'encoding'     => 'utf-8',
	                    'debugbox'     => FALSE,
	                    'pdfversion'   => '1.4',
	                  	'smartpagebreak' => 1,
	                    'output'       => 0
	                    );
	
	  $pipeline->configure($g_config);	  
	  $pipeline->process_batch(array($baseurl), $media);
	  
	  return $path_to_pdf;
}



function build_fieldset(&$fieldset_content,$legend='',$table=TRUE, $expand='',$autoCollapse= false,$callback_after=null) {
	if($autoCollapse) updateDefaultFieldsetExpandStatus($expand,0);
	
	if(empty($legend)) {
		$legend = '&nbsp;';
	}
	if($table){
		return '<table><tr><td>'.build_cadre($fieldset_content,$legend,$expand).'</td></tr></table>';		
	}else{
		return build_cadre($fieldset_content,$legend,$expand,$callback_after);	
	}
}


function ucftw($code){
	$value=tw($code);
	return mb_strtoupper(utf8_encode(substr( utf8_decode($value),0,1)) , "UTF-8").utf8_encode(substr( utf8_decode($value),1));
}

function mktime2dmy($mktime){
	if($mktime==0) return '';
	return date('d/m/Y', $mktime);	
}

function mktime2hm($mktime){
	if($mktime==0) return '';
	return date('H\hi',$mktime);	
}

function mypic($id, $alt='', $param=''){
	return build_img('mypic.php?id='.$id, $alt, $param);
}

function build_img($src,$alt="",$param=""){
	$html='';
	$html='<img src="'.$src.'" alt="'.$alt.'" '.$param.' />';
	return $html;
}

function sql_to_xls($sql=FALSE, $th_list=array()){
	$out='';	
	$bol='<tr><td>';
	$eol='</td></tr>';
	$separator='</td><td>';
	$result=query($sql);
	if(mysqli_num_rows($result)){
		$out.='<table class="result">';
		if(empty($th_list)){
			$r=mysqli_fetch_assoc($result);
			$th=array_keys($r);	
			$out.='<tr><th>'.implode('</th><th>',$th).'</th></tr>';
			$out.=$bol.implode($separator,$r).$eol;
		}else{
			$out.='<tr><th>'.utf8_decode(implode('</th><th>',$th_list)).'</th></tr>';
		}
		while($r=mysqli_fetch_row($result)){
			$out.=$bol.implode($separator,$r).$eol;
		}
		$out.='</table>';
	}else{
		return FALSE;
	}
	return $out;
}

function sql_to_csv($sql=FALSE,$separator=';'){
	$out='';
	$eol="\n";
	$result=query($sql);
	if(mysqli_num_rows($result)){
		$r=mysqli_fetch_assoc($result);
		$th=array_keys($r);	
		$out.=implode($separator,$th).$eol;
		$out.=implode($separator,$r).$eol;
		while($r=mysqli_fetch_row($result)){
			$out.=implode($separator,$r).$eol;
		}
	}else{
		return FALSE;
	}
	return $out;
}

function sql_to_htm($sql=FALSE, $th_list=array()){
	$out='';	
	$bol='<tr><td>';
	$eol='</td></tr>';
	$separator='</td><td>';
	$result=query($sql);
	if(mysqli_num_rows($result)){
		$out.='<table class="result">';
		if(empty($th_list)){
			$r=mysqli_fetch_assoc($result);
			$th=array_keys($r);	
			$out.='<tr><th>'.implode('</th><th>',$th).'</th></tr>';
			$out.=$bol.implode($separator,$r).$eol;
		}else{
			$out.='<tr><th>'.implode('</th><th>',$th_list).'</th></tr>';
		}
		while($r=mysqli_fetch_row($result)){
			$out.=$bol.implode($separator,$r).$eol;
		}
		$out.='</table>';
	}else{
		return FALSE;
	}
	return $out;
}


function magic_unquote($val){
	return stripslashes(str_replace('"',"''",$val));
}

function imgbol($if){
	if($if)
		return '<img src="pic/is_true.png" alt="" />';
	else
		return '<img src="pic/is_false.png" alt="" />';
}

function  format_date($timestamp, $format='d/m/Y H:i'){
	$date = '';
	if($timestamp != -1){
		$date = date($format, $timestamp);
	}	
	return $date;
}
		
function generatePassword ($length = 6){

  // start with a blank password
  $password = "";

  // define possible characters
  $possible = "0123456789abcdfghjkmnpqrstvwxyz@"; 
    
  // set up a counter
  $i = 0; 
    
  // add random characters to $password until $length is reached
  while ($i < $length) { 

    // pick a random character from the possible ones
    $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
        
    // we don't want this character if it's already in the password
    if (!strstr($password, $char)) { 
      $password .= $char;
      $i++;
    }

  }

  // done!
  return $password;

}


function id_exists($table,$id){
	$sql="SELECT id FROM ".$table." WHERE id='".$id."' LIMIT 1";
	$result=query($sql);
	return mysqli_num_rows($result);
}

function jump_to_location($getvar_to_trunc=FALSE){
	if($getvar_to_trunc)
		header("Location: ".html_entity_decode(trunc_get($getvar_to_trunc)));
	else
		header("Location: ".html_entity_decode(URLSELF));
    exit();
}

function btn($code_bouton,$replace='',$is_pop=TRUE,$code_image='', $id_image=''){
	$sql="SELECT btn.code,btn.lien,btn.libelle,img.id AS id_image FROM ".T_BOU." btn";
	$sql.=" LEFT JOIN ".T_IMG." img ON (btn.code_image =img.code )";
	$sql.=" WHERE btn.code='$code_bouton' LIMIT 1;";
	$result=query($sql);
	if(!mysqli_num_rows($result)){
		return '';
	}
	$r=mysqli_fetch_assoc($result);
	if(!empty($replace)){		
		if(is_array($replace)){
			$r['lien']=str_replace('%id%',$replace[0],$r['lien']);
			$r['libelle']=$replace[1];
		}
		else{
			$r['lien']=str_replace('%id%',$replace,$r['lien']);
		}
	}
	$no_js=TRUE;
	if( (substr($r['lien'],0,4)=='http') ){
		$url=$r['lien'];
		//$no_js=TRUE;
	}
	elseif( (substr($r['lien'],0,11)=='javascript:') ){
		$url=substr($r['lien'],11);
		$no_js=FALSE;
	}
	elseif(strpos($r['lien'],'?')){
		$url=$r['lien'];
		//$no_js=TRUE;
	}
	else{
		$url='engine.php?to='.$r['lien'];
	}

	if(strpos($r['lien'],'&amp;print')){
			$no_js=TRUE;
	}
	if($id_image==''){
		if($code_image != '') $id_image = get_id_from_primary(T_IMG,array($code_image));	
		else $id_image = $r['id_image'];
	}
	

	$param=array();
	if ($no_js)
		$param['url']=$url;
	else
		$param['onclick']=$url;
	$param['mypic']=$id_image;
	$param['label']=$r['libelle'];
	if($is_pop) $param['type']='pop';
		
	return ubtn_iOne($param);

}


function build_formTable($labels,$fields=array(),$legend='',$param='',$tableEncaps=FALSE){
	$table='';
	if($tableEncaps) $table.='<table><tr><td>';
	$table.="\n\n".(empty($legend) ? '':'<fieldset '.$param.'><legend>'.$legend.'</legend>');
	
	$table.='<table width="100%" border="0">';
	
	if(is_array($labels)){
		foreach($labels as $key => $value){
			$table.="\n\n".'<tr valign="top">';
			if(isset($fields[$key]) && $fields[$key]==='th'){
				$table.='<th class="form_label" colspan="2">'.$value.'</th>';
			}elseif(isset($fields[$key]) && $fields[$key]==='tdmerge'){
				$table.='<td class="form_label" colspan="2" align="center">'.$value.'</td>';
			}else{
				$table.='<td class="form_label_ione">'.$value.'</td>';
				$table.='<td class="form_field">'.(isset($fields[$key]) ? $fields[$key] : '&nbsp;').'</td>';
			}
			$table.='</tr>';
		}
	}else{
		$table.='<tr><td>'.$labels.'</td></tr>';
	}
	$table.='</table>';
	if(!empty($legend)) $table.='</fieldset>';
	if($tableEncaps) $table.='</td></tr></table>';
	return $table;
}

function wrap_form(&$form,$name='form', $param='',$action=URLSELF,$html=FALSE){
	if(!empty($form)){
		if(!$html) {
			echo "\n\n".'<form name="'.$name.'" id="'.$name.'" method="post" action="'.$action.'" '.$param.'>';
			echo $form;
			echo '</form>';
		} else return '<form name="'.$name.'" id="'.$name.'" method="post" action="'.$action.'" '.$param.'>'.$form.'</form>';
	}
}
	
function build_valid_url($url) {
	$url = urldecode($url);
	$url = str_replace(" ","",$url);
	if (strncmp($url, "http://", 7) != 0 && strncmp($url, "https://", 8) != 0) {
		return "http://".$url;
	}
	return $url; 
}

function formatNombre($nb) {
	return number_format($nb, 2, '.', ' ');
}		
		
function sp2nbsp($string){
	return str_replace(' ','&nbsp;',trim($string));
}

function get_rnd_iv($iv_len){
    $iv = '';
    while ($iv_len-- > 0) {
        $iv .= chr(mt_rand() & 0xff);
    }
    return $iv;
}

function md5_encrypt($plain_text, $iv_len = 16){
	$password = MAGIC_PRIVATE_KEY;
    $plain_text .= "\x13";
    $n = strlen($plain_text);
    if ($n % 16) $plain_text .= str_repeat("\0", 16 - ($n % 16));
    $i = 0;
    $enc_text = get_rnd_iv($iv_len);
    $iv = substr($password ^ $enc_text, 0, 512);
    while ($i < $n) {
        $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
        $enc_text .= $block;
        $iv = substr($block . $iv, 0, 512) ^ $password;
        $i += 16;
    }
    return base64_encode($enc_text);
}

function md5_decrypt($enc_text, $iv_len = 16){
	$password = MAGIC_PRIVATE_KEY;
    $enc_text = base64_decode($enc_text);
    $n = strlen($enc_text);
    $i = $iv_len;
    $plain_text = '';
    $iv = substr($password ^ substr($enc_text, 0, $iv_len), 0, 512);
    while ($i < $n) {
        $block = substr($enc_text, $i, 16);
        $plain_text .= $block ^ pack('H*', md5($iv));
        $iv = substr($block . $iv, 0, 512) ^ $password;
        $i += 16;
    }
    return preg_replace('/\\x13\\x00*$/', '', $plain_text);
}

function send_email($m,$debug=FALSE){
	
	require_once(PATH_TO_COMMON_FOLDER."phpmailer/class.phpmailer.php");
	include(PATH_TO_SPECIFIC_PARAM_FOLDER.'param_mail.php');
	
	if(isset($m['html'])) $mail->IsHTML($m['html']);

	if(isset($m['CharSet'])) $mail->CharSet=$m['CharSet']; 
	else $mail->CharSet='utf-8'; 
			
	if(isset($m['confirmReadingTo'])) $mail->ConfirmReadingTo = $m['confirmReadingTo']; 
	
	if(isset($m['header'])){		
		$mail->AddCustomHeader($m['header']);
	}	
	
	//=========== MAIL FROM ===========================
	if(isset($m['mailfrom'])){
		$mail->From = $m['mailfrom'];		
	}else{
		$mail->From =DEFAULT_MAIL_FROM;
	}
	
	if(isset($m['mailfromname'])){
		$mail->FromName = $m['mailfromname'];
	}else{
		$mail->FromName = DEFAULT_MAIL_NAME;
	}
	
	//=========== REPLY TO ============================
	if(isset($m['mailreplyto'])){
		$mail->AddReplyTo($m['mailreplyto']);
	}else{
		
		$mail->AddReplyTo(DEFAULT_MAIL_REPLY, DEFAULT_MAIL_NAME);		
	}
		
	//=========== MAIL TO =============================
	if(!empty($m['mailto'])) {
		if(is_array($m['mailto'])){
			foreach($m['mailto'] as $val){
				$mail->AddAddress($val);
			}
		} else {
			//dbug($m['mailto']);
			$mail->AddAddress($m['mailto']);
		}
	}else{
		$mail->AddAddress(DEFAULT_MAIL_TO);
	}

	//=========== MAIL TOBCC ==========================
	if(!empty($m['mailtobcc'])){
		if(is_array($m['mailtobcc'])){
			foreach($m['mailtobcc'] as $val){
				$mail->AddBCC($val);
			}
		} else {
			$mail->AddBCC($m['mailtobcc']);
		}
	}		
	
	//=========== MAIL ATTACHMENT======================
	if(isset($m['attachment'])){
		if(is_array($m['attachment'])){
			foreach($m['attachment'] as $val){
				$mail->AddAttachment($val['path'],$val['filename'],'base64',$val['mine']);
			}
		} else {
			$mail->AddAttachment($m['attachment']['path'],$m['attachment']['filename'],'base64',$m['attachment']['mine']);
		}
	}
	
	$mail->Subject = $m['subject'];
	$mail->Body    = $m['body'];
	
	if($debug){
		if(!$mail->Send()) {
			return FALSE;	
		}
		return TRUE;		
	} else {
		if($mail->Send()){
			return true;
		}else {
			return false;
		}
	}
}

function dateToMktime($stringDate,$dayEndTime=false,$format='FR') {
	$dayT = '';
	$monthT = '';
	$yearT = '';		
	$v_i_cpt_element_fill = 0;	
	
	for($i=0;$i <= strlen($stringDate);$i++) {
		$char = substr($stringDate,$i,1);
		if(is_numeric($char)) {
			if($v_i_cpt_element_fill == 0) {
				if($format == 'FR') $dayT.=$char;
				else $monthT.=$char;
			} elseif($v_i_cpt_element_fill == 1) {
				if($format == 'FR') $monthT.=$char;
				else $dayT.=$char;
			} elseif($v_i_cpt_element_fill == 2)	$yearT.=$char;
		} else $v_i_cpt_element_fill++;				
		if($i > 10) break;
	}
	if(!empty($dayT) && !empty($monthT) && !empty($yearT)) {
		if(checkdate($monthT,$dayT,$yearT)) {
			if($dayEndTime) return mktime(23,59,59,$monthT,$dayT,$yearT);
			else return mktime(0,0,0,$monthT,$dayT,$yearT);
		}
		else return false;
		
	} return false;
}

function getLastDay($month, $year){
	if((int)$month === 12){
		$year++;
		$month = 01;
	}else{
		$month++;
	}

	$timestamp = mktime(0, 0, 0, $month, 0, $year);
	$lastday = strftime('%d', $timestamp);

	return $lastday;
}

function date2word($mktime, $range=2){
	if($range>0){
		$today=mktime(0, 0, 0, date("m") , date("d"), date("Y"));
		if( mktime(0, 0, 0, date("m",$mktime) , date("d",$mktime), date("Y",$mktime)) == $today )
			return "Aujourd'hui";
	}
	if($range>1){
		$yesterday=mktime(0, 0, 0, date("m") , date("d") -1, date("Y"));
		if( mktime(0, 0, 0, date("m",$mktime) , date("d",$mktime), date("Y",$mktime)) == $yesterday )
			return "Hier";
	}
	return 'Le '.date('d/m/Y', $mktime);
}

function datetime2word($mktime, $range=2){
	return date2word($mktime, $range).' à  '.date(' H\hi',$mktime);
}

function monthNumToName($month) {
	$v_a_month = Array("", 
					"Janvier", 
					"Février", 
					"Mars", 
					"Avril", 
					"Mai", 
					"Juin", 
					"Juillet", 
					"Aout", 
					"Septembre",
					"Octobre", 
					"Novembre", 
					"Décembre");
	return (intval($month) > 0 && intval($month) < 13) ? $v_a_month[intval($month)] : "";
}

function dateUserFriendly(){
	$month = array("","Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre");
	$day = array("","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche");
	return $day[date('N')].' '.date('d').' '.$month[date('n')].' '.date('Y');
}

function AssociativeMonthsList() {
	$month = array();
	for($i=1;$i<=12;$i++) {
		$month[$i] = monthNumToName($i);		
	}
	return $month;
}

function daysBetweenDates($beginDate,$endDate,$countNoWorkDay=true,$countWeekEnd=true){
	global $f_ohd_noworkDay;
	
	$daysBetweenDates = 0;
	
	if( $endDate<$beginDate ){
		$tmpDate = $beginDate;
		$beginDate = $endDate;
		$endDate = $tmpDate;
	}
	
	// Convertion at 3:00am
	$beginDate = mktime(3,0,0,date('m',$beginDate),date('d',$beginDate),date('Y',$beginDate));
	$endDate = mktime(3,0,0,date('m',$endDate),date('d',$endDate),date('Y',$endDate));
	
	$daysInSeconds = $endDate-$beginDate;
	$daysBetweenDates = ceil($daysInSeconds/(3600*24));
	
	if( !$countNoWorkDay||!$countWeekEnd ){
		$noWorkDay = getColParam(T_OHD_NOWORKDAY, $f_ohd_noworkDay[4], $f_ohd_noworkDay[0]);
		foreach( $noWorkDay as $key=>$timestamp ){
			$noWorkDay[$key] = date('dm',$timestamp);
		}
		
		for($current_day=$beginDate ; $current_day<=$endDate+3600 ; $current_day+=3600*24 ){
			$number_JourSemaine = date('N',$current_day);	// Lundi: 1 ; Dimanche: 7
			if( !$countNoWorkDay&&in_array(date('dm',$current_day),$noWorkDay)||!$countWeekEnd&&$number_JourSemaine>5 ){
				$daysBetweenDates--;
			}
		}
	}
	
	return $daysBetweenDates;
}

function mktimeDayStart($timestamp=''){
	if(empty($timestamp))
		$timestamp = time();
		
	return mktime(0,0,0,date('m', $timestamp), date('d', $timestamp), date('y', $timestamp));
}

function mktimeDayEnd($timestamp=''){
	if(empty($timestamp))
		$timestamp = time();
		
	return mktime(23,59,59,date('m', $timestamp), date('d', $timestamp), date('y', $timestamp));
}

function getSiteParam($code){
	global $g_parametre;
	
	$value = squery("SELECT {$g_parametre['value']} FROM ".T_PARAMETRE." WHERE {$g_parametre['code']}='".$code."' LIMIT 1");
	if($value)
		return stripslashes($value);
	else
		return '';
}

function stripAccents($string){
    $table = array(
        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r'
    );
    
    return strtr($string, $table);
}

function clean_url_rewriting($string){
    $table = array(
        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', '.'=>'_', '?'=>'_', '!'=>'_', '°'=>'_', ' '=>'_', '-'=>'_', '"'=>'_',
    	 "'"=>'_'
    );
    
    return strtr($string, $table);
}
?>