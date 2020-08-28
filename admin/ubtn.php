<?php
#CONFIG>
	if(!defined('SITE_NAME'))
		define('SITE_NAME',file_get_contents('../param.ini'));
	$ubtn_gfx_path='pic/ubtn/';
	$ubtn_cache=FALSE;
	$ubtn_cache_path='';
	$ubtn_font='arial.ttf';
	$ubtn_font_size=12;
	$prefix='';
	$size='small';
	if (isset($_GET['xgrid']) && $_GET['xgrid']==1)
	{
		$ubtn_font_size=10;
		$prefix='xgrid_';
		$size='xgrid';
	}
	$ubtn_no_text_icon_offset=-4;
	define('PATH_CACHE_UBTN','cache/ubtn_');
	$ubtn_cache_id=md5($_SERVER['QUERY_STRING']);
#<CONFIG

if(!is_file(PATH_CACHE_UBTN.$ubtn_cache_id) ) {
//if(1==1) {
	/*
	button elements
	left: left button border
	center: center button area
	right: right button border
	icon: prepend text with icon (optional)
	text: button text (optional)
	 */
	
	$b=array();
	$b['text']['string']='';
	
	if(isset($_GET['label'])){
		$b['text']['string']=stripslashes($_GET['label']);
	}
	$b['text']['string']=_local_accent($b['text']['string']);
	
	$hover=(isset($_GET['h']) OR isset($_GET['h2']));
	$h=( $hover ?  ( isset($_GET['h']) ? 'h':'h2'):'');
	
	if(isset($_GET['t']) ){
		$h.='_'.$_GET['t'];
	}
	
	$img_b = array ();
	$b['left']['image']=imagecreatefrompng($ubtn_gfx_path.$prefix."ubtn_l$h.png");
	$b['left']['width']=imagesx($b['left']['image']);
	$b['left']['height']=imagesy($b['left']['image']);
	
	$b['center']['image']=imagecreatefrompng($ubtn_gfx_path.$prefix."ubtn_c$h.png");
	$b['center']['height']=imagesy($b['center']['image']);
	
	$b['right']['image']=imagecreatefrompng($ubtn_gfx_path.$prefix."ubtn_r$h.png");
	$b['right']['width']=imagesx($b['right']['image']);
	$b['right']['height']=imagesy($b['right']['image']);
	
	if(isset($_GET['pic'])){
		$b['icon']['image']=imagecreatefrompng($_GET['pic']);
	}
	
	if(isset($_GET['mypic'])){
		session_name(SITE_NAME);
		session_start();
		require '../param/param_dbfield.php';
		require '../_specific/'.file_get_contents('../param.ini').'/param.php';
		require 'inc/inc_sqlconnect.php';
		require 'inc/inc_sqlquery.php';
		
		$id=$_GET['mypic'];
		if(!empty($id)){
			define('PATH_CACHE_MYPIC','cache/mypic_');
			if(is_file(PATH_CACHE_MYPIC.$id)){
				
			}else{
				$req = "SELECT code, mimetype, img_blob FROM ".T_IMG." WHERE id = '$id' OR code='$id'";
				$result=query($req);
				if (mysqli_num_rows($result)){
					$col = mysqli_fetch_row($result);
					header ("Content-type: ".$col[1]);
					file_put_contents(PATH_CACHE_MYPIC.$id, $col[2]);
				}
			}
			$b['icon']['image']=imagecreatefromstring(file_get_contents(PATH_CACHE_MYPIC.$id));
		}
	}
	
	if(isset($b['icon']['image'])){
		$b['icon']['width']=imagesx($b['icon']['image']); //also act as text x position offset
		$b['icon']['height']=imagesy($b['icon']['image']);
	}else{
		$b['icon']['width']=0;
		$b['icon']['height']=0;
	}
	
	$b['text']['string']=ucfirst($b['text']['string']);
		
	foreach($img_b as $key=>&$void){
		imageAlphaBlending($img_b[$key], false);
		imageSaveAlpha($img_b[$key], true);
	}
	$font=$ubtn_gfx_path.$ubtn_font;	
	$font_size=$ubtn_font_size;
	$b['text']['width']=0;
	$b['text']['height']=0;
	if(!empty($b['text']['string'])){
		$box= imagettfbbox ($font_size, 0, $font, $b['text']['string']);
		
		$b['text']['width']=abs($box[4]-$box[0]);
		$b['text']['height']=abs($box[5]-$box[1]);
	}
	$b['center']['width']=$b['icon']['width']+$b['text']['width'] + 15;
	
	$b['width']=$b['left']['width']+$b['center']['width']+$b['right']['width'];
	$b['height']=max( $b['icon']['height'], $b['center']['height'] ); //fit to taller of both icon and center button picture
	
	$img = imagecreatetruecolor ($b['width'], $b['height']);
	imageAlphaBlending($img, false);
	imageSaveAlpha($img, true);
	if($hover){
		if ($size=='xgrid'){
			$inkh = imagecolorallocate($img,255,255,255);
			$inkombre = imagecolorallocate($img,10,10,10);
			$ink = imagecolorallocate($img,255,255,255);
		}else{
			$inkh = imagecolorallocate($img,255,255,255);
			$inkombre = imagecolorallocate($img,10,10,10);
			$ink = imagecolorallocate($img,255,255,255);
		}
	}else{
		if ($size=='xgrid'){
			$ink = imagecolorallocate($img, 255, 255, 255);
		}else{
			$ink = imagecolorallocate($img, 255, 255, 255);
		}
	}
	
	$b['text']['x']=$b['left']['width']+$b['icon']['width'];
	
	#VERTICAL>
		$dst_y_button=0; //used for left/center/right
		$b['icon']['y']=0;	
		if($b['center']['height']<$b['height']){
			$dst_y_button=floor( ($b['height']-$b['center']['height'])/2 );	
		}
		if($b['icon']['height']<$b['height']){
			$b['icon']['y']=floor( ($b['height']-$b['icon']['height'])/2 ) + 1;	
		}
		$b['text']['y']=25;
		if($size=='xgrid')
			$b['text']['y']=15;
	#<VERTICAL
	
	#LEFT>
		$b['left']['x']=0;
		$b['left']['y']=$dst_y_button;
		imagecopy($img,$b['left']['image'],$b['left']['x'],$b['left']['y'], 0, 0, $b['left']['width'], $b['height']);
		imagedestroy ($b['left']['image']);
	#<LEFT
	
	#CENTER>
		$b['center']['x']=$b['left']['width']; //offset to left width
		$b['center']['y']=$dst_y_button;
		imagecopy($img,$b['center']['image'],$b['center']['x'],$b['center']['y'],0,0, $b['center']['width'] ,$b['center']['height']);
		imagedestroy ($b['center']['image']);
	#<CENTER
	
	#RIGHT>
		$b['right']['x']=$b['left']['width']+$b['center']['width']; //offset to left+center width
		$b['right']['y']=$dst_y_button;
		imagecopy($img,$b['right']['image'],$b['right']['x'],$b['right']['y'],0,0, $b['right']['width'], $b['right']['height']);
		imagedestroy ($b['right']['image']);
	#<RIGHT
	
	imageAlphaBlending($img, true);
	imageSaveAlpha($img, false);
	
	if(isset($b['icon']['image'])){
		$b['icon']['x']=$b['left']['width']+($b['text']['width']>0 ? $ubtn_no_text_icon_offset:0) - 3;
		imagecopy ($img,$b['icon']['image'],$b['icon']['x'] ,$b['icon']['y'],0,0, $b['icon']['width'], $b['icon']['height']);
		imagedestroy ($b['icon']['image']);
	}
	
	if(!empty($b['text']['string'])){
		if($hover){
			imagettftext($img, $font_size, 0, $b['text']['x']-1, $b['text']['y']-0.5, $inkombre, $font, $b['text']['string']);
			imagettftext($img, $font_size, 0, $b['text']['x'], $b['text']['y'], $inkh, $font, $b['text']['string']);
		}else{
			imagettftext($img, $font_size, 0, $b['text']['x'], $b['text']['y'], $ink, $font, $b['text']['string']);
		}
	}
	
	imageAlphaBlending($img, false);
	imageSaveAlpha($img, true);
	
	imagepng($img, PATH_CACHE_UBTN.$ubtn_cache_id);
}

header('Expires: ' . gmdate('D, d M Y H:i:s', time()+24*60*60) . ' GMT');
header ("Content-type: image/png");
readfile(PATH_CACHE_UBTN.$ubtn_cache_id);












function _local_accent($utf = '') {
  if($utf == '') return($utf);

  $max_count = 5; // flag-bits in $max_mark ( 1111 1000 == 5 times 1)
  $max_mark = 248; // marker for a (theoretical ;-)) 5-byte-char and mask for a 4-byte-char;

  $html = '';
  for($str_pos = 0; $str_pos < strlen($utf); $str_pos++) {
    $old_chr = $utf{$str_pos};
    $old_val = ord( $utf{$str_pos} );
    $new_val = 0;

    $utf8_marker = 0;

    // skip non-utf-8-chars
    if( $old_val > 127 ) {
      $mark = $max_mark;
      for($byte_ctr = $max_count; $byte_ctr > 2; $byte_ctr--) {
        // actual byte is utf-8-marker?
        if( ( $old_val & $mark  ) == ( ($mark << 1) & 255 ) ) {
          $utf8_marker = $byte_ctr - 1;
          break;
        }
        $mark = ($mark << 1) & 255;
      }
    }

    // marker found: collect following bytes
    if($utf8_marker > 1 and isset( $utf{$str_pos + 1} ) ) {
      $str_off = 0;
      $new_val = $old_val & (127 >> $utf8_marker);
      for($byte_ctr = $utf8_marker; $byte_ctr > 1; $byte_ctr--) {

        // check if following chars are UTF8 additional data blocks
        // UTF8 and ord() > 127
        if( (ord($utf{$str_pos + 1}) & 192) == 128 ) {
          $new_val = $new_val << 6;
          $str_off++;
          // no need for Addition, bitwise OR is sufficient
          // 63: more UTF8-bytes; 0011 1111
          $new_val = $new_val | ( ord( $utf{$str_pos + $str_off} ) & 63 );
        }
        // no UTF8, but ord() > 127
        // nevertheless convert first char to NCE
        else {
          $new_val = $old_val;
        }
      }
      // build NCE-Code
      $html .= '&#'.$new_val.';';
      // Skip additional UTF-8-Bytes
      $str_pos = $str_pos + $str_off;
    }
    else {
      $html .= chr($old_val);
      $new_val = $old_val;
    }
  }
  return($html);
}


?> 