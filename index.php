<?php
/*
 * @desc 		Engine Web v3.0 - UltraBold
 * @date		01/01/2016
 * @author		Christophe Thibault <christophe.thibault@gmail.com>
 */

ini_set('session.gc_maxlifetime', 7200);

// Utilise l'encodage interne UTF-8 
ini_set('default_charset', 'utf-8');
mb_internal_encoding("UTF-8");
@error_reporting(E_ALL ^ E_DEPRECATED);

//ebug : error, mdbug : php mem, tdbug : php exec time, qdbug : query
if(isset($_GET['e_all'])) ini_set( 'display_errors' , TRUE  );

// Inclusion pour préparation du moteur 
require 'inc/inc_common.php';

// Chargement de la session et verification pour authentification
session_name(SITE_NAME);
session_start();
if(isset($_REQUEST['lang'])) {
	if($_REQUEST['lang'] == 1 || $_REQUEST['lang'] == 2){
		$_SESSION[SITE_NAME]['id_lang'] = $_REQUEST['lang'];
		if($_REQUEST['lang'] == 1) {
            $_SESSION[SITE_NAME]['lang'] = 'fr';
        }else {
            $_SESSION[SITE_NAME]['lang'] = 'uk';
        }
	}else{
		$_SESSION[SITE_NAME]['id_lang'] = 1;
		$_SESSION[SITE_NAME]['lang'] = 'fr';
	}
}

if(!isset($_SESSION[SITE_NAME]['id_lang'])){
	$_SESSION[SITE_NAME]['id_lang'] = DEFAULT_LANGUAGE_ID;
	$_SESSION[SITE_NAME]['lang'] = 'fr';
}

require_once('inc/lang/'.$_SESSION[SITE_NAME]['id_lang'].'.php');
/*
 * index.php?to=page
 * -pick $url_php from $page[$_GET['to']] and try to include optionals associated header and head
 * files (suffix _header.php and _head.php )
 * -any link within the included page must refer to engine.php?to=page
 * -to add a page, edit param/param_engine.php
 *
 * complete structure loading for a page.php:
 * 0>
 * 		index.php: session managment and page loading.
 * 		All pages shall be displayed trought this engine.php
 * 1>
 * 		page_header.php (optional) some big code may be sub-decomposed in:
 * 		page_proc.php: html form and database processing.
 * 		page_build.php: build whole html sections to be outputed later within <body> section.
 * 		NO HTML OUTPUT shall occur in header section so that header() redirections works.
 * 1bis>
 * 		page_meta.php (optional):
 * 		define('DESCRIPTION_META','contenu pour la description');
 * 		define('KEYWORD_META','conetnu pour les mots clefs');
 * 2>
 * 		<html> output starts here if $html_wrap
 * 		<head>+engine.php default css & js inclusions
 * 3>
 * 		page_head.php (optional) may choose an alternate title, and additional .js & .css inclusions
 * 		\!/ excludes <head> tags
 * 4>
 * 		</head>
 * 		page.php (mandatory)
 * 		\!/ includes '<body></body>' tags so that we can define specific <body> attributes.
 * 		all page content shall appear here, mainly from $page_X contents generated from page_header.php+page_build.php
 * 		in best cases, no processing shall occur here but echoing $page_X variables...
 * 		</html>
 * thus, complete page structure may be composed of:
 * mod/page_header.php includes(param/param_page.php, mod/page_proc.php, mod/page_build.php)
 * page_head.php includes(css/page_build.css, js/page.js), page.php
 * 
 * 
 * simple structure loading for a page.php is the same but all is processed and displayed in
 * a single page.php loaded after default engine.php head (<head>title+css+js</head>)
 * \!/ page.php still includes '<body></body>' tags.
 */
// 0>

if( isset($_POST['ajax_wrap']) OR  isset($_POST['print_wrap']) OR  isset($_POST['csv_wrap']) 
	OR isset($_GET['print_wrap']) OR  isset($_GET['csv_wrap'])){
	$is_ajax_wrap=isset($_POST['ajax_wrap']);
	$is_print_wrap=( isset($_POST['print_wrap']) OR isset($_GET['print_wrap']) );
	$is_csv_wrap=( isset($_POST['csv_wrap']) OR isset($_GET['csv_wrap']) );
	$html_wrap=FALSE; // is FALSE for ajax, print or csv
}else{
	$html_wrap=TRUE;
}

$isHome = false;
if(!empty($_GET['to']) && isset($page[$_GET['to']])){
	$url_php=$page[$_GET['to']];
    $isHome = true;
}else{
	if(is_file('is_close')) {
        $url_php = $page['close'];
    } else {
        $url_php = $page['home'];
        $isHome = true;
    }
}

// 1>
$url_php_func=str_replace('.php','_func.php',$url_php);

if(is_file($url_php_func)){
	include $url_php_func;
}

$url_php_header=str_replace('.php','_header.php',$url_php);
if(is_file($url_php_header)){
	include $url_php_header;
}

// 2>
if($html_wrap){

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title><?php echo TITLE_SITE; ?></title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, minimal-ui">

        <link rel="icon" type="image/png" href="pic/favicon.png" />

    <!-- Facebook stuff -->
        <meta property="og:title" content="<?php echo TITLE_SITE; ?>" />
		<meta property="og:type" content="website" />
		<meta property="og:url" content="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];?>" />

        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

        <!-- Chrome Android -->
        <meta name="mobile-web-app-capable" content="yes">

		<!-- Bootstrap -->
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

        <!--flex slider css-->
        <link href="css/flexslider.css" rel="stylesheet">
        
        <!--animated css-->
        <link href="css/animate.css" rel="stylesheet">

        <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
        <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <script src="js/respond.min.js"></script>
        <![endif]-->


<?php
}


if($html_wrap){
	// 1bis>
	$url_php_meta=str_replace('.php','_meta.php',$url_php);
	if(is_file($url_php_meta)){
		include $url_php_meta;
	}else{
		echo '<meta name="description" content="'.getSiteParam('meta_description').'" />'.PHP_EOL;
		echo '<meta name="keywords" content="'.getSiteParam('meta_keyword').'" />';
	}

	// Gestion de l'inclusion des Scrip et Style pour le site
    include(PATH_TO_CLASS_FOLDER.'HTMLHeader.php');
    $_Header = new HTMLHeader();
    $_Header->registerCommonScripts();
    $_Header->registerCommonStyleSheets();
    $_Header->registerSpecificStyleSheets();
    $_Header->render();

		
}


// 3>
if($html_wrap) $url_php_head=str_replace('.php','_head.php',$url_php);
else $url_php_head='';
if(is_file($url_php_head)){
	include $url_php_head;
}else{
	if($html_wrap){
		echo '<title>'.TITLE_SITE.'</title>';
	}
}

// 4>
if($html_wrap) {
	echo '</head>';
}

// Inclusion de la page demandé par l'utilisateur
require $url_php;



if($html_wrap){
	// Google Analytics
	if(is_file('is_prod')){

	    // Gestion de l'inclusion si besoin de Google Analytics

        //$html_analytics = '<!-- Global site tag (gtag.js) - Google Analytics -->'.PHP_EOL;
        //$html_analytics.= '<script async src="https://www.googletagmanager.com/gtag/js?id=UA-XXXXXXXX-1"></script>'.PHP_EOL;
        //$html_analytics.= '<script>'.PHP_EOL;
        //$html_analytics.= '  window.dataLayer = window.dataLayer || [];'.PHP_EOL;
        //$html_analytics.= '  function gtag(){dataLayer.push(arguments);}'.PHP_EOL;
        //$html_analytics.= '  gtag(\'js\', new Date());'.PHP_EOL;
        //$html_analytics.= '  gtag(\'config\', \'UA-119424441-1\');'.PHP_EOL;
        //$html_analytics.= '</script>'.PHP_EOL;
		
		//echo $html_analytics;
	}
	echo '</html>';
}

?>