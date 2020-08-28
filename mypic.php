<?php
session_name('set');
session_start();
require 'param/param_dbfield.php';
require 'inc/inc_sqlconnect.php';
require 'inc/inc_sqlquery.php';
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