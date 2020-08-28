<?php
session_name('site');
session_start();
require 'inc/inc_common.php';
if ( isset($_GET['id']) ){
	$id = $_GET['id'];
	if(!is_numeric($id)){
		$id=squery("SELECT id FROM ".T_IMG." WHERE code = '$id' LIMIT 1;");
	}
	if($id>0){
		$req = "SELECT code, mimetype, img_blob ".
		"FROM ".T_IMG."  WHERE id = ".$id;
		$ret = query($req);
		$col = mysqli_fetch_row($ret);
		if ( $col[0] ){
			header ("Content-type: ".$col[1]);
			echo $col[2];
			exit();
		}
	}
}
?>