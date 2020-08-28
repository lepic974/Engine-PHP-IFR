<?php
/*
 * @desc 		Administration Make Me Prod
 * @date		01/05/2011
 * @author		Christophe Thibault <christophe.thibault@gmail.com>
 */

ini_set('session.gc_maxlifetime', 7200);

/* Utilise l'encodage interne UTF-8 */
ini_set('default_charset', 'utf-8');
mb_internal_encoding("UTF-8");
@error_reporting(E_ALL ^ E_DEPRECATED);

//ebug : error, mdbug : php mem, tdbug : php exec time, qdbug : query
if(isset($_GET['e_all'])) ini_set( 'display_errors' , TRUE  );

// Inclusion pour préparation du moteur 
require 'inc/inc_common.php';

// Chargement de la session et verification pour authentification
session_name('ub');
session_start();

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

// force login
$auth = new auth();

if($auth->verif_auth()){
	if(!empty($_GET['to']) && isset($page[$_GET['to']])){
		$url_php=$page[$_GET['to']];
	}else{
		$url_php=$page['home'];
	}
}else{
	$url_php=$page['login'];
}

// 1>
$url_php_header=str_replace('.php','_func.php',$url_php);

if(is_file($url_php_header)){
	include $url_php_header;
}

$url_php_header=str_replace('.php','_header.php',$url_php);
if(is_file($url_php_header)){
	include $url_php_header;
}

// 2>
if($html_wrap){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-Transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
}
//ob_start();
if($html_wrap){
?>
<head>
<link rel="icon" type="image/png" href="pic/favicon.png" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="ROBOTS" content="none,noarchive"/>
<?php 
	// rendu des scripts d'entetes et de style
	include(PATH_TO_CLASS_FOLDER.'HTMLHeader.php');
	$_Header = new HTMLHeader();
	$_Header->registerCommonScripts();
	$_Header->registerCommonStyleSheets();
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
	// Gestion User Interface
	echo manage_ui();
	echo '</head>';
}

// Inclusion de la page demandé par l'utilisateur
require $url_php;

if($html_wrap){
	echo '</html>';
}

?>