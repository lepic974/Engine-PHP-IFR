<?php
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

function build_fieldset($fieldset_content,$legend='',$table=TRUE) {
    if(empty($legend)) {
        $legend = '&nbsp;';
    }

    if($table){
        return '<table><tr><td>'.build_cadre($fieldset_content,$legend).'</td></tr></table>';
    }else{
        return build_cadre($fieldset_content,$legend);
    }
}

function build_cadre($info_content,$legend){
    $content = '<div class="gray_frame">';// style="height:100%">';
    $content.= '	<div class="title_gray_frame">'.$legend.'</div>';
    $content.= '	<hr class="sep_titre" />';
    $content.= '	'.$info_content;
    $content.= '</div>';
    return $content;
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
    $possible = "0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@";

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

function build_r_from_id($table,$id,$fetch_type='assoc'){
    $sql="SELECT * FROM ".$table." WHERE `id` ='".$id."' LIMIT 1";
    $result=query($sql);
    switch($fetch_type){
        case 'assoc':
            return mysqli_fetch_assoc($result);
            break;
        case 'array':
            return mysqli_fetch_array($result);
            break;
        case 'row':
            return mysqli_fetch_row($result);
            break;
    }

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

function build_erreur_msg($msg){
    $result = '<div class="erreur_message" onclick="hide_me(this);">';
    $result.= '	<table style="width:100%;height:100%;" cellspacing="0" cellpadding="0" border="0">';
    $result.= '		<tr>';
    $result.= '			<td style="width: 60px;text-align:center;" valign="center">';
    $result.= '				<img src="'.PICTURE_FOLDER.'/icons/erreur.png" alt="erreur" title="erreur"/>';
    $result.= '			</td>';
    $result.= '			<td style="text-align:center;">';
    $result.= '				<span style="color:#FFFFFF;">';
    $result.= '					'.$msg;
    $result.= '				</span>';
    $result.= '			</td>';
    $result.= '		</tr>';
    $result.= '	</table>';
    $result.= '</div>';

    return $result;
}

function build_valid_msg($msg){
    $result = '<div class="valid_message" onclick="hide_me(this);">';
    $result.= '	<table style="width:100%;height:100%;" cellspacing="0" cellpadding="0" border="0">';
    $result.= '		<tr>';
    $result.= '			<td style="width: 60px;text-align:center;" valign="center">';
    $result.= '				<img src="'.PICTURE_FOLDER.'/icons/valid.png" alt="valid" title="valid"/>';
    $result.= '			</td>';
    $result.= '			<td style="text-align:center;">';
    $result.= '				<span style="color:#000000;">';
    $result.= '					'.$msg;
    $result.= '				</span>';
    $result.= '			</td>';
    $result.= '		</tr>';
    $result.= '	</table>';
    $result.= '</div>';

    return $result;
}

function build_formTable($labels,$fields=array(),$legend='',$widthClass=true,$tableEncaps=FALSE){
    $table='';
    if($tableEncaps) $table.='<table><tr><td>';

    $table.='<table width="100%" border="0" cellspacing="0" cellpadding="0">';

    $array_tab = array();
    if(is_array($labels)){
        foreach($labels as $key => $value){
            $ligne ="\n\n".'<tr>';
            if(isset($fields[$key]) && $fields[$key]==='th'){
                $ligne.='<th class="form_label_colspan" colspan="2">'.$value.'</th>';
            }elseif(isset($fields[$key]) && $fields[$key]==='tdmerge'){
                $ligne.='<td class="form_label_colspan" colspan="2" align="center">'.$value.'</td>';
            }else{
                if(empty($value)){
                    $ligne.='<td></td>';
                    $ligne.='<td class="form_field_empty">'.(isset($fields[$key]) ? $fields[$key] : '&nbsp;').'</td>';
                }else{
                    $ligne.='<td '.(($widthClass)?' class="form_label" ':'').'>'.$value.'</td>';
                    $ligne.='<td '.(($widthClass)?' class="form_field" ':'').'>'.(isset($fields[$key]) ? $fields[$key] : '&nbsp;').'</td>';
                }

            }

            $array_tab[] = $ligne;
        }
        $sep_ligne = '</tr>';
        $sep_ligne.= '<tr style="height: 15px;">';
        $sep_ligne.= '	<td style="height: 15px;" colspan="2"></td>';
        $sep_ligne.= '</tr>';
        $table.= implode($sep_ligne, $array_tab);

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

?>