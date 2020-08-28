<?php
	$auth = new auth();	
	$auth->unload_auth();
	jump_to_url_and_exit('index.php');
?>