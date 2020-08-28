<?php

class page{

	private $header = '';
	private $footer = '';
	private $corps  = '';
	
	public function __construct($encapse_corps = true){
		if($encapse_corps){
			$this->build_header();
			$this->build_footer();
		}else{
			$this->header = '<body>';
			$this->footer = '</body>';
		}
	}
	
	private function build_header(){
		$this->header = '<body>';
	}
	
	private function build_footer(){
		$this->footer = '</body>';
	}
	
	public function build_content($html=''){
		$this->corps = $html;
	}
	
	public function show(){
		echo $this->header;
		echo $this->corps;
		echo $this->footer;
	}
}
?>