<?php

function  user_isAdministrateur(){
	if(isset($_SESSION['set']['type_access'])){
		if($_SESSION['set']['type_access']==3)
			return true;
		else
			return false;
	}else{
		return false;
	}
}

function  user_isConseiller(){
	if(isset($_SESSION['set']['type_access'])){
		if($_SESSION['set']['type_access']==2)
			return true;
		else
			return false;
	}	
}

?>