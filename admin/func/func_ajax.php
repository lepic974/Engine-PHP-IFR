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

#<AJAX
?>