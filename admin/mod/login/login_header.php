<?php
// Vérification des données du formulaire de connexion
$error_msg = "";
if(!empty($_POST)){
	$login = $_POST['form_login'];
	$pass = $_POST['form_pass'];

	$auth = new auth();
	if(isset($_SESSION[USERSESSION]['error_login'])) unset($_SESSION[USERSESSION]['error_login']);
	$auth_res = $auth->load_auth($login, $_POST['form_pass']);
	
	if(empty($auth_res)){
		$error_msg = 'Identifiant ou mot de passe invalide.';
		if(isset($_SESSION[USERSESSION]['error_login'])){
			$error_msg = $_SESSION[USERSESSION]['error_login'];
		}
	}else{
		$url = 'index.php';
		if (!empty($_GET)) {
			$url .= '?';
			
			$tour = 0;
			foreach ($_GET as $param => $value) {
				if ($param == "to" && $value == "user") {
					$value='';
				}
				
				if ($tour == 0) {						
					$url .= $param."=".$value;
					$tour++;
				}else {
					$url .= "&".$param."=".$value;	
				}	
			}
		}
		
		header('Location: '.$url);
		exit();
	}
}


?>