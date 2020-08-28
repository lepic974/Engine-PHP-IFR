<?php

function ulink($param=array()){
	$html='';
	if(!isset($param['label'])) $param['label']='';
	#ONCLICK>
		$html_onclick='';
		#CUSTOM>
			if(isset($param['submit'])){
				$html_onclick.="ubtn_name(this);";
			}
		#<CUSTOM
		if(isset($param['onclick'])){
			$html_onclick.=trim($param['onclick'].';');		
		}
		if(isset($param['url'])){
			$html_onclick.="window.open('{$param['url']}');";
		}
		if(isset($param['a'])){
			$html_onclick.="document.location.href = ('{$param['a']}');";
		}
		if(!empty($html_onclick)) $html_onclick=' onclick="'.$html_onclick.'"';			
	#<ONCLICK
	#ONMOUSEOVER>
		$html_onmouseover='';
		if(isset($param['onmouseover'])){
			$html_onmouseover.=trim($param['onmouseover'].';');		
		}
		if(!empty($html_onmouseover))	$html_onmouseover=' onmouseover="'.$html_onmouseover.'"';
	#<ONMOUSEOVER
	#ONMOUSEOUT>
		$html_onmouseout='';
		if(isset($param['onmouseout'])){
			$html_onmouseout.=trim($param['onmouseout'].';');		
		}
		if(!empty($html_onmouseout)) $html_onmouseout=' onmouseout="'.$html_onmouseout.'"';	
	#<ONMOUSEOUT
	#STYLE>
		$html_style='';
		if(isset($param['style'])){
			$html_style.=trim($param['style'].';');		
		}
		if(!empty($html_style))	$html_style=' style="'.$html_style.'"';
	#<STYLE

	$html.='<span class="ulink"';	
			
	$html.=$html_style;
	$html.=(isset($param['name']) ? ' name="'.$param['name'].'"':'');
	$html.=(isset($param['title']) ? ' title="'.$param['title'].'"':'');
	$html.=(isset($param['param']) ? $param['param']:'');
	$html.=$html_onclick;
	$html.=$html_onmouseout;
	$html.=$html_onmouseover;
	$html.='>'.$param['label'].'</span>';
	return str_replace(';;',';',$html);
}

function ubtn_ok($param){
	$param['type']='ok';
	return ubtn($param);
}

function ubtn_nok($param){
	$param['type']='nok';
	return ubtn($param);
}

function ubtn($param=array()){
	$html='';
	
	if(!isset($param['label'])) $param['label']='';
	
	if(isset($param['pic'])) $html_pic='&amp;pic='.$param['pic'];
	elseif(isset($param['mypic'])) $html_pic='&amp;mypic='.$param['mypic'];
	else $html_pic='';
			
	$html_src='ubtn.php?label='.urlencode($param['label']);	
	if(isset($param['type'])){
		$html_src.='&t='.$param['type'];
	}
	if (isset($param['size'])){
		$html_src.='&size='.$param['size'];
	}
	$html_src.=$html_pic;
	
	#HIGHLIGHT>
		$h=(isset($param['danger']) ? 'h2':'h');
	#<HIGHLIGHT
	#ONCLICK>
		$html_onclick='';
		#CUSTOM>
			if(isset($param['submit'])){
				if(isset($param['value'])){
					$html_onclick.="ubtn_name(this,'".$param['value']."');";
				}else{
					$html_onclick.="ubtn_name(this);";
				}
				
				if(!isset($param['name'])) $param['name']=$param['submit'];
			}
		#<CUSTOM
		if(isset($param['onclick'])){
			$html_onclick.=trim($param['onclick'].';');		
		}
		if(isset($param['url'])){
			$html_onclick.="window.open('{$param['url']}');";
		}
		if(isset($param['a'])){
			$html_onclick.="document.location.href =('{$param['a']}');";
		}
		if(!empty($html_onclick)) $html_onclick=' onclick="'.$html_onclick.'"';		
	#<ONCLICK
	#ONMOUSEOVER>
		if(isset($param['noonmouseover'])){
			$html_onmouseover='';
		} else {
			$html_onmouseover='';
			if(isset($param['onmouseover'])){
				$html_onmouseover.=trim($param['onmouseover'].';');		
			}
			//$html_onmouseover.="this.src='{$html_src}&amp;$h';";
			$html_onmouseover.="$(this).attr('src','{$html_src}&amp;$h');";
			$html_onmouseover=' onmouseover="'.$html_onmouseover.'"';
		}			
	#<ONMOUSEOVER
	#ONMOUSEOUT>
		if(isset($param['noonmouseout'])){
			$html_onmouseout='';
		} else {
			$html_onmouseout='';
			if(isset($param['onmouseout'])){
				$html_onmouseout.=trim($param['onmouseout'].';');		
			}
			$html_onmouseout.="this.src='{$html_src}';";
			$html_onmouseout=' onmouseout="'.$html_onmouseout.'"';				
		}		
	#<ONMOUSEOUT
	#STYLE>
		$html_style='';
		$html_style.="border:0px;cursor:pointer;vertical-align:middle;";
		if(isset($param['style'])){
			$html_style.=trim($param['style'].';');		
		}
		$html_style=' style="'.$html_style.'"';
	#<STYLE
	#CLASS>
		$html_class='';
		//$html_class.="border:0px;cursor:pointer;vertical-align:middle;";
		if(isset($param['class'])){
			$html_class.=trim($param['class']); //.' '
		}
		if(!empty($html_class))	$html_class=' class="'.$html_class.'"';
	#<CLASS
	#ID>
		$html_id='';		
		if(isset($param['id'])){
			$html_id.=trim($param['id']);		
		}
		if(!empty($html_id)) $html_id=' id="'.$html_id.'"';
	#<ID
	if(isset($param['submit'])){
		
		$html.='<input type="image" src="'.$html_src.'"';
	}else{
		$html.='<img src="'.$html_src.'"';
	}	
	$html.=$html_style.$html_id.$html_class;
	$html.=(isset($param['alt']) ? ' alt="'.$param['alt'].'"':'');
	$html.=(isset($param['name']) ? ' name="'.$param['name'].'"':'');
	$html.=(isset($param['title']) ? ' title="'.$param['title'].'"':'');
	$html.=(isset($param['param']) ? $param['param']:'');
	$html.=(!empty($param['label']) ? ' alt="'.$param['label'].'"':'');
	$html.=$html_onclick;
	$html.=$html_onmouseout;
	$html.=$html_onmouseover;
	$html.='/>';
	//dbug(htmlentities($html));
	return str_replace(';;',';',$html);
}

function custom_ubtn($code){
	$ubtn=array();
	switch($code){
		case 'APPLY':
			$ubtn['label']='appliquer';
			$ubtn['mypic']='OK';
			$ubtn['submit']=TRUE;
			$ubtn['name']='apply';
		break;
		default:
			$ubtn['label']='appliquer';
			$ubtn['submit']=TRUE;
	}
	return ubtn($ubtn);
}

function ubtn_CCF($param=array(),$notUrlEncodeLabel = true){
	$html='';
	
	if(!isset($param['label'])) $param['label']='';
	
	if(isset($param['pic'])) $html_pic='&amp;pic='.$param['pic'];
	elseif(isset($param['mypic'])) $html_pic='&amp;mypic='.$param['mypic'];
	else $html_pic='';
	
	//$html_src='ubtn.php?label='.urlencode($param['label']).$html_pic;
	if(!$notUrlEncodeLabel) {
		$html_src='ubtn_CCF.php?label='.urlencode($param['label']).$html_pic;
	}
	else $html_src='ubtn_CCF.php?label='.($param['label']).$html_pic;
	
	
		#HIGHLIGHT>
		$h=(isset($param['danger']) ? 'h2':'h');
	#<HIGHLIGHT
	#ONCLICK>
		$html_onclick='';
		#CUSTOM>
			if(isset($param['submit'])){
				$html_onclick.="ubtn_name(this);";
				if(!isset($param['name'])) $param['name']=$param['submit'];
			}
		#<CUSTOM
		if(isset($param['onclick'])){
			$html_onclick.=trim($param['onclick'].';');		
		}
		if(isset($param['url'])){
			$html_onclick.="window.open('{$param['url']}');";
		}
		if(isset($param['a'])){
			$html_onclick.="document.location.href =('{$param['a']}');";
		}
		if(!empty($html_onclick)) $html_onclick=' onclick="'.$html_onclick.'"';		
	#<ONCLICK
	#ONMOUSEOVER>
		$html_onmouseover='';
		if(isset($param['onmouseover'])){
			$html_onmouseover.=trim($param['onmouseover'].';');		
		}
		$html_onmouseover.="this.src='{$html_src}&amp;$h';";
		$html_onmouseover=' onmouseover="'.$html_onmouseover.'"';	
	#<ONMOUSEOVER
	#ONMOUSEOUT>
		$html_onmouseout='';
		if(isset($param['onmouseout'])){
			$html_onmouseout.=trim($param['onmouseout'].';');		
		}
		$html_onmouseout.="this.src='{$html_src}';";
		$html_onmouseout=' onmouseout="'.$html_onmouseout.'"';	
	#<ONMOUSEOUT
	#STYLE>
		$html_style='';
		$html_style.="border:0px;cursor:pointer;padding:0px; margin:0px;";
		if(isset($param['style'])){
			$html_style.=trim($param['style'].';');		
		}
		$html_style=' style="'.$html_style.'"';
	#<STYLE
	#CLASS>
		$html_class='';
		//$html_class.="border:0px;cursor:pointer;vertical-align:middle;";
		if(isset($param['class'])){
			$html_class.=trim($param['class']).' ';		
		}
		if(!empty($html_class))	$html_class=' class="'.$html_class.'"';
	#<CLASS
	#ID>
		$html_id='';
		if(isset($param['id'])){
			$html_id.=trim($param['id']);
		}
		if(!empty($html_id)) $html_id = ' id="'.$html_id.'"';
	#<ID
	if(isset($param['submit'])){
		
		$html.='<input type="image" src="'.$html_src.'"';
	}else{
		$html.='<img src="'.$html_src.'"';
	}	
	$html.=$html_style.$html_class.$html_id;
	$html.=(isset($param['name']) ? ' name="'.$param['name'].'"':'');
	$html.=(isset($param['title']) ? ' title="'.$param['title'].'"':'');
	$html.=(isset($param['param']) ? $param['param']:'');
	$html.=(!empty($param['label']) ? ' alt="'.$param['label'].'"':'');
	$html.=$html_onclick;
	$html.=$html_onmouseout;
	$html.=$html_onmouseover;
	$html.='/>';
	return str_replace(';;',';',$html);
}
?>