<?php
$ajax_action_post = '';
$ajax_action_get = '';
if(isset($_POST['_ajax_action'])) $ajax_action_post = $_POST['_ajax_action'];
if(isset($_GET['_ajax_action'])) $ajax_action_get = $_GET['_ajax_action'];

$ajax_action = $ajax_action_get;
if($ajax_action_post != "") $ajax_action = $ajax_action_post;

if($ajax_action){
	switch($ajax_action){
		case 'supp_user':
			$idUser = $_POST['idUser'];
			squery("DELETE FROM ".T_USER." WHERE {$g_user['id']}=".$idUser);
			to_ajax_location('index.php?to=listing_user');
		break;
		case 'change_etat_user':
			$idUser = $_POST['idUser'];
			$etat = squery("SELECT {$g_user['desactiveON']} FROM ".T_USER." WHERE {$g_user['id']}=".$idUser);
			if($etat == 1){
				$sql = "UPDATE ".T_USER." SET {$g_user['desactiveON']}=0 WHERE {$g_user['id']}=".$idUser;
				squery($sql);
				$html = '<a href="#" onclick="change_etat_user('.$idUser.'); return false;"><img src="pic/ok.png" style="border:none;" /></a>';
			}else{
				$sql = "UPDATE ".T_USER." SET {$g_user['desactiveON']}=1 WHERE {$g_user['id']}=".$idUser;
				squery($sql);
				$html = '<a href="#" onclick="change_etat_user('.$idUser.'); return false;"><img src="pic/cancel.png" style="border:none;" /></a>';				
			}
			to_ajax('set','zone_'.$idUser,$html);
		break;
		case 'new_mdp':
			$idUser = $_POST['idUser'];
			$new_mdp = generatePassword(8);
			$sql = "UPDATE ".T_USER." SET {$g_user['password']}='".md5($new_mdp)."' WHERE {$g_user['id']}=".$idUser;
			squery($sql);
			
			$name = get_userName($idUser);
			$email = get_userEmail($idUser);
			//$email = 'christophe.thibault@gmail.com';

			// Envoi du Mail
			require '../class/phpmailer/class.phpmailer.php';
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->Host = SMTP_HOST;
			$mail->Port = SMTP_PORT;
			$mail->SMTPAuth = true;
			$mail->Username = SMTP_USER;
			$mail->Password = SMTP_PASSWORD;
			$mail->IsHTML(true); 
			
		    $mail->From = "webmaster@urban-legion.com";
			$mail->FromName = 'Webmaster Urban Legion';
			$mail->AddAddress($email,$email);
			
			$mail->Subject = "Nouveau Mot de passe - Urban Legion";
			
			$body = '<div style="width:80%; margin:auto;background-color:#c7ae6b;text-align:center;height:50px;padding:10px;padding-top:20px;"><img src="http://www.urban-legion.com/newsletter/logo_mail.jpg" alt="Urban Legion" /></div>';
			$body.= '<div style="width:80%; margin:auto;background-color:#ddcea5;padding:10px;"><br/>';
			$body.= '	<b>Bonjour</b> '.$name.'<br/><br/>';
			$body.= '	Un nouveau mot de passe vous a été attribué sur le site d\'<b>URBAN LEGION</b> le '.date('d/m/Y \à H:i',time()).'<br/>';
			$body.= '	Voici un récapitulatif des changements : <br/>';
			$body.= '	<ul>';
			$body.= '	<li><b>Login : </b>'.$email.'</li>';
			$body.= '	<li><b>Mot de passe : </b>'.htmlentities($new_mdp).'</li>';
			$body.= '	</ul>';
			$body.= '	Vous pouvez changer ce mot de passe en vous rendant dans la gestion de votre compte sur le site d\'Urban Legion.<br/>';
			$body.= '	Conservez bien ce mail, nous ne pourrons pas vous refournir votre mot de passe...<br/><br/>';
			$body.= '	A très bientot sur http://www.urban-legion.com !<br/>';
			$body.= '	L\'équipe d\'Urban Legion.<br/><br/><br/>';
			$body.= '</div>';
			
			$mail->Body    = $body;
			$mail->AltBody = $body;	
					
			if($mail->Send()){
				$img = mypic('OK');
				to_ajax('set','new_mdp_'.$idUser,$img);
			}else{
				$img = mypic('NO');
				to_ajax('set','new_mdp_'.$idUser,$img);
			}
			
		break;
	}
}

?>