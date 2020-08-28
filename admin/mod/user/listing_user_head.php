<title> <?php echo TITLE_SITE ?> - Utilisateurs</title>
<script type="text/javascript" src="jquery/xgrid/jquery.xgrid.js"></script>
<script type="text/javascript" src="jquery/jquery.form.js"></script>
<script type="text/javascript">
_post_onload.URL='index.php?to=ajax_user';
var _local_ajax_php='index.php?to=ajax_user';
	
	function change_etat_user(id){
		_post.idUser = id;
		_ajax_post('change_etat_user');
	}
	
	function supp_user(id){
		if(window.confirm('Etes vous sur ?')){
			_post.idUser = id;
			_ajax_post('supp_user');
		}	
	}
	
	function new_mdp(id){
		if(window.confirm('Etes vous sur ?')){
			_post.idUser = id;
			_ajax_post_sync('new_mdp');
			alert('Utilisateur averti par mail...');
		}	
	}
	
	$().ready(function(){
		$('.xgrid').xGrid();
	});
</script>
<link rel="stylesheet" href="jquery/xgrid/jquery.xgrid.css" type="text/css" />