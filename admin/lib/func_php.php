<?php
function jump_to_url_and_exit($url='') {
	if(empty($url)) $url=$_SERVER['REQUEST_URI'];
	header("Location: ".$url);
	exit();
}

function get_fileExtension($filename, $getDot=true){
  	if($getDot){ 
		$ext = strtolower(substr($filename, strrpos($filename, '.')));
  	}else{
		 $ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
	}
  return $ext;
}
?>