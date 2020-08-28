<?php
#AJAX>

function build_xcbox($proc_code, $proc_id, $flag){
	return "<img id=\"$proc_code:$proc_id\" alt=\"$flag\" src=\"$flag.png\" onclick=\"Xcbox(this, '$proc_code', $proc_id)\"/>";
}

function build_ajax_div($id,$loading=TRUE){
			return '<div id="'.$id.'">'.($loading ?'<img src="pic/ajax/loading.gif" alt=""/>':'').'</div>';
		}
function build_ajax_xpop($proc_id,$id='',$html_button='<img src="pic/details_16.png" alt=""/>',$param=''){
	if(!empty($id)){
		if($proc_id>0)
			return '<a style="cursor: pointer; position:relative;" onMouseUp="ajax_xpop('.$proc_id.',\''.$id.'\',this);" '.$param.'>'.$html_button.'</a>';
		else
			return '<a style="cursor: pointer; position:relative;" onMouseUp="ajax_xpop('.$proc_id.',\''.htmlspecialchars($id).'\',this)" '.$param.'>'.$html_button.'</a>';
	}
}
function build_ajax_xpop_custom($proc_id,$id='',$option=array()){
		if(!isset($option['button'])) $option['button']='<img src="pic/details_16.png" alt=""/>';
		if(!isset($option['center'])) $option['center']=1; else $option['center']=0;
		if(!isset($option['title'])) $option['title']='';
		
		if(!empty($id)){
			if($proc_id>0){								
				return '<a style="cursor: pointer; position:relative;" onMouseUp="ajax_xpop_custom( \''.$proc_id.'\', \''.$id.'\', this, '.$option['center'].', \''.$option['title'].'\')">'
								.$option['button'].'</a>';
			}
		}
		return '';
	}
function build_xpop_msg($msg='',$img='info',$button=array()){
	echo '<table style="margin:5px;"><tr>';
	echo '<td><img src="./pic/ajax_xpop/msg_'.$img.'.png" alt="" /></td>';
	echo '<td>'.$msg.'</td>';
	echo '</tr></table>';
	echo '<div style="clear:both;"></div>';
	if(!empty($button)){
		echo '<div style="clear:both;text-align:right;white-space:nowrap;">';
		foreach($button as $html){
			echo '&nbsp;';
			switch($html){
				CASE '0':
					$ubtn=array();
					$ubtn['label']='Annuler';
					$ubtn['mypic']='CANCEL';
					$ubtn['onclick']="xpop_hide();";
					echo ubtn($ubtn);
					//echo '<input type="button" value="ANNULER" class="button" onmousedown="xpop_hide();">';
				break;
				CASE 1:
					$ubtn=array();
					$ubtn['label']='OK';
					$ubtn['mypic']='OK';
					$ubtn['onclick']="xpop_hide();";
					echo ubtn($ubtn);
					//echo '<input type="button" value="OK" class="button" onmousedown="xpop_hide();">';
				break;
				CASE 2:
					$ubtn=array();
					$ubtn['label']='OK';
					$ubtn['mypic']='OK';
					$ubtn['onclick']="window.close();";
					echo ubtn($ubtn);
				break;
				CASE 5:
					$ubtn=array();
					$ubtn['label']='OK';
					$ubtn['mypic']='OK';
					$ubtn['onclick']="window.location.reload();";
					echo ubtn($ubtn);
				break;
				default:
					echo $html;
			}
		}
		echo '</div>';
		
	}
}

function from_ajax_init(){
	$this_node=array();
	if(isset($_POST['_node_value'])){
		$this_node['value'] = $_POST['_node_value'];
	}
	if(isset($_POST['_node_id'])){
		$this_node['id'] = $_POST['_node_id'];
	}
	return $this_node;
}

	function to_ajax_dbug($dbug='', $clear=FALSE){
		if(is_array($dbug)){
			$html='';
			foreach($dbug as $key=>&$val) $html.=$key.'=>'.$val.'<br/>';
			$dbug=$html;

		}
		if(empty($dbug)) $dbug=mktime().dbug($_POST, TRUE);
		if($clear) to_ajax('dbug_clean','','');
		to_ajax('dbug','', $dbug);
	}
	/*
	function to_ajax_dbug($html){
			echo 'to_ajax_dbug¤><div style="width:80%;padding:2px;background:#eee;color:#000;border:1px double #000;">'.$html.'</div>¤.';	
	}
*/
	
	/*
	 * @desc build action to be parsed and executed in jquery.ajax.js callback
	 * @param string $action append|prepend|remove|set|focus
	 * @param string $id DOM element id
	 * @param string $data optional data to be used by callback action
	 * @comment will work only with jquery version of ajax.js
	 * @see jquery.ajax.js Xfill() for action documentation
	 */
	function to_ajax($action, $id='void', $data=''){
		$action=strtolower($action);
		switch($action){
			case 'alert':
			case 'dbug':
			case 'dbug_clean':
			case 'append':
			case 'after':
			case 'prepend':
			case 'set':
			case 'focus':
			case 'select':
			case 'class':
			case 'altsrc':
			case 'hide':
			case 'show':
			case 'wait':
			case 'endwait':
			case 'remove':
			case 'modal':
			case 'html':
			case 'location':
				echo $id.'<xfill type="'.$action.'">'.$data.'</xfill>';
			break;
			
		}
	}
	
	/*
	 *  show modal
	 * @param string $url
	 */
	function to_ajax_modal_html($html){
		$js="$('<div>".addslashes($html)."</div>').modal();";
		to_ajax_eval($js);
	}
	
	function to_ajax_modal_close(){
		to_ajax_eval('$.modal.close();');
	}
	
	
	/*
	 *  eval javascript
	 * @param string $js
	 */
	function to_ajax_eval($js){
		echo 'void<xfill type="eval">'.$js.'</xfill>';
	}
	
	
	/*
	 * @deprecated, use to_ajax('set') instead
	 */
	function to_ajax_inner($values,$echo=TRUE){
		if($echo)
			foreach($values as $key => $val)
				echo $key.'<xfill type="inner">'.$val.'</xfill>';	
		else{
			$return='';
			foreach($values as $key => $val)
				$return.=$key.'<xfill type="inner">'.$val.'</xfill>';	
			return $return;
		}
	}
	/*
	 * @deprecated, use to_ajax('set') instead
	 */
	function to_ajax_value($values){
		foreach($values as $key => $val)
			echo $key.'<xfill type="value">'.$val.'</xfill>';	
	}
	/*
	 * @deprecated, use to_ajax('set') instead
	 */
	function to_ajax_set($values){
		foreach($values as $key => $val)
			echo $key.'<xfill type="set">'.$val.'</xfill>';	
	}
	/*
	 * @deprecated, use to_ajax('class') instead
	 */
	function to_ajax_class($values){
		foreach($values as $key => $val)
			echo $key.'<xfill type="class">'.$val.'</xfill>';	
	}
	/*
	 * @deprecated, use to_ajax('focus') instead
	 */
	function to_ajax_focus($key){
			echo $key.'<xfill type="focus"></xfill>';	
	}
	/*
	 * @deprecated, use to_ajax('select') instead
	 */
	function to_ajax_select($key){
			echo $key.'<xfill type="select"></xfill>';	
	}
	/*
	 * @deprecated, use to_ajax('altsrc') instead
	 */
	function to_ajax_altsrc($values){
		foreach($values as $key => $val)
			echo $key.'<xfill type="altsrc">'.$val.'</xfill>';	
	}
	
	function to_ajax_var($name, $value){
			echo $name.'<xfill type="var">'.$value.'</xfill>';	
	}
	
	function to_ajax_location($url=''){
			echo 'void<xfill type="location">'.$url.'</xfill>';	
	}
	
	function to_ajax_close(){
		echo 'void<xfill type="close"></xfill>';	
	}
	
	function to_ajax_popup($url=''){
			echo 'void<xfill type="popup">'.$url.'</xfill>';	
	}
	
	function build_xform(&$html,$form_name='xform',$table_wrap=TRUE){
		
		return  '<form id="'.$form_name.'" action="" onsubmit="return false;">'.
				($table_wrap ? '<table border="0">'.$html.'</table>':$html).'</form>';
	}
		
	/*
	 #XFORM>
		#INIT>
			$xform_id='1';
			$xform='';$form=array();$db=array();
		#<INIT
		#FIELDSET>	
			
		#<FIELDSET
		#APPLY>
			$form=array('type'=>'apply');			
			$xform.=build_xform_row($form);
		#<APPLY		
		#WRAP>
			$xform=build_xform($xform,'xform');
			${'html_xform_'.$xform_id}=build_fieldset($xform,'xform',FALSE);
			unset($xform,$form,$db);
		#<WRAP
	#<XFORM
	 */
	
	function build_xform_row_multifield($form,$dbs=array()){
		$form['raw_field']=TRUE;
		$multifield='';
		$form_param='';
		if(isset($form['param']) AND is_array($form['param'])) $form_param=$form['param'];
		$cpt = count($dbs);
		$offset = 0;
		foreach($dbs as $key => $db){
			if(!empty($form_param)) $form['param']=$form_param[$key];
			$multifield.=build_xform_row($form,$db);
			$offset++;
			if($cpt!=$offset) $multifield.='&nbsp;';
		}
		if(!isset($form['label'])) $form['label']='&nbsp;';
		return '<tr><td style="text-align:right;white-space: nowrap;">'.label($form['label']).'</td><td>'.$multifield.'</td></tr>';
	}
	/**
	* Build a xform row
	*
	* @author CC
	* @version 1.1
	* @param array $form
	* @param array $db
	* @return html
	*
	*/
	function build_xform_row($form,$db=array()){
		#HELP>
			//$form['type']: input/textarea/checkbox/select/apply/cancel/radio
			//$db['table']: db table name
			//$db['field']: db field name
			//$db['id']: db table row id , 0=INSERT else UPDATE
			//$form['label']: field label using label()
			//$form['value']: use as field value instead of loading from db
		#<HELP
		
		#REQUIRED>
			if(!isset($form['type'])) return '';
		#<REQUIRED
		#DEFAULT_VALUES>
			if(!isset($form['param'])) $form['param']='';
			$form['param']=' '.trim($form['param']);
			if(!isset($form['label'])) $form['label']='&nbsp;';
		#<DEFAULT_VALUES
		
		#PICTURE>
			//PREPEND_PICTURE_TO_LABEL
			if(isset($form['picture'])) $form['label']='<img style="float:left;" src="'.$form['picture'].'" alt=""/>&nbsp;'.$form['label'];
			$html='<tr><td style="text-align:right;white-space: nowrap;">'.label($form['label']).'</td><td>';
		#<PICTURE

		$is_readonly=isset($form['readonly']);
		$disabled=' disabled="disabled" style="border:1px solid white;background:white;color:black;"';
		$onchange=' onchange="Xform_onchange(this)"';
		$void=' name="void"';
		$if_readonly='';
		if($form['type']=='apply' OR $form['type']=='apply_refresh' OR $form['type']=='empty'){
			
		}
		else{
			if(!isset($form['value'])) $form['value']=squery("SELECT ".$db['field']." FROM ".$db['table']." WHERE id=".$db['id']." LIMIT 1;");
			if($is_readonly){
				$form['name']='xvoid:'.$db['table'].':'.$db['field'].':'.(int)$db['id'];
				$if_readonly='disabled="disabled" style="border:1px solid white;background:white;color:black;"';
			}else{
				$form['name']='xform';
				if($form['type']=='date'){
					$form['name'].='_date';
					$id_btn_date = 'btn_date_'.$db['field'].'_'.(int)$db['id'];
				}
				$form['name'].=':'.$db['table'].':'.$db['field'].':'.(int)$db['id'];
			}
		}
		
		switch($form['type']){
			
			case 'input':
				if(isset($form['width_pixel'])) $form['param'].=' style="width: '.(int)$form['width_pixel'].'px"';
				if(isset($form['width'])) $form['param'].=' size="'.(int)$form['width'].'"';
				if($is_readonly){
					$raw_field='<input'.$void.' value="'.$form['value'].'"'.$form['param'].$disabled.'/>';
				}else{
					$raw_field='<input name="'.$form['name'].'" value="'.$form['value'].'"'.$form['param'].$onchange.'/>';
				}
				
			break;
			
			case 'radio':
				if($is_readonly){
					$raw_field='<input'.$void.' type="checkbox" '.($form['value'] ? 'checked="checked"':'').$form['param'].$disabled.'/>';
				}else{
					$raw_field='';
					$raw_field.='<input name="'.$form['name'].'" type="radio" value="1" '.($form['value']==1 ? 'checked="checked"':'');
					$raw_field.=$form['param'].$onchange.'/>&nbsp;Oui';	
					$raw_field.='&nbsp;&nbsp;&nbsp;';	
					$raw_field.='<input name="'.$form['name'].'" type="radio" value="0" '.($form['value']==0 ? 'checked="checked"':'');
					$raw_field.=$form['param'].$onchange.'/>&nbsp;Non';	
						
					
				}	
			break;
			
			case 'checkbox':
				if($is_readonly){
					$raw_field='<input'.$void.' type="checkbox" '.($form['value'] ? 'checked="checked"':'').$form['param'].$disabled.'/>';
				}else{
					$raw_field='<input value="'.$form['name'].'" type="checkbox" '.($form['value'] ? 'checked="checked"':'').$form['param'].$onchange.'/>';		
				}	
			break;
			case 'textarea':
				if($is_readonly){
					$raw_field='<textarea '.$void.$form['param'].$disabled.'/>'.$form['value'].'</textarea>';
				}else{
					$raw_field='<textarea name="'.$form['name'].'" '.$form['param'].$onchange.'/>'.$form['value'].'</textarea>';
				}				
			break;
			case 'select':
				if(isset($form['width'])) $form['param'].=' cols="'.(int)$form['width'].'"';
				if(isset($form['height'])) $form['param'].=' rows="'.(int)$form['height'].'"';
				if(isset($form['width_pixel'])) $form['param'].=' style="width: '.(int)$form['width_pixel'].'px"';
				if($is_readonly){
					$raw_field='<select'.$void.$form['param'].$disabled.'>';
				}else{
					$raw_field='<select name="'.$form['name'].'" '.$form['param'].$onchange.'>';
				}
				
				if(isset($form['option_blank']))
					$raw_field.='<option value="">&nbsp;</option>';
				if(!empty($form['option'])){
					foreach($form['option'] as $value => $label)
						if($value==$form['value'])
							$raw_field.='<option value="'.$value.'" selected="selected">'.$label.'</option>';
						else
							$raw_field.='<option value="'.$value.'">'.$label.'</option>';
				}
				$raw_field.='</select>';
			break;
			
			
			#SPECIAL>
				//Save then reset apply highlight
				case 'apply':
					if(isset($form['dbug'])) $form['func']='Xdbug';
					if(!isset($form['func'])) $form['func']='';
					
				
					$ubtn=array();			
					$ubtn['onclick']="Xform(this,'{$form['func']}', false);Xform_onvalid(this);";
					$ubtn['onmouseover']="this.focus();";
					$ubtn['label']='appliquer';
					$ubtn['mypic']='OK';
					$ubtn['name']='xform_apply';
					$ubtn['value']='Appliquer';
					$raw_field=ubtn($ubtn);
					//$raw_field='<input name="xform_apply" value="Appliquer" type="button" class="button" onmouseover= onclick= '.$form['param'].'/>';
					
					
				break;
				//Save then reset apply highlight, call func BUT WAIT AJAX RETURN
				case 'apply_sync':
					if(isset($form['dbug'])) $form['func']='Xdbug';
					if(!isset($form['func'])) $form['func']='';
					$raw_field='<input name="xform_apply" value="Appliquer" type="button" class="button" onmouseover="this.focus();" onclick="Xform(this,\''.$form['func'].'\',false);Xform_onvalid(this);" '.$form['param'].'/>';
				break;
				//Save then reload page
				case 'apply_refresh':
					if(!isset($form['func'])) $form['func']='';
					$raw_field='<input name="xform_apply" value="Appliquer" type="button" class="button" onmouseover="this.focus();" onclick="Xform(this,\''.$form['func'].'\',false);Xform_onvalid_refresh();" '.$form['param'].'/>';
				break;
				case 'date':
					if($form['value']) $form['value']=date("d/m/Y",$form['value']);
					else $form['value']='';
					$raw_field='<nobr>';
					if($is_readonly){
						$raw_field.='<input'.$void.' value="'.$form['value'].'" '.$form['param'].' '.$disabled.'/>';
					}else{
						$raw_field.='<input id="'.$form['name'].'" name="'.$form['name'].'" value="'.$form['value'].'" '.$form['param'].' '.$onchange.'/>';
					}
					
					if(!isset($form['no_date_js']) AND !$is_readonly){
						global $xcal_id;
						if(!isset($xcal_id)) $xcal_id=0;
						$xcal_id++;
						
						$inputCalendar = '<img src="'.PICTURE_FOLDER.'/ico_calendar.png" id="'.$id_btn_date.'" align="absmiddle"	style="cursor: pointer; border: 0px solid black;" title="Selectionner la date"/>';
						$inputCalendar.= '<script type="text/javascript">';
						$inputCalendar.= '	Calendar.setup({';
						$inputCalendar.= '		inputField 		: "'.$form['name'].'",';
						$inputCalendar.= '		ifFormat 		: "%d/%m/%Y",';
						$inputCalendar.= '		button 			: "'.$id_btn_date.'",';
						$inputCalendar.= '		align 			: "Tl",';
						$inputCalendar.= '		singleClick 	: true';
						$inputCalendar.= '	});';
						$inputCalendar.= '</script>';
						$raw_field.= $inputCalendar;
						//$raw_field.='<a href="javascript:var xcal_'.$xcal_id.' = new calendar1(document.getElementById(\''.$form['name'].'\'));xcal_'.$xcal_id.'.popup();"><img src="pic/js_calendar/date.gif" border="0" alt="Cliquez ici pourchoisir une date."></a>';
						if (!isset($form['no_erase_date'])) {	
							$raw_field.='<img src="pic/js_calendar/reset.gif"  border="0" style="cursor:pointer;" alt="Effacer la date." onclick="document.getElementById(\''.$form['name'].'\').value=\'\';">';
						}
						//xcal_'.$xcal_id.'.year_scroll = true;xcal_'.$xcal_id.'.time_comp = false;
					}
					$raw_field.='</nobr>';
				break;
				case 'empty':
					$raw_field='&nbsp;';
				break;
			#<SPECIAL
			
			default:
			return '';
		}		
		if(isset($form['raw_field'])) return $raw_field;
		$html.=$raw_field;
		if(isset($form['queue_option'])) $html.=$form['queue_option'];
		$html.='</td></tr>';
		return $html;
	}
	function build_xform_tr($value, $label=FALSE){
		if(!$label){
			return '<tr><td colspan="2">'.$value.'</td></tr>';
		}else{
			return '<tr><td style="text-align:right;">'.label($label).'</td><td>'.$value.'</td></tr>';
		}
		
	}
#<AJAX
?>