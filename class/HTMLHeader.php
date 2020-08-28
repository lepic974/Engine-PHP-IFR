<?php
/**
* @package HTML_Rendering
* @desc HTML Header Handling using and adding css and scripts files
* @version 1.0, 10/2008
*/	
class HTMLHeader {
	
	public $_scripts 	 = 	array();
	public $_jquery 	 = 	array();
	public $_bootstrap 	 = 	array();
	public $_jquerycss	 =	array();
	public $_css		 =	array();
	public $_specifics   =  array();
	public $_cssBootstrap = array();
	public $_title		 =  null;
	
	public function __construct() {
		$this->_title = 'HTML_Header_Rendering';
	}
	
	public function registerClientScript($file,$group='common') {
		$this->_scripts[$group][hash('md5',$file)] = $file;
	}
	public function registerJQueryScript($file,$group='jquery') {
		$this->_jquery[$group][hash('md5',$file)] = $file;
	}
	public function registerBootstrapScript($file,$group='bootstrap') {
		$this->_bootstrap[$group][hash('md5',$file)] = $file;
	}
	public function registerJQueryStylesheet($file,$group="common") {
		$this->_jquerycss[$group][hash('md5',$file)] = $file;
	}	
	public function registerStylesheet($file,$group="common") {
		$this->_css[$group][hash('md5',$file)] = $file;
	}
	public function registerSpecificStylesheet($file,$group="common") {
		$this->_specifics[$group][hash('md5',$file)] = $file;
	}
	public function registerBootstrapStylesheet($file,$group="common") {
		$this->_cssBootstrap[$group][hash('md5',$file)] = $file;
	}
		
	public function getClientScripts() {
		return $this->_scripts; 
	}
	
	public function getClientStylesheets() {
		return $this->_css; 
	}
	
	public function registerBootstrap() {
		$this->registerBootstrapScript('bootstrap.min.js','bootstrap');
	}
	
	public function registerJQuery() {
		$this->registerJQueryScript('jquery.min.js','jquery');
		$this->registerJQueryScript('jquery.ajax.js','jquery');
		$this->registerJQueryScript('moderniz.min.js','jquery');
		$this->registerJQueryScript('wow.min.js','jquery');
        //$this->registerJQueryScript('scrolloverflow.min.js','jquery');
        $this->registerJQueryScript('jquery.fullPage.js','jquery');
        $this->registerJQueryScript('jquery.mousewheel.js');
        $this->registerJQueryScript('jquery.jscrollpane.min.js');
	}
	
	
	public function registerCommonScripts() {
		$this->registerJQuery();
		$this->registerBootstrap();
        $this->registerClientScript('specific_js.js','common');
		$this->registerClientScript('common.js','common');
		$this->registerClientScript('autoload.js','common');
	}

    public function registerCommonScriptsMobile() {
        $this->registerJQueryScript('jquery.min.js','jquery');
        $this->registerJQueryScript('jquery.ajax.js','jquery');
        $this->registerJQueryScript('jquery.jscrollpane.min.js');
        //$this->registerJQueryScript('moderniz.min.js','jquery');
        //$this->registerJQueryScript('wow.min.js','jquery');
        //$this->registerJQueryScript('scrolloverflow.min.js','jquery');
        //$this->registerJQueryScript('jquery.fullPage.js','jquery');
        //$this->registerJQueryScript('jquery.mousewheel.js');


        $this->registerBootstrap();
        $this->registerClientScript('specific_js.js','common');
        $this->registerClientScript('common.js','common');
        $this->registerClientScript('autoload.js','common');
    }
	
	public function registerCommonStyleSheets() {
		$this->registerStylesheet('common.css');
        $this->registerStylesheet('jquery.fullPage.css');
        $this->registerStylesheet('jquery.jscrollpane.css');


		$this->registerBootstrapStyleSheets();
	}

    public function registerCommonStyleSheetsMobile() {
        $this->registerStylesheet('common.css');
        $this->registerStylesheet('jquery.jscrollpane.css');


        $this->registerBootstrapStyleSheets();
    }
	
	public function registerSpecificStyleSheets(){
		$this->registerSpecificStylesheet('interface.css');
		$this->registerSpecificStylesheet('interface_phone.css');
		$this->registerSpecificStylesheet('interface_phablet.css');
        $this->registerSpecificStylesheet('interface_phablet_portrait.css');
		$this->registerSpecificStylesheet('interface_sd.css');
        $this->registerSpecificStylesheet('interface_hd.css');
        $this->registerSpecificStylesheet('interface_phone_retina.css');
        $this->registerSpecificStylesheet('interface_phablet_retina.css');
        $this->registerSpecificStylesheet('interface_phablet_retina_portrait.css');
        $this->registerSpecificStylesheet('interface_sd_retina.css');
        $this->registerSpecificStylesheet('interface_sd_retina_portrait.css');
        $this->registerSpecificStylesheet('interface_hd_retina.css');
        $this->registerSpecificStylesheet('interface_hd_retina_portrait.css');
	}

    public function registerSpecificStyleSheetsMobile(){
        $this->registerSpecificStylesheet('interface.css');
        $this->registerSpecificStylesheet('interface_mobile.css');
    }

	public function registerBootstrapStyleSheets(){
		$this->registerBootstrapStylesheet('bootstrap.min.css');
	}
	
	public function render() {

		$buff = '';

		if(count($this->_jquery)) {
			foreach($this->_jquery as $group=>$scripts) {
				$buff .="\n".'<script type="text/javascript" src="js/proxyscripts.php?name='.$group.'&amp;c=js&amp;f='.implode(',',$scripts).'"></script>';
			}
		}
		
		if(count($this->_bootstrap)) {
			foreach($this->_bootstrap as $group=>$scripts) {
 				$buff .="\n".'<script type="text/javascript" src="bootstrap/js/proxyscripts.php?name='.$group.'&amp;c=js&amp;f='.implode(',',$scripts).'"></script>';
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
		
		if(count($this->_cssBootstrap)) {
			foreach($this->_cssBootstrap as $group=>$css) {
				$buff .="\n".'<link rel="stylesheet" type="text/css" href="bootstrap/css/proxycss.php?name='.$group.'&amp;c=text/css&amp;f='.implode(',',$css).'"/>';
			}
		}
		
		if(count($this->_specifics)) {
			foreach($this->_specifics as $group=>$css) {
				$buff .="\n".'<link rel="stylesheet" type="text/css" href="_specific/'.file_get_contents('param.ini').'/css/proxycss.php?name='.$group.'&amp;c=text/css&amp;f='.implode(',',$css).'"/>';
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