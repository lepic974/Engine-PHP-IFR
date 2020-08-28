<title><?php echo TITLE_SITE; ?> - Configuration Interface</title>
<script type="text/javascript" src="jquery/jquery.form.js"></script>
<script type="text/javascript" src="jquery/jquery.imageZoom.js"></script>
<script type="text/javascript">
	_post_onload.URL='index.php?to=ajax_param';
	
	$().ready(function(){
		$(".imageZoom").imageZoom();
		$("#tabs").tabs(); 
	});
</script>
<link rel="stylesheet" href="css/jquery.imageZoom.css" media="screen" type="text/css" />