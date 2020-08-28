<?

function alert($value,$class){
	$alert='<div class="'.$class.'">';
	$alert.='<div id="alert_title">Message</div><div id="alert_content">';
	$alert.=$value;
	$alert.='</div></div>';
	return $alert;
}//end of alert


function dbug($data, $text=''){

	$section = str_replace(' ', '_', trim($text));
	$displayAction = "montre('". $section ."', 'display_". $section ."', '". $section ."')";
	$display = 'block';
	?>
	<div style="border:2px solid; border-color:#FF0000; width:95%; background-color:#DDDDDD; padding:2px; margin-bottom:1px; text-align:left">
	<?
	if(!empty($text)){
	?>
	<div onClick="<? echo $displayAction; ?>" onMouseOver="this.style.cursor='pointer'" style="background-color:#CCCCCC; width:100%; font-weight:bold; padding:1px"><? echo $text; ?></div><?
		$display = 'none';
	}
	?>
	<div id="<? echo $section; ?>" style="display:<? echo $display; ?>">
		<pre><? print_r($data); ?></pre>
	</div>
	</div>
	<?
}

function dbug_sql($sql=FALSE,$comment='',$invar=FALSE){
	global $page_debug;
	if(!$sql) global $sql;
	$etape_prec = getmicrotime();
	$result=query($sql);
	$temps_ecoule = ($etape_prec) ? round((getmicrotime() - $etape_prec)*1000) : 0;
	while($r[]=mysql_fetch_assoc($result)){}
	
	$tr='';
	$sql=explode_sql($sql);
	$tr.='<tr><th>SQL:<br/>( exécuté en <span style="font-size:12px">'.$temps_ecoule.'</span> ms)</th><td colspan="'.(count($r[0])-1).'">'.$comment.'<pre>'.$sql.'</pre></td></tr>';
	$tr.=build_tr($r[0],TRUE);
	foreach($r as $this_row) $tr.=build_tr($this_row);
	$table='<table class="dbug_sql" border="1">'.$tr.'</table>';
	if($invar){
			$page_debug.=$table;
		}
		else{
			echo $table;
		}
}

function getmicrotime() {
    // découpe le tableau de microsecondes selon les espaces
    list($usec, $sec) = explode(" ",microtime());

    // replace dans l'ordre
    return ((float)$usec + (float)$sec);
}

?>