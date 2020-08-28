<?php
/**
* @package HTML_Rendering
* @author PAR <pierre-andre.rohard@nexto.fr>
* @desc HTML Header Handling using and adding css and scripts files
* @version 1.0, 10/2008
*/	
class HTMLHeader {
	
	public $_scripts 	= 	array();
	public $_jquery 	= 	array();
	public $_jquerycss	=	array();
	public $_css		=	array();
	public $_title		= null;
	
	public function __construct() {
		$this->_title = 'contact_CCF';
	}
	
	public function registerClientScript($file,$group='common') {
		$this->_scripts[$group][hash('md5',$file)] = $file;
	}
	public function registerJQueryScript($file,$group='jquery') {
		$this->_jquery[$group][hash('md5',$file)] = $file;
	}
	public function registerJQueryStylesheet($file,$group="common") {
		$this->_jquerycss[$group][hash('md5',$file)] = $file;
	}	
	public function registerStylesheet($file,$group="common") {
		$this->_css[$group][hash('md5',$file)] = $file;
	}
	
	public function getClientScripts() {
		return $this->_scripts; 
	}
	
	public function getClientStylesheets() {
		return $this->_css; 
	}
	
	
	public function registerJQuery() {
		$this->registerJQueryScript('jquery-1.8.2.js','jquery');
		$this->registerJQueryScript('jquery.ajax.js','jquery');
		$this->registerJQueryScript('jquery.modal.js','jquery');
		$this->registerJQueryScript('jquery.animated.innerfade.js','jquery');
		$this->registerJQueryScript('jquery.hoverIntent.js','jquery');
		$this->registerJQueryScript('jquery.livequery.js','jquery');
		$this->registerJQueryScript('jquery-ui.min.js','jquery');
		$this->registerJQueryScript('jquery.vegas.js','jquery');
		$this->registerJQueryScript('jquery.calendar.js','jquery');
		$this->registerJQueryScript('jquery.manage_background.js','jquery');
		
	}
	
	public function registerCommonScripts() {
		
		$this->registerJQuery();
		$this->registerClientScript('common.js','common');
		$this->registerClientScript('autoload.js','common');
		$this->registerClientScript('calendar.js','common');
	}
	
	public function registerCommonStyleSheets() {
		$this->registerStylesheet('common.css');
		$this->registerStyleSheet('calendar.css');
		$this->registerStyleSheet('jquery-ui.min.css');
		$this->registerStyleSheet('jquery.vegas.css');
	}
	
	public function render() {

		$buff = '';

		if(count($this->_jquery)) {
			foreach($this->_jquery as $group=>$scripts) {
				$buff .="\n".'<script type="text/javascript" src="jquery/proxyscripts.php?name='.$group.'&amp;c=js&amp;f='.implode(',',$scripts).'"></script>';
			}
		}
		if(count($this->_scripts)) {
			foreach($this->_scripts as $group=>$scripts) {
				$buff .="\n".'<script type="text/javascript" src="js/proxyscripts.php?name='.$group.'&amp;c=js&amp;f='.implode(',',$scripts).'"></script>';
			}
		}
		if(count($this->_jquerycss)) {
			foreach($this->_jquerycss as $group=>$css) {
				$buff .="\n".'<link rel="stylesheet" type="text/css" href="proxyscripts.php?name='.$group.'&amp;c=text/css&amp;f='.implode(',',$css).'"/>';
			}	
		}
		if(count($this->_css)) {
			foreach($this->_css as $group=>$css) {
				$buff .="\n".'<link rel="stylesheet" type="text/css" href="css/proxycss.php?name='.$group.'&amp;c=text/css&amp;f='.implode(',',$css).'"/>';
			}	
		}
		$buff .="\n";
		echo $buff;
		
		// set à 0 de tous.
		$this->reset();
	}
	public function reset() {
		$this->_css = array();
		$this->_jquery = array();
		$this->_scripts = array();
		$this->_jquerycss = array();
	}
	
	public function setTitle($title) {
		$this->_title = $title;
	}
	public function getTitle() {
		return $this->_title;
	}
}
?>