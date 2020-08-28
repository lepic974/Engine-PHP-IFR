<?php


function datetime2mktime($date){
	$date = explode(' ',$date);

	if(count($date) != 2)
		return 0;
	
	//---< Gestion des jours >---\\
	$day = explode('/',$date[0]);

	//---< Gestion des heures >---\\
	$hour = explode(":",$date[1]);

	if($day[0]>0 && $day[1]>0 && $day[2]>0 && $hour[0]>=0 && $hour[0]>=0)
		return mktime($hour[0],$hour[1],0,$day[1],$day[0],$day[2]);
	else
		return 0;
}

function date2mktime($date){
	$date=explode('/',$date);
	foreach($date as $key => $val){
		$date[$key]=(int)$val; 
	}
	if(count($date) == 3) {
		if($date[0]>0 && $date[1]>0 && $date[2]>0){	
			return mktime(0,0,0,$date[1],$date[0],$date[2]);
		}
	} 
	return 0;	
}

function mb_ucfirst($p_s_text,$p_s_encoding='UTF-8') {
	$p_s_text = mb_strtolower($p_s_text,$p_s_encoding);
	return mb_strtoupper(substr($p_s_text,0,1),$p_s_encoding).substr($p_s_text,1);
}

?>