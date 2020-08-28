<?php
function show_debug(){
	echo '<hr/><br/><br/>';
	echo '<div style="width:80%;margin:auto;border:3px solid red;background-color:#DDDDDD;color:#000000;padding:10px;">';
	echo ' <b>debug visible uniquement pour les développements....</b><br/><br/><pre>';
	if(isset($_SESSION))
		print_r($_SESSION);
	echo '</pre></div>';
}

function isProd() {
	if(defined('IS_PROD') && IS_PROD) {
		return true;
	}
	else return false;
}

function isCachingEnabled() {
	if(defined('CACHING_ENABLE') && CACHING_ENABLE && extension_loaded('apc')) {	// apc is the only caching method) 
		return true;
	}
	else return false;
}

function dir_exists($dirname,$perms = 0777) {
	if(file_exists($dirname)) {
		if(!is_writable($dirname)) {
			chmod($dirname,$perms);
		}
	}
	else {
		mkdir($dirname,$perms);
	}
}

function getmicrotime() {
    // découpe le tableau de microsecondes selon les espaces
    list($usec, $sec) = explode(" ",microtime());

    // replace dans l'ordre
    return ((float)$usec + (float)$sec);
}

function array_flatten_recursive($a){
	$s='';
	foreach($a as $k=>$v){
		if(!empty($s)) $s=$s.', ';
		if(is_array($v)){
			$s.='array('.array_flatten_recursive($v).')';
		}else{
			
			$s.='['.$k.']=>'.$v;
		}
	}
	return $s;
}

function logif($check,$param=''){
	if($check){
		$r=build_backtrace_log_array(debug_backtrace(),$param);
		$error_id=sql_simple_insert('l_iflog', $r );
	}
	return $check;
}

function exitif($check=1,$reason=''){
	if($check){
		$r=build_backtrace_log_array(debug_backtrace(), $reason);
		if(empty($reason)) $reason='Action impossible ou interdite';
		$error_id=sql_simple_insert('l_iflog', $r );
		// color:white;background:darkblue;
		// style="font-family:System;position: absolute;width: 50em;margin-left: -15em;left: 50%;height: 4em;margin-top: -2em;top: 50%;text-align:center;"
		$v_s_msg = '';
		$v_s_msg.= '<div style="width:400px;">';
		$v_s_msg.= '<span>ID non valide ou restriction des droits d\'accès.</span>';
		$v_s_msg.= '<br /><br/><br /><div  align="center">Détail : <b>'.utf8_decode($reason).'</b>.</div>';		
		$v_s_msg.= '<br/><br/>Si besoin (bug bloquant), contactez l\'administrateur ERP en lui communiquant<br />';
		$v_s_msg.= ' le code de sortie : <b>#'.$error_id.'</b>.';
		$v_s_msg.= '</div>';
		
		?>
		<link rel="stylesheet" type="text/css" href="css/common.css"/>
		<body  style="background: transparent url(css/bg_iOne.png) repeat-x scroll 0;">
		<table class="headeriOne" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td class="headeriOne_iOne">&nbsp;</td>
				<td class="headeriOne_emi">&nbsp;</td>
			</tr>
		</table>  
		<!--XDBUG-->
		<br/><br/>
		
		<div align="center">
			<?php
			if(function_exists("build_fieldset")) { 
				echo build_fieldset($v_s_msg,'PROBLEME');
			} else {
				echo $v_s_msg;
			} 
			?>		
		</div>	 			
 		</body>
 		<?php
		exit();
	}
}

function dbug_vars($dbug_vars){
	//  $dbug_vars=get_defined_vars();
	// $dbug_vars=$GLOBALS;
	$filter=array('GLOBALS','page','word','halt','child_param');
	foreach($filter as $val)
		unset($dbug_vars[$val]);
	foreach($dbug_vars as $key => $val){
		if(substr($key,0,2)=='f_' OR substr($key,0,1)=='_' OR substr($key,0,5)=='HTTP_'){
			unset($dbug_vars[$key]);
		}
	}
	return $dbug_vars;
}

function dbug_form(){
	$dbug_form=array();
	foreach($GLOBALS as $key => $val){
		if(substr($key,0,5)=='form_'){
			$dbug_form[$key]=$val;
		}
	}
	return $dbug_form;
}

function trace(){
	foreach($GLOBALS as $key =>$value)
		echo $key.'=>'.$value.'<br/>';
}

function trace2file($output,$trace_file='../trace.htm',$timestamp=TRUE){
		if(!is_file($trace_file)) return false;
		if (is_writable($trace_file)) {
		   if (!$handle = fopen($trace_file, 'a+')){
		   	//echo 'cant open';
		   	exit;
		   } 
		   if(is_array($output)){
		   		$output_array='<br/>';
		   		foreach($output as $key => $val){
		   			$output_array.=$key.'>'.$val.'<br/>';
		   		}
		   		$output_array.='<br/>';
		   		$output=&$output_array;
		   }
		   $timestamp=($timestamp ? '['.date("H:i:s",mktime()).']' : '' );
		   if (fwrite($handle, $timestamp.$output.'<br/>') === FALSE) {exit;} // can't write
		   fclose($handle);
		}else{
			//echo 'cant write';
		   exit; // no writable
		}
}

function dbug($var='',$return=FALSE){
	
	if(is_object($var)){
		echo '<pre style="color:#FF0000">';
		var_dump($var);
		echo '</pre>';
		return '';
	}
	if(is_array($var)){
		// print_r no screen flush
		ob_start();
		echo '<pre>';print_r($var);echo '</pre>';
		$dbug=ob_get_contents();
		ob_end_clean();
		
		$dbug='<div class="debug">[debug:]'.$dbug.'&nbsp;</div>';
		if($return){
			
			return $dbug;
		}else{
			echo $dbug;
		}
	}else if($var===false){
		echo '<div class="debug">[debug:]FALSE&nbsp;</div>';

	}else{
		$dbug='<div class="debug">[debug:]'.$var.'&nbsp;</div>';
		if($return){
			return $dbug;
		}else{
			echo $dbug;
		}
	}
}	

function dbug_date($var='',$return=FALSE) {
	$value = '';
	if(is_array($var)){
		foreach($var as $key => $date) {
			$value.=date("d/m/Y",$date).'<br />';
		}
	} else {
		$value.=date("d/m/Y",$var).'<br />';
	}
	$dbug='<div class="debug">[debug:]'.$value.'&nbsp;</div>';
	if($return){
		return $dbug;
	}
	else{
		echo $dbug;
	}
}

function build_tr($cells,$th=FALSE){
	if(is_array($cells)){
		$t='td';
		if($th) {$t='th';$cells=array_keys($cells);}
		return '<tr><'.$t.'>'.implode('</'.$t.'><'.$t.'>',$cells).'</'.$t.'></tr>';
	}
}

function explode_sql($sql){
	$br_words=array(' FROM ',' LEFT JOIN ',' INNER JOIN ',' RIGHT JOIN ',' INSERT ',' VALUE ',' WHERE ',' ORDER ',' (SELECT ',' GROUP ');
	$tab_words=array(' AND ',' OR ');
	$sql=str_replace(CHR(10),"",$sql); $sql=str_replace(CHR(13),"",$sql); 
	foreach($br_words as $value)
		$sql=str_ireplace($value,"\n".$value,$sql);
	foreach($tab_words as $value)
		$sql=str_ireplace($value.' ',"\n\t".$value.' ',$sql);
	$sql=str_ireplace(', ',"\n\t, ",$sql);
	return $sql;
}

function dbug_sql($sql=FALSE,$comment='',$invar=FALSE){
	global $page_debug;
	if(!$sql) global $sql;
	$etape_prec = getmicrotime();
	$result=query($sql);
	$temps_ecoule = ($etape_prec) ? round((getmicrotime() - $etape_prec)*1000) : 0;
	while($r[]=mysqli_fetch_assoc($result)){}
	
	$tr='';
	$sql=explode_sql($sql);
	$tr.='<tr><th>SQL:<br/>( exécuté en <span style="font-size:12px">'.$temps_ecoule.'</span> ms)</th><td colspan="'.(count($r[0])-1).'" style="color:black;">'.$comment.'<pre>'.$sql.'</pre></td></tr>';
	$tr.=build_tr($r[0],TRUE);
	foreach($r as $this_row) $tr.=build_tr($this_row);
	$table='<table class="dbug_sql">'.$tr.'</table>';
	if($invar){
			return $table;
		}
		else{
			echo $table;
		}
}

function trap($trap='BREAKPOINT'){
?>

<script type="text/javascript">
alert('<?php echo addslashes($trap);?>');
</script>

<?php
}

function dbug_flags($table, $r_table, $return=FALSE){
	$html='';
	if(!is_array($r_table) AND is_numeric($r_table)){ //row id sent
		$result=query("SELECT * FROM $table WHERE id=".$r_table);
		$r_table=mysqli_fetch_assoc($result);
	}
	#TABLE_SCHEM>
		$sql="SHOW COLUMNS FROM ".$table;
		$result=query($sql);
		$r_bol=array(); // boolean type = tinyint(1)
		while($r=mysqli_fetch_assoc($result)){
			if(substr($r['Type'],0,10)=='tinyint(1)') $r_bol[]=$r['Field'];
		}
		asort($r_bol);
	#<TABLE_SCHEM
	
	$html.='<div style="text-align:left;width:200px;">';
	foreach($r_bol as $key){
		$html.='<div style="';				
		if($r_table[$key]){
			$html.='background:#7f7;';
		}
		$html.='">'.$key.' = '.$r_table[$key];
		$html.='</div>';
	}
	$html.='</div>';
	if($return){
		return $html;		
	}else{
		$html='<fieldset><legend>Flags table '.$table.'</legend>'.$html.'</fieldset>';
		echo $html;
	}				
}

function get_code_tableJoinList($table,$param='') {		
	$code_used=array();
	$code_prefix='code_'.str_replace('_','',substr($table,2));
	 	
	$result=query("SHOW TABLES");
	while($r=mysqli_fetch_array($result)){		
		if(substr($r[0],0,2)=='t_'){
			$checkTable = TRUE;
			if(isset($param['ignoreSameTable'])&& stripos($r[0],$table) === 0) {				
				$checkTable = FALSE;								
			} else if(isset($param['OnlySameTable'])) {
				if(stripos($r[0],$table) === 0) {
					$checkTable = TRUE;
				} else {
					$checkTable = FALSE;		
				}
			}
			//dbug($r[0].' '.$checkTable);
			if($checkTable == TRUE) {
				$result2=query("SHOW COLUMNS FROM ".$r[0]);
				$cols=array();
				// on remplit un tableau avec la liste des colonnes
				while($r2=mysqli_fetch_array($result2)){
					if(substr($r2[0],0,5)=='code_'){
						$tmp_col=strtolower($r2[0]);
						$cols[]=$tmp_col;
					}				
				}
				// on cherche la code dans la liste des colonnes
				if(in_array($code_prefix,$cols)){ 
					$code_used[]=$r[0];
				}		
			}
		}
	}
	return $code_used;
}
?>