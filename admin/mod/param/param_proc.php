<?php
if(isset($_POST) && !empty($_POST)){
	setSiteParam('nom_site',$_POST['nom_site']);
	setSiteParam('tel_contact',$_POST['tel_contact']);
	setSiteParam('adresse_1',$_POST['adresse_1']);
	setSiteParam('adresse_2',$_POST['adresse_2']);
	setSiteParam('ligne_gps',$_POST['ligne_gps']);
    setSiteParam('ligne_web_gps',$_POST['ligne_web_gps']);
	setSiteParam('credit',$_POST['credit']);
	setSiteParam('twitter',$_POST['twitter']);
    setSiteParam('instagram',$_POST['instagram']);
	setSiteParam('facebook',$_POST['facebook']);
	setSiteParam('meta_description',$_POST['meta_description']);
	setSiteParam('meta_keyword',$_POST['meta_keyword']);
    setSiteParam('cgu',$_POST['cgu']);

	jump_to_url_and_exit('index.php?to=param');
}
?>